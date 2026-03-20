<?php

use App\Models\User;
use App\Models\Team;
use App\Models\Sidebar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('debugs notification positioning with screenshots', function () {
    // Create roles
    Role::create(['name' => 'SuperAdmin']);
    Role::create(['name' => 'TeamAdmin']);
    Role::create(['name' => 'Student']);

    // Create a team
    $team = Team::factory()->create([
        'name' => 'Test Department',
        'slug' => 'test',
        'manager_email' => 'manager@test.cmu.edu',
    ]);

    // Create a super admin user
    $user = User::factory()->create([
        'email' => 'alberts@andrew.cmu.edu',
        'name' => 'Albert Scheuring',
        'current_team_id' => $team->id,
        'department' => 'Test Department',
        'year_in_program' => 'Graduate',
        'andrew_id' => 'alberts',
        'profile_completed_at' => now(),
    ]);

    // Assign SuperAdmin role
    $user->assignRole('SuperAdmin');

    // Create a sidebar to edit
    $sidebar = Sidebar::factory()->create([
        'team_id' => $team->id,
        'name' => 'Test Sidebar',
        'title' => 'Test Title',
        'content' => '<p>Test content</p>',
    ]);

    $this->actingAs($user);

    // Visit the sidebar edit page directly
    $page = visit('/admin/sidebars/' . $sidebar->id . '/edit');

    // Take initial screenshot
    $page->screenshot();

    // Create and inject test notification
    $page->script('
        // Remove any existing test notifications
        document.querySelectorAll(".test-notification").forEach(el => el.remove());

        // Create test notification container
        const container = document.createElement("div");
        container.className = "fi-notifications test-notification";
        container.style.cssText = `
            position: fixed !important;
            bottom: 16px !important;
            right: 16px !important;
            z-index: 2147483647 !important;
            max-width: 400px !important;
            transform: translateZ(0) !important;
            background: rgba(255, 0, 0, 0.1) !important;
            border: 2px solid red !important;
            padding: 8px !important;
        `;

        const notification = document.createElement("div");
        notification.className = "fi-notification";
        notification.style.cssText = `
            background: #dc2626 !important;
            color: white !important;
            padding: 16px !important;
            border-radius: 8px !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
            font-weight: bold !important;
            text-align: center !important;
        `;
        notification.innerHTML = "🔴 TEST NOTIFICATION<br>Should be in BOTTOM RIGHT!";

        container.appendChild(notification);
        document.body.appendChild(container);

        // Log all the information we need
        const rect = container.getBoundingClientRect();
        const headerRect = document.querySelector(".cmu-header")?.getBoundingClientRect();

        console.log("=== NOTIFICATION DEBUG INFO ===");
        console.log("Notification position:", rect);
        console.log("Header position:", headerRect);
        console.log("Window size:", window.innerWidth, "x", window.innerHeight);
        console.log("Computed z-index:", window.getComputedStyle(container).zIndex);
        console.log("Computed position:", window.getComputedStyle(container).position);

        return {
            notification: rect,
            header: headerRect,
            viewport: { width: window.innerWidth, height: window.innerHeight }
        };
    ');

    // Take screenshot with injected notification
    $page->screenshot();

    // Also try adding to different locations to test
    $page->script('
        // Try adding to different parent elements
        const layouts = [".fi-layout", ".fi-main", "body"];

        layouts.forEach((selector, index) => {
            const parent = document.querySelector(selector);
            if (parent) {
                const testDiv = document.createElement("div");
                testDiv.style.cssText = `
                    position: fixed !important;
                    top: ${20 + (index * 30)}px !important;
                    right: 20px !important;
                    z-index: 2147483647 !important;
                    background: lime !important;
                    padding: 8px !important;
                    border: 1px solid green !important;
                    font-size: 12px !important;
                `;
                testDiv.textContent = `Test in ${selector}`;
                parent.appendChild(testDiv);
            }
        });
    ');

    // Take final screenshot showing all test elements
    $page->screenshot();

    expect(true)->toBeTrue(); // Simple assertion to make test pass
});