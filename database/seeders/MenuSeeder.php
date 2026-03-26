<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Team;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teams = Team::all();

        foreach ($teams as $team) {
            $menuSlug = $team->slug.'-nav';

            // Create or find main navigation menu for each team
            $menu = Menu::firstOrCreate(
                ['slug' => $menuSlug, 'team_id' => $team->id],
                [
                    'name' => $team->name.' Navigation',
                    'slug' => $menuSlug,
                    'description' => 'Main navigation menu for '.$team->name,
                    'team_id' => $team->id,
                    'is_active' => true,
                ]
            );

            if ($menu->wasRecentlyCreated) {
                $this->command->info("      ✓ Created menu: {$menu->name}");
                // Create default menu items only for new menus
                $this->createDefaultMenuItems($menu, $team);
            } else {
                $this->command->info("      → Menu already exists: {$menu->name}");
                // Optionally update existing menu items here if needed
            }
        }
    }

    private function createDefaultMenuItems(Menu $menu, Team $team): void
    {
        $menuItems = [];

        // About section
        $menuItems[] = [
            'menu_id' => $menu->id,
            'title' => 'About',
            'link_type' => 'external',
            'external_url' => $this->getAboutUrl($team->slug),
            'opens_in_new_tab' => false,
            'sort_order' => 10,
            'is_visible' => true,
            'description' => 'About '.$team->name,
        ];

        // Research section
        $menuItems[] = [
            'menu_id' => $menu->id,
            'title' => 'Research',
            'link_type' => 'external',
            'external_url' => $this->getResearchUrl($team->slug),
            'opens_in_new_tab' => false,
            'sort_order' => 20,
            'is_visible' => true,
            'description' => 'Research in '.$team->name,
        ];

        // People section
        $menuItems[] = [
            'menu_id' => $menu->id,
            'title' => 'People',
            'link_type' => 'external',
            'external_url' => $this->getPeopleUrl($team->slug),
            'opens_in_new_tab' => false,
            'sort_order' => 30,
            'is_visible' => true,
            'description' => 'Faculty and staff in '.$team->name,
        ];

        // Graduate Programs
        $menuItems[] = [
            'menu_id' => $menu->id,
            'title' => 'Graduate Programs',
            'link_type' => 'external',
            'external_url' => $this->getGradUrl($team->slug),
            'opens_in_new_tab' => false,
            'sort_order' => 40,
            'is_visible' => true,
            'description' => 'Graduate programs in '.$team->name,
        ];

        // Undergraduate Programs
        $menuItems[] = [
            'menu_id' => $menu->id,
            'title' => 'Undergraduate',
            'link_type' => 'external',
            'external_url' => $this->getUndergradUrl($team->slug),
            'opens_in_new_tab' => false,
            'sort_order' => 50,
            'is_visible' => true,
            'description' => 'Undergraduate programs in '.$team->name,
        ];

        // External link to main department
        $menuItems[] = [
            'menu_id' => $menu->id,
            'title' => 'Department Website',
            'link_type' => 'external',
            'external_url' => $this->getDepartmentUrl($team->slug),
            'opens_in_new_tab' => true,
            'sort_order' => 60,
            'is_visible' => true,
            'description' => 'Visit the main '.$team->name.' website',
            'icon' => 'heroicon-o-arrow-top-right-on-square',
        ];

        foreach ($menuItems as $item) {
            try {
                MenuItem::firstOrCreate(
                    [
                        'menu_id' => $item['menu_id'],
                        'title' => $item['title'],
                    ],
                    $item
                );
                $this->command->info("        → Menu item: {$item['title']}");
            } catch (\Exception $e) {
                $this->command->warn("        Warning: Could not create menu item '{$item['title']}': ".$e->getMessage());
            }
        }
    }

    private function getAboutUrl(string $slug): string
    {
        return match ($slug) {
            'biosci' => 'https://www.cmu.edu/bio/about/',
            'math' => 'https://www.math.cmu.edu/about/',
            'chemistry' => 'https://www.cmu.edu/chemistry/about/',
            'physics' => 'https://www.cmu.edu/physics/about/',
            default => 'https://www.cmu.edu/mcs/',
        };
    }

    private function getResearchUrl(string $slug): string
    {
        return match ($slug) {
            'biosci' => 'https://www.cmu.edu/bio/research/',
            'math' => 'https://www.math.cmu.edu/research/',
            'chemistry' => 'https://www.cmu.edu/chemistry/research/',
            'physics' => 'https://www.cmu.edu/physics/research/',
            default => 'https://www.cmu.edu/mcs/research/',
        };
    }

    private function getPeopleUrl(string $slug): string
    {
        return match ($slug) {
            'biosci' => 'https://www.cmu.edu/bio/people/',
            'math' => 'https://www.math.cmu.edu/people/',
            'chemistry' => 'https://www.cmu.edu/chemistry/people/',
            'physics' => 'https://www.cmu.edu/physics/people/',
            default => 'https://www.cmu.edu/mcs/people/',
        };
    }

    private function getGradUrl(string $slug): string
    {
        return match ($slug) {
            'biosci' => 'https://www.cmu.edu/bio/graduate/',
            'math' => 'https://www.math.cmu.edu/graduate/',
            'chemistry' => 'https://www.cmu.edu/chemistry/graduate/',
            'physics' => 'https://www.cmu.edu/physics/graduate/',
            default => 'https://www.cmu.edu/mcs/graduate/',
        };
    }

    private function getUndergradUrl(string $slug): string
    {
        return match ($slug) {
            'biosci' => 'https://www.cmu.edu/bio/undergraduate/',
            'math' => 'https://www.math.cmu.edu/undergraduate/',
            'chemistry' => 'https://www.cmu.edu/chemistry/undergraduate/',
            'physics' => 'https://www.cmu.edu/physics/undergraduate/',
            default => 'https://www.cmu.edu/mcs/undergraduate/',
        };
    }

    private function getDepartmentUrl(string $slug): string
    {
        return match ($slug) {
            'biosci' => 'https://www.cmu.edu/bio/',
            'math' => 'https://www.math.cmu.edu/',
            'chemistry' => 'https://www.cmu.edu/chemistry/',
            'physics' => 'https://www.cmu.edu/physics/',
            default => 'https://www.cmu.edu/mcs/',
        };
    }
}
