<?php

namespace App\Jobs;

use App\Models\EventFeed;
use App\Models\User;
use App\Services\EventFeedImporter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class ImportEventFeedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300; // 5 minutes

    public int $tries = 3;

    public int $backoff = 60; // 1 minute backoff between retries

    public function __construct(
        public EventFeed $eventFeed,
        public ?User $user = null
    ) {
        $this->onQueue('default');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Starting event feed import for feed: {$this->eventFeed->name} (ID: {$this->eventFeed->id})");

        try {
            $importer = new EventFeedImporter($this->eventFeed);
            $result = $importer->import();

            if ($result['success']) {
                Log::info("Event feed import completed successfully for feed: {$this->eventFeed->name}", [
                    'feed_id' => $this->eventFeed->id,
                    'stats' => $result['stats'],
                ]);

                // Send success notification if user is provided
                if ($this->user) {
                    $this->sendSuccessNotification($result);
                }
            } else {
                Log::error("Event feed import failed for feed: {$this->eventFeed->name}", [
                    'feed_id' => $this->eventFeed->id,
                    'message' => $result['message'],
                    'errors' => $result['errors'] ?? [],
                ]);

                // Send failure notification if user is provided
                if ($this->user) {
                    $this->sendFailureNotification($result);
                }

                // Mark job as failed so it will be retried
                $this->fail(new \Exception($result['message']));
            }
        } catch (\Exception $e) {
            Log::error("Exception during event feed import for feed: {$this->eventFeed->name}", [
                'feed_id' => $this->eventFeed->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Send exception notification if user is provided
            if ($this->user) {
                $this->sendExceptionNotification($e);
            }

            throw $e; // Re-throw to mark job as failed
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Event feed import job failed permanently for feed: {$this->eventFeed->name}", [
            'feed_id' => $this->eventFeed->id,
            'attempts' => $this->attempts(),
            'exception' => $exception->getMessage(),
        ]);

        // Send final failure notification if user is provided
        if ($this->user) {
            $this->sendPermanentFailureNotification($exception);
        }
    }

    /**
     * Send success notification to user.
     */
    protected function sendSuccessNotification(array $result): void
    {
        try {
            \Filament\Notifications\Notification::make()
                ->success()
                ->title('Event import completed')
                ->body("Successfully imported events from '{$this->eventFeed->name}': {$result['message']}")
                ->sendToDatabase($this->user);
        } catch (\Exception $e) {
            Log::warning("Failed to send success notification: {$e->getMessage()}");
        }
    }

    /**
     * Send failure notification to user.
     */
    protected function sendFailureNotification(array $result): void
    {
        try {
            \Filament\Notifications\Notification::make()
                ->danger()
                ->title('Event import failed')
                ->body("Import failed for '{$this->eventFeed->name}': {$result['message']}")
                ->sendToDatabase($this->user);
        } catch (\Exception $e) {
            Log::warning("Failed to send failure notification: {$e->getMessage()}");
        }
    }

    /**
     * Send exception notification to user.
     */
    protected function sendExceptionNotification(\Exception $exception): void
    {
        try {
            \Filament\Notifications\Notification::make()
                ->danger()
                ->title('Event import error')
                ->body("An error occurred importing '{$this->eventFeed->name}': {$exception->getMessage()}")
                ->sendToDatabase($this->user);
        } catch (\Exception $e) {
            Log::warning("Failed to send exception notification: {$e->getMessage()}");
        }
    }

    /**
     * Send permanent failure notification to user.
     */
    protected function sendPermanentFailureNotification(\Throwable $exception): void
    {
        try {
            \Filament\Notifications\Notification::make()
                ->danger()
                ->title('Event import permanently failed')
                ->body("Import for '{$this->eventFeed->name}' failed permanently after {$this->tries} attempts. Error: {$exception->getMessage()}")
                ->sendToDatabase($this->user);
        } catch (\Exception $e) {
            Log::warning("Failed to send permanent failure notification: {$e->getMessage()}");
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'event-import',
            "feed:{$this->eventFeed->id}",
            "team:{$this->eventFeed->team_id}",
        ];
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [60, 300, 900]; // 1 minute, 5 minutes, 15 minutes
    }
}
