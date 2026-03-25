<?php

namespace App\Console\Commands;

use App\Jobs\ImportEventFeedJob;
use App\Models\EventFeed;
use App\Services\EventFeedImporter;
use Illuminate\Console\Command;

class ImportEventFeedsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-event-feeds
                           {--feed= : Import specific feed by ID}
                           {--team= : Import feeds for specific team}
                           {--force : Import all feeds regardless of last import time}
                           {--sync : Run imports synchronously (not queued)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import events from external feeds';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting event feed import process...');

        try {
            $feeds = $this->getFeeds();

            if ($feeds->isEmpty()) {
                $this->warn('No feeds found matching the criteria.');

                return self::SUCCESS;
            }

            $this->info("Found {$feeds->count()} feed(s) to process.");

            $imported = 0;
            $errors = 0;

            foreach ($feeds as $feed) {
                $this->line("Processing feed: {$feed->name} (ID: {$feed->id})");

                try {
                    if ($this->option('sync')) {
                        // Run synchronously
                        $importer = new EventFeedImporter($feed);
                        $result = $importer->import();

                        if ($result['success']) {
                            $this->info("  ✓ {$result['message']}");
                            $imported++;
                        } else {
                            $this->error("  ✗ Import failed: {$result['message']}");
                            $errors++;

                            if (! empty($result['errors'])) {
                                foreach ($result['errors'] as $error) {
                                    $this->line("    - {$error}");
                                }
                            }
                        }
                    } else {
                        // Queue the job
                        ImportEventFeedJob::dispatch($feed);
                        $this->info('  ✓ Import job queued');
                        $imported++;
                    }
                } catch (\Exception $e) {
                    $this->error("  ✗ Error processing feed: {$e->getMessage()}");
                    $errors++;
                }

                $this->newLine();
            }

            // Summary
            if ($this->option('sync')) {
                $this->info("Import completed: {$imported} successful, {$errors} errors");
            } else {
                $this->info("Queued {$imported} import jobs");
                if ($errors > 0) {
                    $this->warn("Failed to queue {$errors} jobs");
                }
            }

            return $errors > 0 ? self::FAILURE : self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Command failed: {$e->getMessage()}");

            return self::FAILURE;
        }
    }

    /**
     * Get the feeds to import based on command options.
     */
    protected function getFeeds()
    {
        $query = EventFeed::query();

        // Filter by specific feed ID
        if ($feedId = $this->option('feed')) {
            return $query->where('id', $feedId)->get();
        }

        // Filter by team
        if ($teamId = $this->option('team')) {
            $query->where('team_id', $teamId);
        }

        // Only active feeds by default
        $query->where('is_active', true);

        // Unless forced, only import feeds that need import
        if (! $this->option('force')) {
            $query->where(function ($q) {
                $q->whereNull('last_imported_at')
                    ->orWhere('last_imported_at', '<', now()->subHours(1));
            });
        }

        return $query->orderBy('name')->get();
    }
}
