<?php

namespace Database\Seeders;

use App\Models\Team;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting database seeding...');

        // Create teams first
        $this->command->info('📂 Seeding teams...');
        $this->call(TeamSeeder::class);

        // Create roles and permissions
        $this->command->info('🔐 Seeding roles and permissions...');
        $this->call(RolePermissionSeeder::class);

        // Create default menus for each team
        $this->command->info('📋 Seeding navigation menus...');
        $this->call(MenuSeeder::class);

        $this->command->info('👥 Creating user accounts...');

        // Create SuperAdmin user
        $superAdmin = $this->createOrUpdateUser([
            'name' => 'Albert Scheuring',
            'email' => 'alberts@andrew.cmu.edu',
            'andrew_id' => 'alberts',
            'department' => 'Mellon College of Science',
            'profile_completed_at' => now(),
        ], 'SuperAdmin');

        // Create team admins for each department
        $teamAdmins = [
            ['email' => 'kkovacs@andrew.cmu.edu', 'name' => 'Krisztina Kovacs', 'andrew_id' => 'kkovacs', 'department' => 'Biological Sciences', 'team_slug' => 'biosci'],
            ['email' => 'cgilchri@andrew.cmu.edu', 'name' => 'Chris Gilchrist', 'andrew_id' => 'cgilchri', 'department' => 'Mathematical Sciences', 'team_slug' => 'math'],
            ['email' => 'keishawd@andrew.cmu.edu', 'name' => 'Keisha Ward', 'andrew_id' => 'keishawd', 'department' => 'Chemistry', 'team_slug' => 'chemistry'],
            ['email' => 'hmarawi@andrew.cmu.edu', 'name' => 'Hassan Marawi', 'andrew_id' => 'hmarawi', 'department' => 'Physics', 'team_slug' => 'physics'],
        ];

        foreach ($teamAdmins as $adminData) {
            $team = Team::where('slug', $adminData['team_slug'])->first();
            $teamId = $team ? $team->id : null;

            $this->createOrUpdateUser([
                'name' => $adminData['name'],
                'email' => $adminData['email'],
                'andrew_id' => $adminData['andrew_id'],
                'department' => $adminData['department'],
                'current_team_id' => $teamId,
                'profile_completed_at' => now(),
            ], 'TeamAdmin');
        }

        // Create test student user for development
        $team = Team::where('slug', 'biosci')->first();
        $this->createOrUpdateUser([
            'name' => 'Test Student',
            'email' => 'teststudent@andrew.cmu.edu',
            'andrew_id' => 'teststudent',
            'department' => 'Biological Sciences',
            'current_team_id' => $team ? $team->id : null,
            'year_in_program' => 'Junior',
            'major' => 'Biology',
            'profile_completed_at' => now(),
        ], 'Student');

        $this->command->info('✅ Database seeding completed successfully!');
        $this->command->info('📊 Final counts:');
        $this->command->info('   - Teams: '.Team::count());
        $this->command->info('   - Users: '.User::count());
        $this->command->info('   - Roles: '.Role::count());
        $this->command->info('   - Permissions: '.Permission::count());
    }

    /**
     * Create or update a user safely.
     */
    private function createOrUpdateUser(array $userData, string $roleName): User
    {
        try {
            $user = User::where('email', $userData['email'])->first();

            if ($user) {
                // Update existing user
                $user->update($userData);
                $this->command->info("   ✓ Updated existing user: {$userData['name']} ({$userData['email']})");
            } else {
                // Create new user
                $user = User::factory()->create($userData);
                $this->command->info("   ✓ Created new user: {$userData['name']} ({$userData['email']})");
            }

            // Assign role (this will handle existing roles gracefully)
            if (! $user->hasRole($roleName)) {
                $user->assignRole($roleName);
                $this->command->info("     → Assigned role: {$roleName}");
            }

            return $user;
        } catch (\Exception $e) {
            $this->command->error("   ✗ Error creating/updating user {$userData['email']}: ".$e->getMessage());

            // Try a fallback approach
            try {
                $user = User::updateOrCreate(
                    ['email' => $userData['email']],
                    $userData
                );

                if (! $user->hasRole($roleName)) {
                    $user->assignRole($roleName);
                }

                $this->command->info("   ✓ Successfully processed user via fallback: {$userData['name']}");

                return $user;
            } catch (\Exception $fallbackError) {
                $this->command->error("   ✗ Failed to create user {$userData['email']}: ".$fallbackError->getMessage());
                throw $fallbackError;
            }
        }
    }
}
