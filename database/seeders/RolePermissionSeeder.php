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

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $superAdminRole = Role::create(['name' => 'SuperAdmin']);
        $superAdminRole->givePermissionTo(Permission::all());

        $teamAdminRole = Role::create(['name' => 'TeamAdmin']);
        $teamAdminRole->givePermissionTo([
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
        ]);

        $studentRole = Role::create(['name' => 'Student']);
        $studentRole->givePermissionTo([
            'view content',
            'view announcements',
            'view polls',
            'participate polls',
        ]);
    }
}
