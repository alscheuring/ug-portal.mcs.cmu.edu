<?php

namespace App\Observers;

use App\Models\LayupPage;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class TeamObserver
{
    /**
     * Handle the Team "created" event.
     */
    public function created(Team $team): void
    {
        try {
            // Find a super admin user to be the author
            $author = User::whereHas('roles', function ($query) {
                $query->where('name', 'SuperAdmin');
            })->first();

            if (! $author) {
                Log::warning("Cannot create department home page for team '{$team->name}': No SuperAdmin user found");

                return;
            }

            // Create the department home page
            LayupPage::create([
                'title' => $team->name,
                'slug' => $team->slug,
                'content' => $this->generateDefaultContent($team),
                'status' => 'published',
                'meta' => [],
                'team_id' => $team->id,
                'author_id' => $author->id,
                'published_at' => now(),
                'is_department_home' => true,
            ]);

            Log::info("Created department home page for team '{$team->name}' ({$team->slug})");
        } catch (\Exception $e) {
            Log::error("Failed to create department home page for team '{$team->name}': {$e->getMessage()}");
        }
    }

    /**
     * Handle the Team "updated" event.
     */
    public function updated(Team $team): void
    {
        //
    }

    /**
     * Handle the Team "deleted" event.
     */
    public function deleted(Team $team): void
    {
        //
    }

    /**
     * Handle the Team "restored" event.
     */
    public function restored(Team $team): void
    {
        //
    }

    /**
     * Handle the Team "force deleted" event.
     */
    public function forceDeleted(Team $team): void
    {
        //
    }

    /**
     * Generate default content for a new department home page.
     */
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
            ],
        ];
    }
}
