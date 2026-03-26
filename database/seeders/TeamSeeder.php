<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teams = [
            [
                'name' => 'Biological Sciences',
                'slug' => 'biosci',
                'description' => 'Department of Biological Sciences at Carnegie Mellon University',
                'manager_email' => 'kkovacs@andrew.cmu.edu',
                'is_active' => true,
            ],
            [
                'name' => 'Mathematical Sciences',
                'slug' => 'math',
                'description' => 'Department of Mathematical Sciences at Carnegie Mellon University',
                'manager_email' => 'cgilchri@andrew.cmu.edu',
                'is_active' => true,
            ],
            [
                'name' => 'Chemistry',
                'slug' => 'chemistry',
                'description' => 'Department of Chemistry at Carnegie Mellon University',
                'manager_email' => 'keishawd@andrew.cmu.edu',
                'is_active' => true,
            ],
            [
                'name' => 'Physics',
                'slug' => 'physics',
                'description' => 'Department of Physics at Carnegie Mellon University',
                'manager_email' => 'hmarawi@andrew.cmu.edu',
                'is_active' => true,
            ],
        ];

        foreach ($teams as $teamData) {
            try {
                $team = Team::where('slug', $teamData['slug'])->first();

                if ($team) {
                    // Update existing team with new data
                    $team->update($teamData);
                    $this->command->info("Updated existing team: {$teamData['name']} ({$teamData['slug']})");
                } else {
                    // Create new team
                    Team::create($teamData);
                    $this->command->info("Created new team: {$teamData['name']} ({$teamData['slug']})");
                }
            } catch (\Exception $e) {
                $this->command->warn("Error processing team {$teamData['slug']}: ".$e->getMessage());

                // Try one more time with updateOrCreate as fallback
                try {
                    Team::updateOrCreate(
                        ['slug' => $teamData['slug']],
                        $teamData
                    );
                    $this->command->info("Successfully processed team using fallback: {$teamData['name']}");
                } catch (\Exception $fallbackError) {
                    $this->command->error("Failed to create/update team {$teamData['slug']}: ".$fallbackError->getMessage());
                }
            }
        }

        $this->command->info('Team seeding completed. Total teams: '.Team::count());
    }
}
