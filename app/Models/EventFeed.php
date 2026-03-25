<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Http;

class EventFeed extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'api_url',
        'max_events',
        'is_active',
        'import_settings',
        'last_imported_at',
        'team_id',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'import_settings' => 'array',
            'last_imported_at' => 'datetime',
        ];
    }

    /**
     * Get the team that owns this event feed.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the events imported from this feed.
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Scope query to only include active feeds.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope query to filter by team.
     */
    public function scopeForTeam(Builder $query, $teamId): Builder
    {
        return $query->where('team_id', $teamId);
    }

    /**
     * Scope query to filter feeds that need import (haven't been imported recently).
     */
    public function scopeNeedingImport(Builder $query, $hoursAgo = 24): Builder
    {
        return $query->where('is_active', true)
            ->where(function ($q) use ($hoursAgo) {
                $q->whereNull('last_imported_at')
                    ->orWhere('last_imported_at', '<', now()->subHours($hoursAgo));
            });
    }

    /**
     * Check if the feed is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if the feed needs to be imported.
     */
    public function needsImport(int $hoursAgo = 24): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if (! $this->last_imported_at) {
            return true;
        }

        return $this->last_imported_at < now()->subHours($hoursAgo);
    }

    /**
     * Get the time since last import in a human-readable format.
     */
    public function getLastImportedDiffAttribute(): string
    {
        if (! $this->last_imported_at) {
            return 'Never imported';
        }

        return $this->last_imported_at->diffForHumans();
    }

    /**
     * Get the import status.
     */
    public function getImportStatusAttribute(): string
    {
        if (! $this->is_active) {
            return 'Inactive';
        }

        if (! $this->last_imported_at) {
            return 'Not imported yet';
        }

        if ($this->needsImport()) {
            return 'Needs import';
        }

        return 'Up to date';
    }

    /**
     * Get the count of imported events from this feed.
     */
    public function getImportedEventsCountAttribute(): int
    {
        return $this->events()->count();
    }

    /**
     * Test the feed connection and validate the URL.
     */
    public function testConnection(): array
    {
        try {
            $response = Http::timeout(10)->get($this->api_url);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Connection successful',
                    'status_code' => $response->status(),
                    'content_type' => $response->header('Content-Type'),
                ];
            }

            return [
                'success' => false,
                'message' => 'HTTP Error: '.$response->status(),
                'status_code' => $response->status(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection failed: '.$e->getMessage(),
                'status_code' => null,
            ];
        }
    }

    /**
     * Get the default import settings for a CMU Events API feed.
     */
    public static function getCmuEventsFeedSettings(): array
    {
        return [
            'type' => 'cmu_events',
            'field_mapping' => [
                'title' => 'title',
                'description' => 'description',
                'summary' => 'summary',
                'start_datetime' => 'start_date',
                'end_datetime' => 'end_date',
                'location' => 'location',
                'info_url' => 'url',
                'image_url' => 'image',
                'tags' => 'tags',
                'external_id' => 'id',
            ],
            'date_format' => 'Y-m-d H:i:s',
            'timezone' => 'America/New_York',
        ];
    }

    /**
     * Get the default import settings for a generic JSON feed.
     */
    public static function getGenericJsonFeedSettings(): array
    {
        return [
            'type' => 'generic_json',
            'field_mapping' => [
                'title' => 'title',
                'description' => 'description',
                'summary' => 'summary',
                'start_datetime' => 'start',
                'end_datetime' => 'end',
                'location' => 'location',
                'info_url' => 'url',
                'image_url' => 'image',
                'tags' => 'tags',
                'external_id' => 'id',
            ],
            'date_format' => 'c', // ISO 8601
            'timezone' => 'UTC',
        ];
    }

    /**
     * Mark the feed as imported.
     */
    public function markAsImported(): void
    {
        $this->update(['last_imported_at' => now()]);
    }

    /**
     * Activate the feed.
     */
    public function activate(): void
    {
        $this->update(['is_active' => true]);
    }

    /**
     * Deactivate the feed.
     */
    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }
}
