<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create teams first
        $this->call(TeamSeeder::class);

        // Create roles and permissions
        $this->call(RolePermissionSeeder::class);

        // Create default menus for each team
        $this->call(MenuSeeder::class);

        // Create SuperAdmin user
        $superAdmin = User::factory()->create([
            'name' => 'Albert Scheuring',
            'email' => 'alberts@andrew.cmu.edu',
            'andrew_id' => 'alberts',
            'department' => 'Mellon College of Science',
            'profile_completed_at' => now(),
        ]);
        $superAdmin->assignRole('SuperAdmin');

        // Create team admins for each department
        $teams = [
            ['email' => 'kkovacs@andrew.cmu.edu', 'name' => 'Krisztina Kovacs', 'andrew_id' => 'kkovacs', 'department' => 'Biological Sciences', 'team_id' => 1],
            ['email' => 'cgilchri@andrew.cmu.edu', 'name' => 'Chris Gilchrist', 'andrew_id' => 'cgilchri', 'department' => 'Mathematical Sciences', 'team_id' => 2],
            ['email' => 'keishawd@andrew.cmu.edu', 'name' => 'Keisha Ward', 'andrew_id' => 'keishawd', 'department' => 'Chemistry', 'team_id' => 3],
            ['email' => 'hmarawi@andrew.cmu.edu', 'name' => 'Hassan Marawi', 'andrew_id' => 'hmarawi', 'department' => 'Physics', 'team_id' => 4],
        ];

        foreach ($teams as $teamData) {
            $teamAdmin = User::factory()->create([
                'name' => $teamData['name'],
                'email' => $teamData['email'],
                'andrew_id' => $teamData['andrew_id'],
                'department' => $teamData['department'],
                'current_team_id' => $teamData['team_id'],
                'profile_completed_at' => now(),
            ]);
            $teamAdmin->assignRole('TeamAdmin');
        }

        // Create test student user for development
        $testStudent = User::factory()->create([
            'name' => 'Test Student',
            'email' => 'teststudent@andrew.cmu.edu',
            'andrew_id' => 'teststudent',
            'department' => 'Biological Sciences',
            'current_team_id' => 1,
            'year_in_program' => 'Junior',
            'major' => 'Biology',
            'profile_completed_at' => now(),
        ]);
        $testStudent->assignRole('Student');
    }
}
