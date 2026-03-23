<?php

namespace App\Console\Commands;

use App\Models\LayupPage;
use App\Models\Team;
use App\Models\User;
use Illuminate\Console\Command;

class CreateDepartmentHomePages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'department:create-home-pages {--force : Force creation even if pages already exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create undeleteable LayupPage home pages for all departments';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $force = $this->option('force');

        $this->info('Creating department home pages...');

        // Get all active teams
        $teams = Team::where('is_active', true)->get();

        if ($teams->isEmpty()) {
            $this->warn('No active teams found.');

            return self::SUCCESS;
        }

        // Find a super admin user to be the author
        $author = User::whereHas('roles', function ($query) {
            $query->where('name', 'SuperAdmin');
        })->first();

        if (! $author) {
            $this->error('No SuperAdmin user found. Please ensure at least one SuperAdmin user exists.');

            return self::FAILURE;
        }

        $created = 0;
        $skipped = 0;

        foreach ($teams as $team) {
            // Check if department home page already exists
            $existingPage = LayupPage::where('team_id', $team->id)
                ->where('slug', $team->slug)
                ->where('is_department_home', true)
                ->first();

            if ($existingPage && ! $force) {
                $this->line("Skipping {$team->name} - department home page already exists");
                $skipped++;

                continue;
            }

            // Create or update the department home page
            $pageData = [
                'title' => $team->name,
                'slug' => $team->slug,
                'content' => $this->generateDefaultContent($team),
                'status' => 'published',
                'meta' => [],
                'team_id' => $team->id,
                'author_id' => $author->id,
                'published_at' => now(),
                'is_department_home' => true,
            ];

            if ($existingPage && $force) {
                $existingPage->update($pageData);
                $this->info("Updated department home page for {$team->name}");
            } else {
                LayupPage::create($pageData);
                $this->info("Created department home page for {$team->name}");
            }

            $created++;
        }

        $this->info("Completed! Created/Updated: {$created}, Skipped: {$skipped}");

        return self::SUCCESS;
    }

    private function generateDefaultContent(Team $team): array
    {
        return [
            'rows' => [
                [
                    'id' => 'row_'.str_replace('-', '', \Str::random(8)),
                    'order' => 0,
                    'columns' => [
                        [
                            'id' => 'col_'.str_replace('-', '', \Str::random(8)),
                            'span' => [
                                'xl' => 12,
                                'lg' => 12,
                                'md' => 12,
                                'sm' => 12,
                            ],
                            'widgets' => [
                                [
                                    'id' => 'widget_'.str_replace('-', '', \Str::random(8)),
                                    'type' => 'hero',
                                    'data' => [
                                        'heading' => "Welcome to {$team->name}",
                                        'subheading' => $team->description ?: 'Your department portal at Carnegie Mellon University',
                                        'description' => '<p>Access announcements, polls, and important department information all in one place.</p>',
                                        'alignment' => 'center',
                                        'text_align' => 'center',
                                        'primary_button_text' => 'View Announcements',
                                        'primary_button_url' => "/{$team->slug}/announcements",
                                        'secondary_button_text' => 'View All Pages',
                                        'secondary_button_url' => "/{$team->slug}/pages",
                                        'background_color' => null,
                                        'text_color' => null,
                                        'overlay_color' => '#000000',
                                        'overlay_opacity' => 0,
                                        'height' => 'auto',
                                        'padding' => [
                                            'top' => null,
                                            'right' => null,
                                            'bottom' => null,
                                            'left' => null,
                                            'unit' => null,
                                        ],
                                        'margin' => [
                                            'top' => null,
                                            'right' => null,
                                            'bottom' => null,
                                            'left' => null,
                                            'unit' => null,
                                        ],
                                        'hide_on' => [],
                                        'animation' => null,
                                        'class' => null,
                                        'id' => null,
                                    ],
                                ],
                            ],
                            'settings' => [
                                'padding' => 'p-8',
                                'background' => 'transparent',
                            ],
                        ],
                    ],
                    'settings' => [
                        'wrap' => 'wrap',
                        'align' => 'stretch',
                        'justify' => 'start',
                        'direction' => 'row',
                        'full_width' => false,
                    ],
                ],
                [
                    'id' => 'row_'.str_replace('-', '', \Str::random(8)),
                    'order' => 1,
                    'columns' => [
                        [
                            'id' => 'col_'.str_replace('-', '', \Str::random(8)),
                            'span' => [
                                'xl' => 4,
                                'lg' => 4,
                                'md' => 6,
                                'sm' => 12,
                            ],
                            'widgets' => [
                                [
                                    'id' => 'widget_'.str_replace('-', '', \Str::random(8)),
                                    'type' => 'blurb',
                                    'data' => [
                                        'title' => 'Latest News',
                                        'content' => '<p>Stay updated with the latest announcements and news from the department.</p>',
                                        'url' => "/{$team->slug}/announcements",
                                        'layout' => 'top',
                                        'text_alignment' => 'center',
                                        'media_type' => 'icon',
                                        'icon' => 'heroicon-o-newspaper',
                                        'class' => null,
                                        'text_align' => null,
                                        'text_color' => null,
                                        'background_color' => null,
                                        'padding' => [
                                            'top' => null,
                                            'right' => null,
                                            'bottom' => null,
                                            'left' => null,
                                            'unit' => null,
                                        ],
                                        'margin' => [
                                            'top' => null,
                                            'right' => null,
                                            'bottom' => null,
                                            'left' => null,
                                            'unit' => null,
                                        ],
                                        'hide_on' => [],
                                        'animation' => null,
                                        'id' => null,
                                    ],
                                ],
                            ],
                            'settings' => [
                                'padding' => 'p-4',
                                'background' => 'transparent',
                            ],
                        ],
                        [
                            'id' => 'col_'.str_replace('-', '', \Str::random(8)),
                            'span' => [
                                'xl' => 4,
                                'lg' => 4,
                                'md' => 6,
                                'sm' => 12,
                            ],
                            'widgets' => [
                                [
                                    'id' => 'widget_'.str_replace('-', '', \Str::random(8)),
                                    'type' => 'blurb',
                                    'data' => [
                                        'title' => 'Current Polls',
                                        'content' => '<p>Participate in department polls and make your voice heard.</p>',
                                        'url' => "/{$team->slug}/polls",
                                        'layout' => 'top',
                                        'text_alignment' => 'center',
                                        'media_type' => 'icon',
                                        'icon' => 'heroicon-o-chart-bar',
                                        'class' => null,
                                        'text_align' => null,
                                        'text_color' => null,
                                        'background_color' => null,
                                        'padding' => [
                                            'top' => null,
                                            'right' => null,
                                            'bottom' => null,
                                            'left' => null,
                                            'unit' => null,
                                        ],
                                        'margin' => [
                                            'top' => null,
                                            'right' => null,
                                            'bottom' => null,
                                            'left' => null,
                                            'unit' => null,
                                        ],
                                        'hide_on' => [],
                                        'animation' => null,
                                        'id' => null,
                                    ],
                                ],
                            ],
                            'settings' => [
                                'padding' => 'p-4',
                                'background' => 'transparent',
                            ],
                        ],
                        [
                            'id' => 'col_'.str_replace('-', '', \Str::random(8)),
                            'span' => [
                                'xl' => 4,
                                'lg' => 4,
                                'md' => 12,
                                'sm' => 12,
                            ],
                            'widgets' => [
                                [
                                    'id' => 'widget_'.str_replace('-', '', \Str::random(8)),
                                    'type' => 'blurb',
                                    'data' => [
                                        'title' => 'All Pages',
                                        'content' => '<p>Browse all available department pages and resources.</p>',
                                        'url' => "/{$team->slug}/pages",
                                        'layout' => 'top',
                                        'text_alignment' => 'center',
                                        'media_type' => 'icon',
                                        'icon' => 'heroicon-o-document-text',
                                        'class' => null,
                                        'text_align' => null,
                                        'text_color' => null,
                                        'background_color' => null,
                                        'padding' => [
                                            'top' => null,
                                            'right' => null,
                                            'bottom' => null,
                                            'left' => null,
                                            'unit' => null,
                                        ],
                                        'margin' => [
                                            'top' => null,
                                            'right' => null,
                                            'bottom' => null,
                                            'left' => null,
                                            'unit' => null,
                                        ],
                                        'hide_on' => [],
                                        'animation' => null,
                                        'id' => null,
                                    ],
                                ],
                            ],
                            'settings' => [
                                'padding' => 'p-4',
                                'background' => 'transparent',
                            ],
                        ],
                    ],
                    'settings' => [
                        'wrap' => 'wrap',
                        'align' => 'stretch',
                        'justify' => 'start',
                        'direction' => 'row',
                        'full_width' => false,
                    ],
                ],
            ],
        ];
    }
}
