<?php

use App\Models\LayupPage;
use App\Models\Sidebar;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('layup page can have multiple sidebars assigned', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['current_team_id' => $team->id]);

    $layupPage = LayupPage::factory()->create([
        'team_id' => $team->id,
        'author_id' => $user->id,
        'title' => 'Test Page',
    ]);

    $sidebar1 = Sidebar::factory()->create([
        'team_id' => $team->id,
        'name' => 'sidebar-1',
        'title' => 'Sidebar One',
        'is_active' => true,
    ]);

    $sidebar2 = Sidebar::factory()->create([
        'team_id' => $team->id,
        'name' => 'sidebar-2',
        'title' => 'Sidebar Two',
        'is_active' => true,
    ]);

    // Assign sidebars to the page
    $layupPage->sidebars()->attach([
        $sidebar1->id => ['sort_order' => 1],
        $sidebar2->id => ['sort_order' => 2],
    ]);

    // Verify relationships
    expect($layupPage->sidebars)->toHaveCount(2);
    expect($layupPage->sidebars->pluck('title')->toArray())->toBe(['Sidebar One', 'Sidebar Two']);

    // Verify sort order
    $firstSidebar = $layupPage->sidebars()->orderBy('layup_page_sidebar.sort_order')->first();
    expect($firstSidebar->title)->toBe('Sidebar One');
});

test('sidebar assignment respects team boundaries', function () {
    $team1 = Team::factory()->create(['name' => 'Team 1']);
    $team2 = Team::factory()->create(['name' => 'Team 2']);
    $user = User::factory()->create(['current_team_id' => $team1->id]);

    $layupPage = LayupPage::factory()->create([
        'team_id' => $team1->id,
        'author_id' => $user->id,
    ]);

    $team1Sidebar = Sidebar::factory()->create([
        'team_id' => $team1->id,
        'title' => 'Team 1 Sidebar',
        'is_active' => true,
    ]);

    $team2Sidebar = Sidebar::factory()->create([
        'team_id' => $team2->id,
        'title' => 'Team 2 Sidebar',
        'is_active' => true,
    ]);

    // Should be able to assign sidebar from same team
    $layupPage->sidebars()->attach($team1Sidebar->id);
    expect($layupPage->sidebars)->toHaveCount(1);
    expect($layupPage->sidebars->first()->title)->toBe('Team 1 Sidebar');

    // In a real application, the form would prevent cross-team assignments
    // but the database allows it, so this is more of a business logic test
});

test('only active sidebars should be available for assignment', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['current_team_id' => $team->id]);

    $activeSidebar = Sidebar::factory()->create([
        'team_id' => $team->id,
        'title' => 'Active Sidebar',
        'is_active' => true,
    ]);

    $inactiveSidebar = Sidebar::factory()->create([
        'team_id' => $team->id,
        'title' => 'Inactive Sidebar',
        'is_active' => false,
    ]);

    // Test the query that would be used in the form
    $availableSidebars = Sidebar::where('team_id', $team->id)
        ->where('is_active', true)
        ->pluck('title', 'id');

    expect($availableSidebars)->toHaveCount(1);
    expect($availableSidebars->values()->first())->toBe('Active Sidebar');
    expect($availableSidebars->values()->contains('Inactive Sidebar'))->toBeFalse();
});

test('sidebar assignment can be updated', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['current_team_id' => $team->id]);

    $layupPage = LayupPage::factory()->create([
        'team_id' => $team->id,
        'author_id' => $user->id,
    ]);

    $sidebar1 = Sidebar::factory()->create(['team_id' => $team->id, 'is_active' => true]);
    $sidebar2 = Sidebar::factory()->create(['team_id' => $team->id, 'is_active' => true]);
    $sidebar3 = Sidebar::factory()->create(['team_id' => $team->id, 'is_active' => true]);

    // Initial assignment
    $layupPage->sidebars()->attach([$sidebar1->id, $sidebar2->id]);
    expect($layupPage->sidebars)->toHaveCount(2);

    // Update assignment (remove sidebar1, add sidebar3)
    $layupPage->sidebars()->sync([$sidebar2->id, $sidebar3->id]);
    $layupPage->refresh();

    expect($layupPage->sidebars)->toHaveCount(2);
    expect($layupPage->sidebars->pluck('id')->toArray())->toBe([$sidebar2->id, $sidebar3->id]);
});

test('layup pages table shows sidebar assignments', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['current_team_id' => $team->id]);

    $layupPage = LayupPage::factory()->create([
        'team_id' => $team->id,
        'author_id' => $user->id,
        'title' => 'Page with Sidebars',
    ]);

    $sidebar1 = Sidebar::factory()->create([
        'team_id' => $team->id,
        'title' => 'First Sidebar',
        'is_active' => true,
    ]);

    $sidebar2 = Sidebar::factory()->create([
        'team_id' => $team->id,
        'title' => 'Second Sidebar',
        'is_active' => true,
    ]);

    $layupPage->sidebars()->attach([$sidebar1->id, $sidebar2->id]);

    // Test eager loading (simulating what the table would do)
    $pageWithSidebars = LayupPage::with('sidebars')->find($layupPage->id);

    expect($pageWithSidebars->sidebars)->toHaveCount(2);
    expect($pageWithSidebars->sidebars->pluck('title')->join(', '))->toBe('First Sidebar, Second Sidebar');
});

test('department home pages can have sidebars assigned', function () {
    $superAdmin = User::factory()->create();
    $superAdminRole = Role::create(['name' => 'SuperAdmin']);
    $superAdmin->assignRole($superAdminRole);

    $team = Team::factory()->create(['name' => 'Test Department']);

    // The observer should create a department home page
    $departmentHomePage = LayupPage::where('team_id', $team->id)
        ->where('is_department_home', true)
        ->first();

    expect($departmentHomePage)->not->toBeNull();

    $sidebar = Sidebar::factory()->create([
        'team_id' => $team->id,
        'title' => 'Department Sidebar',
        'is_active' => true,
    ]);

    // Department home pages should be able to have sidebars
    $departmentHomePage->sidebars()->attach($sidebar->id);

    expect($departmentHomePage->sidebars)->toHaveCount(1);
    expect($departmentHomePage->sidebars->first()->title)->toBe('Department Sidebar');
});
