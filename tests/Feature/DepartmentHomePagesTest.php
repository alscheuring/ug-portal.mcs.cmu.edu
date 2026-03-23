<?php

use App\Models\LayupPage;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('department home pages are automatically created when team is created', function () {
    // Create a super admin user
    $superAdmin = User::factory()->create();
    $superAdminRole = Role::create(['name' => 'SuperAdmin']);
    $superAdmin->assignRole($superAdminRole);

    // Create a new team
    $team = Team::factory()->create([
        'name' => 'Test Department',
        'slug' => 'test-dept',
    ]);

    // Check that a department home page was automatically created
    $departmentHomePage = LayupPage::where('team_id', $team->id)
        ->where('slug', $team->slug)
        ->where('is_department_home', true)
        ->first();

    expect($departmentHomePage)->not()->toBeNull();
    expect($departmentHomePage->title)->toBe($team->name);
    expect($departmentHomePage->status)->toBe('published');
    expect($departmentHomePage->is_department_home)->toBeTrue();
});

test('department home pages cannot be deleted', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create();

    $departmentHomePage = LayupPage::factory()->create([
        'team_id' => $team->id,
        'author_id' => $user->id,
        'slug' => $team->slug,
        'is_department_home' => true,
    ]);

    $regularPage = LayupPage::factory()->create([
        'team_id' => $team->id,
        'author_id' => $user->id,
        'is_department_home' => false,
    ]);

    // Department home pages should not be deletable
    expect($departmentHomePage->isDepartmentHome())->toBeTrue();

    // Regular pages should be deletable
    expect($regularPage->isDepartmentHome())->toBeFalse();
});

test('department home page routing works correctly', function () {
    $team = Team::factory()->create(['slug' => 'test-routing']);
    $user = User::factory()->create();

    // Create a department home page
    $departmentHomePage = LayupPage::factory()->create([
        'team_id' => $team->id,
        'author_id' => $user->id,
        'slug' => $team->slug,
        'is_department_home' => true,
        'status' => 'published',
        'published_at' => now(),
    ]);

    // Test that the team URL serves the LayupPage
    $response = $this->get("/{$team->slug}");

    $response->assertStatus(200);
    $response->assertViewIs('layup-pages.show');
    $response->assertViewHas('page', $departmentHomePage);
});

test('command creates department home pages for all active teams', function () {
    // Create super admin user
    $superAdmin = User::factory()->create();
    $superAdminRole = Role::create(['name' => 'SuperAdmin']);
    $superAdmin->assignRole($superAdminRole);

    // Create multiple teams
    $team1 = Team::factory()->create(['name' => 'Team One', 'slug' => 'team-one']);
    $team2 = Team::factory()->create(['name' => 'Team Two', 'slug' => 'team-two']);
    $inactiveTeam = Team::factory()->create(['name' => 'Inactive Team', 'slug' => 'inactive', 'is_active' => false]);

    // Delete any auto-created pages first (force delete to avoid unique constraint issues)
    LayupPage::where('is_department_home', true)->forceDelete();

    // Run the command
    $this->artisan('department:create-home-pages')
        ->expectsOutput('Creating department home pages...')
        ->expectsOutput('Created department home page for Team One')
        ->expectsOutput('Created department home page for Team Two')
        ->expectsOutput('Completed! Created/Updated: 2, Skipped: 0')
        ->assertExitCode(0);

    // Verify pages were created for active teams
    expect(LayupPage::where('team_id', $team1->id)->where('is_department_home', true)->exists())->toBeTrue();
    expect(LayupPage::where('team_id', $team2->id)->where('is_department_home', true)->exists())->toBeTrue();

    // Verify no page was created for inactive team
    expect(LayupPage::where('team_id', $inactiveTeam->id)->where('is_department_home', true)->exists())->toBeFalse();
});

test('scope filters department home pages correctly', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create();

    $departmentHomePage = LayupPage::factory()->create([
        'team_id' => $team->id,
        'author_id' => $user->id,
        'is_department_home' => true,
    ]);

    $regularPage = LayupPage::factory()->create([
        'team_id' => $team->id,
        'author_id' => $user->id,
        'is_department_home' => false,
    ]);

    $departmentHomePages = LayupPage::departmentHome()->get();

    expect($departmentHomePages)->toHaveCount(1);
    expect($departmentHomePages->first()->id)->toBe($departmentHomePage->id);
});
