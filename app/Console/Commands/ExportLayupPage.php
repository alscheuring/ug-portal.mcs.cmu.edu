<?php

namespace App\Console\Commands;

use App\Models\LayupPage;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ExportLayupPage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'layup:export
                            {id : Page ID or slug to export}
                            {--output= : Output file path (optional)}
                            {--include-sidebars : Include sidebar assignments in export}
                            {--team= : Team ID or slug to search within}
                            {--format=json : Export format (json)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export a layup page to a JSON file';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $identifier = $this->argument('id');
        $teamOption = $this->option('team');

        // Build query
        $query = LayupPage::with(['sidebars', 'team', 'author']);

        // If team is specified, filter by team
        if ($teamOption) {
            $teamId = is_numeric($teamOption) ? $teamOption : null;
            $teamSlug = is_numeric($teamOption) ? null : $teamOption;

            $query->when($teamId, function ($q) use ($teamId) {
                return $q->where('team_id', $teamId);
            })->when($teamSlug, function ($q) use ($teamSlug) {
                return $q->whereHas('team', function ($teamQuery) use ($teamSlug) {
                    $teamQuery->where('slug', $teamSlug);
                });
            });
        }

        // Find page by ID or slug
        $page = is_numeric($identifier)
            ? $query->find($identifier)
            : $query->where('slug', $identifier)->first();

        if (! $page) {
            $this->error("Page not found: {$identifier}");

            // Show available pages for the team if specified
            if ($teamOption) {
                $this->showAvailablePages($teamOption);
            }

            return self::FAILURE;
        }

        // Prepare export data
        $exportData = [
            'title' => $page->title,
            'slug' => $page->slug,
            'content' => $page->content,
            'status' => $page->status,
            'meta' => $page->meta,
            'is_department_home' => $page->is_department_home,
        ];

        // Include sidebar assignments if requested
        if ($this->option('include-sidebars') && $page->sidebars->isNotEmpty()) {
            $exportData['sidebars'] = $page->sidebars->map(function ($sidebar) {
                return [
                    'id' => $sidebar->id,
                    'name' => $sidebar->name,
                    'title' => $sidebar->title,
                    'sort_order' => $sidebar->pivot->sort_order,
                ];
            })->toArray();
        }

        // Add metadata for context
        $exportData['_meta'] = [
            'exported_at' => now()->toISOString(),
            'original_id' => $page->id,
            'team' => [
                'id' => $page->team->id,
                'name' => $page->team->name,
                'slug' => $page->team->slug,
            ],
            'author' => [
                'id' => $page->author->id,
                'name' => $page->author->name,
                'email' => $page->author->email,
            ],
            'published_at' => $page->published_at?->toISOString(),
            'created_at' => $page->created_at->toISOString(),
            'updated_at' => $page->updated_at->toISOString(),
        ];

        // Determine output file
        $outputFile = $this->option('output');
        if (! $outputFile) {
            $filename = Str::slug($page->title).'.json';
            $outputFile = storage_path('app/layup-exports/'.$filename);

            // Create directory if it doesn't exist
            $directory = dirname($outputFile);
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
        }

        // Write JSON file
        $jsonContent = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if (file_put_contents($outputFile, $jsonContent) === false) {
            $this->error("Failed to write to file: {$outputFile}");

            return self::FAILURE;
        }

        $this->info("Successfully exported layup page to: {$outputFile}");

        // Show summary
        $this->table(['Field', 'Value'], [
            ['Title', $page->title],
            ['Slug', $page->slug],
            ['Status', $page->status],
            ['Team', $page->team->name],
            ['Author', $page->author->name],
            ['Sidebars', $page->sidebars->count()],
            ['File Size', $this->formatBytes(strlen($jsonContent))],
            ['Output', $outputFile],
        ]);

        return self::SUCCESS;
    }

    /**
     * Show available pages for troubleshooting.
     */
    protected function showAvailablePages(string $teamOption): void
    {
        $this->line('');
        $this->info('Available pages for team:');

        $query = LayupPage::with(['team']);

        if (is_numeric($teamOption)) {
            $query->where('team_id', $teamOption);
        } else {
            $query->whereHas('team', function ($teamQuery) use ($teamOption) {
                $teamQuery->where('slug', $teamOption);
            });
        }

        $pages = $query->orderBy('title')->get(['id', 'title', 'slug', 'status']);

        if ($pages->isEmpty()) {
            $this->warn('No pages found for this team.');

            return;
        }

        $tableData = $pages->map(function ($page) {
            return [$page->id, $page->title, $page->slug, $page->status];
        })->toArray();

        $this->table(['ID', 'Title', 'Slug', 'Status'], $tableData);
    }

    /**
     * Format bytes to human readable format.
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision).' '.$units[$i];
    }
}
