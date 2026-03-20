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

        foreach ($teams as $team) {
            Team::create($team);
        }
    }
}
