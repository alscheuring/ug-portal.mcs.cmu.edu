<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('   📋 Creating permissions...');

        // Create permissions
        $permissions = [
            // User management
            'manage users',
            'view users',
            'create users',
            'edit users',
            'delete users',

            // Team management
            'manage teams',
            'view teams',
            'create teams',
            'edit teams',
            'delete teams',

            // Content management
            'manage content',
            'view content',
            'create content',
            'edit content',
            'delete content',
            'publish content',

            // Menu management
            'manage menus',
            'view menus',
            'create menus',
            'edit menus',
            'delete menus',

            // Announcement management
            'manage announcements',
            'view announcements',
            'create announcements',
            'edit announcements',
            'delete announcements',
            'publish announcements',

            // Poll management
            'manage polls',
            'view polls',
            'create polls',
            'edit polls',
            'delete polls',
            'participate polls',
        ];

        foreach ($permissions as $permissionName) {
            try {
                Permission::firstOrCreate(['name' => $permissionName]);
            } catch (\Exception $e) {
                $this->command->warn("      Warning: Could not create permission '{$permissionName}': ".$e->getMessage());
            }
        }

        $this->command->info('   👤 Creating roles...');

        // Create roles and assign permissions
        $superAdminRole = Role::firstOrCreate(['name' => 'SuperAdmin']);
        $superAdminRole->syncPermissions(Permission::all());
        $this->command->info('      ✓ SuperAdmin role configured');

        $teamAdminRole = Role::firstOrCreate(['name' => 'TeamAdmin']);
        $teamAdminPermissions = [
            'view users',
            'create users',
            'edit users',
            'view teams',
            'manage content',
            'view content',
            'create content',
            'edit content',
            'delete content',
            'publish content',
            'manage menus',
            'view menus',
            'create menus',
            'edit menus',
            'delete menus',
            'manage announcements',
            'view announcements',
            'create announcements',
            'edit announcements',
            'delete announcements',
            'publish announcements',
            'manage polls',
            'view polls',
            'create polls',
            'edit polls',
            'delete polls',
        ];
        $teamAdminRole->syncPermissions($teamAdminPermissions);
        $this->command->info('      ✓ TeamAdmin role configured');

        $studentRole = Role::firstOrCreate(['name' => 'Student']);
        $studentPermissions = [
            'view content',
            'view announcements',
            'view polls',
            'participate polls',
        ];
        $studentRole->syncPermissions($studentPermissions);
        $this->command->info('      ✓ Student role configured');

        // Reset cached roles and permissions after changes
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
