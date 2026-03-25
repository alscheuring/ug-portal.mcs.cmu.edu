<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventFeed;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EventFeedImporter
{
    protected EventFeed $eventFeed;

    protected array $importSettings;

    protected int $imported = 0;

    protected int $updated = 0;

    protected int $skipped = 0;

    protected array $errors = [];

    public function __construct(EventFeed $eventFeed)
    {
        $this->eventFeed = $eventFeed;
        $this->importSettings = $eventFeed->import_settings ?? [];
    }

    /**
     * Import events from the external feed.
     */
    public function import(): array
    {
        if (! $this->eventFeed->is_active) {
            return $this->buildResult(false, 'Event feed is not active');
        }

        try {
            // Fetch data from the external API
            $response = Http::timeout(30)
                ->retry(3, 1000)
                ->get($this->eventFeed->api_url);

            if (! $response->successful()) {
                return $this->buildResult(false, "HTTP Error: {$response->status()} - {$response->body()}");
            }

            $data = $response->json();

            if (! $data) {
                return $this->buildResult(false, 'Invalid JSON response from feed');
            }

            // Process events based on feed type
            $events = $this->extractEvents($data);

            if (empty($events)) {
                return $this->buildResult(false, 'No events found in feed');
            }

            // Limit the number of events to process
            $events = array_slice($events, 0, $this->eventFeed->max_events);

            // Process each event
            foreach ($events as $eventData) {
                $this->processEvent($eventData);
            }

            // Mark feed as imported
            $this->eventFeed->markAsImported();

            return $this->buildResult(true, $this->buildSuccessMessage());

        } catch (\Exception $e) {
            Log::error("Event feed import failed for feed {$this->eventFeed->id}: {$e->getMessage()}", [
                'feed_id' => $this->eventFeed->id,
                'feed_name' => $this->eventFeed->name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->buildResult(false, "Import failed: {$e->getMessage()}");
        }
    }

    /**
     * Extract events array from API response based on feed type.
     */
    protected function extractEvents(array $data): array
    {
        $feedType = $this->importSettings['type'] ?? 'generic_json';

        return match ($feedType) {
            'cmu_events' => $this->extractCmuEvents($data),
            'university_calendar' => $this->extractUniversityCalendarEvents($data),
            'generic_json' => $this->extractGenericJsonEvents($data),
            default => $this->extractGenericJsonEvents($data),
        };
    }

    /**
     * Extract events from CMU Events API response.
     */
    protected function extractCmuEvents(array $data): array
    {
        return $data['events'] ?? $data;
    }

    /**
     * Extract events from university calendar response.
     */
    protected function extractUniversityCalendarEvents(array $data): array
    {
        return $data['events'] ?? $data['data'] ?? $data;
    }

    /**
     * Extract events from generic JSON response.
     */
    protected function extractGenericJsonEvents(array $data): array
    {
        // Try common event array keys
        $possibleKeys = ['events', 'data', 'items', 'results'];

        foreach ($possibleKeys as $key) {
            if (isset($data[$key]) && is_array($data[$key])) {
                return $data[$key];
            }
        }

        // If no nested array found, assume the entire response is the events array
        return is_array($data) && isset($data[0]) ? $data : [];
    }

    /**
     * Process a single event from the feed.
     */
    protected function processEvent(array $eventData): void
    {
        try {
            $mappedData = $this->mapEventData($eventData);

            if (! $this->validateEventData($mappedData)) {
                $this->skipped++;

                return;
            }

            // Check if event already exists
            $existingEvent = Event::where('external_id', $mappedData['external_id'])
                ->where('event_feed_id', $this->eventFeed->id)
                ->first();

            if ($existingEvent) {
                // Update existing event
                $existingEvent->update($mappedData);
                $this->updated++;
            } else {
                // Create new event
                Event::create($mappedData);
                $this->imported++;
            }

        } catch (\Exception $e) {
            $this->errors[] = "Error processing event: {$e->getMessage()}";
            Log::warning("Error processing event from feed {$this->eventFeed->id}", [
                'feed_id' => $this->eventFeed->id,
                'event_data' => $eventData,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Map external event data to internal event structure.
     */
    protected function mapEventData(array $eventData): array
    {
        $fieldMapping = $this->importSettings['field_mapping'] ?? [];
        $dateFormat = $this->importSettings['date_format'] ?? 'c';
        $timezone = $this->importSettings['timezone'] ?? 'UTC';

        $mappedData = [
            'team_id' => $this->eventFeed->team_id,
            'event_feed_id' => $this->eventFeed->id,
            'source_type' => 'imported',
            'is_published' => true, // Imported events are published by default
            'author_id' => null, // Imported events don't have authors
        ];

        foreach ($fieldMapping as $internalField => $externalField) {
            $value = $this->getNestedValue($eventData, $externalField);

            if ($value === null) {
                continue;
            }

            switch ($internalField) {
                case 'start_datetime':
                case 'end_datetime':
                    $mappedData[$internalField] = $this->parseDate($value, $dateFormat, $timezone);
                    break;

                case 'tags':
                    $mappedData[$internalField] = $this->parseTags($value);
                    break;

                case 'title':
                    $mappedData[$internalField] = $this->sanitizeText($value, 255);
                    $mappedData['slug'] = Event::generateSlug($mappedData[$internalField]);
                    break;

                case 'description':
                case 'summary':
                    $mappedData[$internalField] = $this->sanitizeHtml($value);
                    break;

                case 'location':
                case 'info_url':
                case 'image_url':
                    $mappedData[$internalField] = $this->sanitizeText($value, 255);
                    break;

                case 'external_id':
                    $mappedData[$internalField] = (string) $value;
                    break;

                default:
                    $mappedData[$internalField] = $value;
                    break;
            }
        }

        return $mappedData;
    }

    /**
     * Get nested value from array using dot notation.
     */
    protected function getNestedValue(array $data, string $key)
    {
        if (strpos($key, '.') === false) {
            return $data[$key] ?? null;
        }

        $keys = explode('.', $key);
        $value = $data;

        foreach ($keys as $nestedKey) {
            if (! is_array($value) || ! isset($value[$nestedKey])) {
                return null;
            }
            $value = $value[$nestedKey];
        }

        return $value;
    }

    /**
     * Parse date from various formats.
     */
    protected function parseDate(string $dateString, string $format, string $timezone): ?Carbon
    {
        try {
            if ($format === 'timestamp') {
                return Carbon::createFromTimestamp($dateString, $timezone)->utc();
            }

            return Carbon::createFromFormat($format, $dateString, $timezone)->utc();
        } catch (\Exception $e) {
            // Try common fallback formats
            $fallbackFormats = [
                'c', // ISO 8601
                'Y-m-d H:i:s',
                'Y-m-d\TH:i:s\Z',
                'Y-m-d\TH:i:sP',
                'M j, Y g:i A',
            ];

            foreach ($fallbackFormats as $fallbackFormat) {
                try {
                    return Carbon::createFromFormat($fallbackFormat, $dateString, $timezone)->utc();
                } catch (\Exception $e) {
                    continue;
                }
            }

            Log::warning("Could not parse date '{$dateString}' with format '{$format}'", [
                'date_string' => $dateString,
                'format' => $format,
                'timezone' => $timezone,
            ]);

            return null;
        }
    }

    /**
     * Parse tags from various formats.
     */
    protected function parseTags($value): ?array
    {
        if (is_array($value)) {
            return array_filter(array_map('trim', $value));
        }

        if (is_string($value)) {
            // Try common delimiters
            $delimiters = [',', ';', '|'];

            foreach ($delimiters as $delimiter) {
                if (strpos($value, $delimiter) !== false) {
                    return array_filter(array_map('trim', explode($delimiter, $value)));
                }
            }

            // Single tag
            return [$value];
        }

        return null;
    }

    /**
     * Sanitize text content.
     */
    protected function sanitizeText(?string $text, ?int $maxLength = null): ?string
    {
        if (! $text) {
            return null;
        }

        $text = trim(strip_tags($text));

        if ($maxLength) {
            $text = Str::limit($text, $maxLength);
        }

        return $text ?: null;
    }

    /**
     * Sanitize HTML content.
     */
    protected function sanitizeHtml(?string $html): ?string
    {
        if (! $html) {
            return null;
        }

        // Allow basic HTML tags
        $allowedTags = '<p><br><strong><b><em><i><u><a><ul><ol><li><h2><h3><blockquote>';

        return trim(strip_tags($html, $allowedTags)) ?: null;
    }

    /**
     * Validate that required event data is present.
     */
    protected function validateEventData(array $data): bool
    {
        // Required fields
        $required = ['title', 'start_datetime', 'end_datetime', 'external_id'];

        foreach ($required as $field) {
            if (empty($data[$field])) {
                $this->errors[] = "Missing required field: {$field}";

                return false;
            }
        }

        // Validate dates
        if (! $data['start_datetime'] instanceof Carbon) {
            $this->errors[] = 'Invalid start_datetime format';

            return false;
        }

        if (! $data['end_datetime'] instanceof Carbon) {
            $this->errors[] = 'Invalid end_datetime format';

            return false;
        }

        if ($data['end_datetime']->lessThanOrEqualTo($data['start_datetime'])) {
            $this->errors[] = 'End datetime must be after start datetime';

            return false;
        }

        return true;
    }

    /**
     * Build result array.
     */
    protected function buildResult(bool $success, string $message): array
    {
        return [
            'success' => $success,
            'message' => $message,
            'stats' => [
                'imported' => $this->imported,
                'updated' => $this->updated,
                'skipped' => $this->skipped,
                'errors' => count($this->errors),
            ],
            'errors' => $this->errors,
        ];
    }

    /**
     * Build success message.
     */
    protected function buildSuccessMessage(): string
    {
        $total = $this->imported + $this->updated + $this->skipped;
        $parts = [];

        if ($this->imported > 0) {
            $parts[] = "{$this->imported} imported";
        }

        if ($this->updated > 0) {
            $parts[] = "{$this->updated} updated";
        }

        if ($this->skipped > 0) {
            $parts[] = "{$this->skipped} skipped";
        }

        if (! empty($this->errors)) {
            $parts[] = count($this->errors).' errors';
        }

        $summary = implode(', ', $parts);

        return "Successfully processed {$total} events: {$summary}";
    }
}
