<?php

use App\Models\LayupPage;
use App\Models\Sidebar;
use App\Models\Team;
use App\Models\User;

test('sidebar can have many-to-many relationship with layup pages', function () {
    // Create a team, user, sidebar and layup page
    $team = Team::factory()->create();
    $user = User::factory()->create(['current_team_id' => $team->id]);

    $sidebar = Sidebar::factory()->create(['team_id' => $team->id]);
    $layupPage = LayupPage::factory()->create([
        'team_id' => $team->id,
        'author_id' => $user->id,
    ]);

    // Attach the sidebar to the page
    $sidebar->pages()->attach($layupPage->id, ['sort_order' => 1]);

    // Test the relationship from both sides
    expect($sidebar->pages)->toHaveCount(1);
    expect($sidebar->pages->first()->id)->toBe($layupPage->id);
    expect($layupPage->sidebars)->toHaveCount(1);
    expect($layupPage->sidebars->first()->id)->toBe($sidebar->id);
});

test('sidebar pages count works correctly', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['current_team_id' => $team->id]);

    $sidebar = Sidebar::factory()->create(['team_id' => $team->id]);

    // Initially no pages
    expect($sidebar->pages()->count())->toBe(0);

    // Create and attach some layup pages
    $page1 = LayupPage::factory()->create(['team_id' => $team->id, 'author_id' => $user->id]);
    $page2 = LayupPage::factory()->create(['team_id' => $team->id, 'author_id' => $user->id]);

    $sidebar->pages()->attach([$page1->id, $page2->id]);

    // Should now have 2 pages
    expect($sidebar->pages()->count())->toBe(2);
    expect($sidebar->loadCount('pages')->pages_count)->toBe(2);
});
