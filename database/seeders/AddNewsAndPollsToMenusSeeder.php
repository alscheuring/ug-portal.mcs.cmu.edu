<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Team;
use Illuminate\Database\Seeder;

class AddNewsAndPollsToMenusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teams = Team::all();

        foreach ($teams as $team) {
            $menu = Menu::forTeam($team->id)->active()->first();

            if (! $menu) {
                $this->command->warn("No active menu found for team: {$team->name}");

                continue;
            }

            // Get the highest sort order for this menu
            $maxSortOrder = $menu->items()->max('sort_order') ?? 0;

            // Check if News/Announcements already exists (properly scoped to this menu)
            $hasNews = $menu->items()
                ->where('menu_id', $menu->id)
                ->where(function ($query) {
                    $query->where('link_type', 'announcements')
                        ->orWhere('title', 'News')
                        ->orWhere('title', 'Announcements');
                })
                ->exists();

            if (! $hasNews) {
                MenuItem::create([
                    'menu_id' => $menu->id,
                    'title' => 'News',
                    'link_type' => 'announcements',
                    'sort_order' => $maxSortOrder + 10,
                    'is_visible' => true,
                ]);
                $this->command->info("Added News to {$team->name} menu");
            } else {
                $this->command->info("News already exists in {$team->name} menu");
            }

            // Check if Polls already exists (properly scoped to this menu)
            $hasPolls = $menu->items()
                ->where('menu_id', $menu->id)
                ->where(function ($query) {
                    $query->where('link_type', 'polls')
                        ->orWhere('title', 'Polls');
                })
                ->exists();

            if (! $hasPolls) {
                MenuItem::create([
                    'menu_id' => $menu->id,
                    'title' => 'Polls',
                    'link_type' => 'polls',
                    'sort_order' => $maxSortOrder + 20,
                    'is_visible' => true,
                ]);
                $this->command->info("Added Polls to {$team->name} menu");
            } else {
                $this->command->info("Polls already exists in {$team->name} menu");
            }
        }

        $this->command->info('Finished adding News and Polls menu items to all teams.');
    }
}
