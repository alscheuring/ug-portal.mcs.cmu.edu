<?php

namespace App\Console\Commands;

use App\Models\LayupPage;
use App\Models\Team;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ImportLayupPage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'layup:import
                            {file : Path to the JSON file to import}
                            {--team= : Team ID or slug to assign the page to}
                            {--author= : Author ID or email to assign the page to}
                            {--status=draft : Page status (draft, published)}
                            {--overwrite : Overwrite existing page with same slug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import a layup page from a JSON file';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filePath = $this->argument('file');

        // Check if file exists
        if (! file_exists($filePath)) {
            $this->error("File not found: {$filePath}");

            return self::FAILURE;
        }

        // Read and decode JSON
        $jsonContent = file_get_contents($filePath);
        $data = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Invalid JSON file: '.json_last_error_msg());

            return self::FAILURE;
        }

        // Validate required fields
        $validator = Validator::make($data, [
            'title' => 'required|string|max:255',
            'slug' => 'sometimes|string|max:255',
            'content' => 'required|array',
            'content.rows' => 'required|array',
            'status' => 'sometimes|in:draft,published',
            'meta' => 'sometimes|array',
            'is_department_home' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            $this->error('Validation failed:');
            foreach ($validator->errors()->all() as $error) {
                $this->line("  • {$error}");
            }

            return self::FAILURE;
        }

        // Generate slug if not provided
        if (! isset($data['slug']) || empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        // Resolve team
        $team = $this->resolveTeam();
        if (! $team) {
            return self::FAILURE;
        }

        // Resolve author
        $author = $this->resolveAuthor();
        if (! $author) {
            return self::FAILURE;
        }

        // Check for existing page
        $existing = LayupPage::where('slug', $data['slug'])
            ->where('team_id', $team->id)
            ->first();

        if ($existing && ! $this->option('overwrite')) {
            $this->error("Page with slug '{$data['slug']}' already exists for team '{$team->name}'. Use --overwrite to replace it.");

            return self::FAILURE;
        }

        // Prepare page data
        $pageData = [
            'title' => $data['title'],
            'slug' => $data['slug'],
            'content' => $this->processContent($data['content']),
            'status' => $this->option('status') ?: ($data['status'] ?? 'draft'),
            'meta' => $data['meta'] ?? [],
            'team_id' => $team->id,
            'author_id' => $author->id,
            'is_department_home' => $data['is_department_home'] ?? false,
        ];

        if ($pageData['status'] === 'published') {
            $pageData['published_at'] = now();
        }

        try {
            DB::beginTransaction();

            if ($existing && $this->option('overwrite')) {
                $existing->update($pageData);
                $page = $existing;
                $this->info("Successfully updated layup page: {$page->title}");
            } else {
                $page = LayupPage::create($pageData);
                $this->info("Successfully created layup page: {$page->title}");
            }

            // Handle sidebar assignments if provided
            if (isset($data['sidebars']) && is_array($data['sidebars'])) {
                $this->assignSidebars($page, $data['sidebars'], $team);
            }

            DB::commit();

            $this->table(['Field', 'Value'], [
                ['Title', $page->title],
                ['Slug', $page->slug],
                ['Status', $page->status],
                ['Team', $team->name],
                ['Author', $author->name ?? $author->email],
                ['URL', $page->getUrl()],
                ['ID', $page->id],
            ]);

            return self::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Failed to import page: {$e->getMessage()}");

            return self::FAILURE;
        }
    }

    /**
     * Process and validate the content structure.
     */
    protected function processContent(array $content): array
    {
        // Ensure all rows have required IDs and structure
        foreach ($content['rows'] as $index => &$row) {
            if (! isset($row['id']) || empty($row['id'])) {
                $row['id'] = 'row_'.Str::random(8);
            }

            if (! isset($row['order'])) {
                $row['order'] = $index;
            }

            // Process columns
            if (isset($row['columns']) && is_array($row['columns'])) {
                foreach ($row['columns'] as &$column) {
                    if (! isset($column['id']) || empty($column['id'])) {
                        $column['id'] = 'col_'.Str::random(8);
                    }

                    // Process widgets
                    if (isset($column['widgets']) && is_array($column['widgets'])) {
                        foreach ($column['widgets'] as &$widget) {
                            if (! isset($widget['id']) || empty($widget['id'])) {
                                $widget['id'] = 'widget_'.Str::random(8);
                            }
                        }
                    }
                }
            }
        }

        return $content;
    }

    /**
     * Resolve the team from options or prompt.
     */
    protected function resolveTeam(): ?Team
    {
        $teamOption = $this->option('team');

        if ($teamOption) {
            // Try by ID first, then by slug
            $team = is_numeric($teamOption)
                ? Team::find($teamOption)
                : Team::where('slug', $teamOption)->first();

            if (! $team) {
                $this->error("Team not found: {$teamOption}");

                return null;
            }

            return $team;
        }

        // If no team specified, prompt for selection
        $teams = Team::orderBy('name')->get(['id', 'name', 'slug']);

        if ($teams->isEmpty()) {
            $this->error('No teams found. Please create a team first.');

            return null;
        }

        $choices = $teams->mapWithKeys(function ($team) {
            return [$team->id => "{$team->name} ({$team->slug})"];
        })->toArray();

        $teamId = $this->choice('Select a team:', $choices);

        return $teams->find($teamId);
    }

    /**
     * Resolve the author from options or current user.
     */
    protected function resolveAuthor(): ?User
    {
        $authorOption = $this->option('author');

        if ($authorOption) {
            // Try by ID first, then by email
            $author = is_numeric($authorOption)
                ? User::find($authorOption)
                : User::where('email', $authorOption)->first();

            if (! $author) {
                $this->error("Author not found: {$authorOption}");

                return null;
            }

            return $author;
        }

        // Default to first admin user
        $author = User::where('is_super_admin', true)->first()
            ?? User::first();

        if (! $author) {
            $this->error('No users found. Please create a user first.');

            return null;
        }

        return $author;
    }

    /**
     * Assign sidebars to the page.
     */
    protected function assignSidebars(LayupPage $page, array $sidebarData, Team $team): void
    {
        $sidebarIds = [];

        foreach ($sidebarData as $index => $sidebarInfo) {
            if (is_numeric($sidebarInfo)) {
                $sidebarIds[$sidebarInfo] = ['sort_order' => $index];
            } elseif (is_array($sidebarInfo) && isset($sidebarInfo['id'])) {
                $sidebarIds[$sidebarInfo['id']] = [
                    'sort_order' => $sidebarInfo['sort_order'] ?? $index,
                ];
            }
        }

        // Validate that sidebars belong to the same team
        $validSidebarIds = $team->sidebars()
            ->whereIn('id', array_keys($sidebarIds))
            ->pluck('id')
            ->toArray();

        $filteredSidebarIds = array_intersect_key($sidebarIds, array_flip($validSidebarIds));

        if (count($filteredSidebarIds) !== count($sidebarIds)) {
            $this->warn('Some sidebars were skipped as they do not belong to the selected team.');
        }

        if (! empty($filteredSidebarIds)) {
            $page->sidebars()->sync($filteredSidebarIds);
            $this->info('Assigned '.count($filteredSidebarIds).' sidebar(s) to the page.');
        }
    }
}
