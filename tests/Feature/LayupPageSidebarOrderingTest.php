<?php

use App\Models\LayupPage;
use App\Models\Sidebar;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('layup page sidebars maintain proper sort order when assigned', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['current_team_id' => $team->id]);

    $layupPage = LayupPage::factory()->create([
        'team_id' => $team->id,
        'author_id' => $user->id,
        'title' => 'Test Page with Ordered Sidebars',
    ]);

    // Create multiple sidebars
    $sidebar1 = Sidebar::factory()->create([
        'team_id' => $team->id,
        'name' => 'first-sidebar',
        'title' => 'First Sidebar',
        'is_active' => true,
    ]);

    $sidebar2 = Sidebar::factory()->create([
        'team_id' => $team->id,
        'name' => 'second-sidebar',
        'title' => 'Second Sidebar',
        'is_active' => true,
    ]);

    $sidebar3 = Sidebar::factory()->create([
        'team_id' => $team->id,
        'name' => 'third-sidebar',
        'title' => 'Third Sidebar',
        'is_active' => true,
    ]);

    // Assign sidebars in a specific order (3, 1, 2)
    $layupPage->sidebars()->sync([
        $sidebar3->id => ['sort_order' => 0],
        $sidebar1->id => ['sort_order' => 1],
        $sidebar2->id => ['sort_order' => 2],
    ]);

    // Refresh the page and verify ordering
    $layupPage->refresh();
    $orderedSidebars = $layupPage->sidebars()->orderBy('layup_page_sidebar.sort_order')->get();

    expect($orderedSidebars)->toHaveCount(3);
    expect($orderedSidebars[0]->id)->toBe($sidebar3->id);
    expect($orderedSidebars[1]->id)->toBe($sidebar1->id);
    expect($orderedSidebars[2]->id)->toBe($sidebar2->id);

    // Verify sort_order values
    expect($orderedSidebars[0]->pivot->sort_order)->toBe(0);
    expect($orderedSidebars[1]->pivot->sort_order)->toBe(1);
    expect($orderedSidebars[2]->pivot->sort_order)->toBe(2);
});

test('layup page sidebars can be reordered', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['current_team_id' => $team->id]);

    $layupPage = LayupPage::factory()->create([
        'team_id' => $team->id,
        'author_id' => $user->id,
        'title' => 'Test Page for Reordering',
    ]);

    $sidebar1 = Sidebar::factory()->create(['team_id' => $team->id, 'title' => 'Alpha', 'is_active' => true]);
    $sidebar2 = Sidebar::factory()->create(['team_id' => $team->id, 'title' => 'Beta', 'is_active' => true]);
    $sidebar3 = Sidebar::factory()->create(['team_id' => $team->id, 'title' => 'Gamma', 'is_active' => true]);

    // Initial order: Alpha, Beta, Gamma
    $layupPage->sidebars()->sync([
        $sidebar1->id => ['sort_order' => 0],
        $sidebar2->id => ['sort_order' => 1],
        $sidebar3->id => ['sort_order' => 2],
    ]);

    $initialOrder = $layupPage->sidebars()->orderBy('layup_page_sidebar.sort_order')->pluck('title')->toArray();
    expect($initialOrder)->toBe(['Alpha', 'Beta', 'Gamma']);

    // Reorder to: Gamma, Alpha, Beta
    $layupPage->sidebars()->sync([
        $sidebar3->id => ['sort_order' => 0],
        $sidebar1->id => ['sort_order' => 1],
        $sidebar2->id => ['sort_order' => 2],
    ]);

    $newOrder = $layupPage->sidebars()->orderBy('layup_page_sidebar.sort_order')->pluck('title')->toArray();
    expect($newOrder)->toBe(['Gamma', 'Alpha', 'Beta']);
});

test('sidebar ordering works with single sidebar', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['current_team_id' => $team->id]);

    $layupPage = LayupPage::factory()->create([
        'team_id' => $team->id,
        'author_id' => $user->id,
        'title' => 'Single Sidebar Page',
    ]);

    $sidebar = Sidebar::factory()->create([
        'team_id' => $team->id,
        'title' => 'Only Sidebar',
        'is_active' => true,
    ]);

    $layupPage->sidebars()->sync([
        $sidebar->id => ['sort_order' => 0],
    ]);

    $sidebars = $layupPage->sidebars()->orderBy('layup_page_sidebar.sort_order')->get();

    expect($sidebars)->toHaveCount(1);
    expect($sidebars[0]->title)->toBe('Only Sidebar');
    expect($sidebars[0]->pivot->sort_order)->toBe(0);
});

test('empty sidebar assignments work correctly', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['current_team_id' => $team->id]);

    $layupPage = LayupPage::factory()->create([
        'team_id' => $team->id,
        'author_id' => $user->id,
        'title' => 'No Sidebars Page',
    ]);

    // Ensure no sidebars are assigned
    $layupPage->sidebars()->sync([]);

    $sidebars = $layupPage->sidebars;
    expect($sidebars)->toHaveCount(0);
    expect($sidebars->isEmpty())->toBeTrue();
});