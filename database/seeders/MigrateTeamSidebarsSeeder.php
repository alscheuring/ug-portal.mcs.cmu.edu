<?php

namespace Database\Seeders;

use App\Models\Sidebar;
use App\Models\Team;
use Illuminate\Database\Seeder;

class MigrateTeamSidebarsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Migrating existing team sidebar content to Sidebar system...');

        $teams = Team::whereNotNull('quick_links_content')
            ->orWhereNotNull('help_box_content')
            ->get();

        foreach ($teams as $team) {
            $this->command->info("Processing team: {$team->name}");

            // Migrate Quick Links sidebar if it has content
            if ($team->quick_links_content) {
                Sidebar::create([
                    'team_id' => $team->id,
                    'name' => 'Quick Links',
                    'title' => $team->quick_links_title ?: 'Quick Links',
                    'content' => $team->quick_links_content,
                    'is_active' => true,
                    'sort_order' => 1,
                ]);
                $this->command->info("  - Created 'Quick Links' sidebar");
            }

            // Migrate Help Box sidebar if it has content
            if ($team->help_box_content) {
                Sidebar::create([
                    'team_id' => $team->id,
                    'name' => 'Help & Support',
                    'title' => $team->help_box_title ?: 'Need Help?',
                    'content' => $team->help_box_content,
                    'is_active' => true,
                    'sort_order' => 2,
                ]);
                $this->command->info("  - Created 'Help & Support' sidebar");
            }
        }

        $this->command->info('Sidebar migration completed successfully.');
        $this->command->warn('Note: You can now remove the old sidebar fields from the teams table after verifying everything works correctly.');
    }
}
