<?php

use App\Models\Page;
use App\Models\Team;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create roles
    Role::firstOrCreate(['name' => 'SuperAdmin']);
    Role::firstOrCreate(['name' => 'TeamAdmin']);
    Role::firstOrCreate(['name' => 'Student']);
});

it('creates pages with proper team scoping', function () {
    $team = Team::factory()->create(['name' => 'Test Team', 'slug' => 'test-team', 'manager_email' => 'manager@test.com']);
    $user = User::factory()->create(['current_team_id' => $team->id]);
    $user->assignRole('TeamAdmin');

    $page = Page::create([
        'title' => 'Test Page',
        'slug' => 'test-page',
        'content' => '<h1>Test Content</h1><p>This is a test page.</p>',
        'is_published' => true,
        'published_at' => now(),
        'team_id' => $team->id,
        'author_id' => $user->id,
    ]);

    expect($page->title)->toBe('Test Page');
    expect($page->team_id)->toBe($team->id);
    expect($page->author_id)->toBe($user->id);
    expect($page->isPublished())->toBeTrue();
    expect($page->team->name)->toBe('Test Team');
    expect($page->author->id)->toBe($user->id);
    expect($page->url)->toBe('/test-team/test-page');
});

it('generates unique slugs within teams', function () {
    $team1 = Team::factory()->create(['name' => 'Team 1', 'slug' => 'team1', 'manager_email' => 'manager1@test.com']);
    $team2 = Team::factory()->create(['name' => 'Team 2', 'slug' => 'team2', 'manager_email' => 'manager2@test.com']);
    $user = User::factory()->create();

    // Create pages with same title in different teams
    $page1 = Page::create([
        'title' => 'About Us',
        'slug' => 'about-us',
        'content' => 'Team 1 content',
        'team_id' => $team1->id,
        'author_id' => $user->id,
    ]);

    $page2 = Page::create([
        'title' => 'About Us',
        'slug' => 'about-us',
        'content' => 'Team 2 content',
        'team_id' => $team2->id,
        'author_id' => $user->id,
    ]);

    expect($page1->slug)->toBe('about-us');
    expect($page2->slug)->toBe('about-us');
    expect($page1->url)->toBe('/team1/about-us');
    expect($page2->url)->toBe('/team2/about-us');

    // Test slug generation for duplicate within same team
    $slug = Page::generateSlug('About Us', $team1->id);
    expect($slug)->toBe('about-us-1');
});

it('handles hierarchical pages correctly', function () {
    $team = Team::factory()->create(['name' => 'Test Team', 'slug' => 'test-team', 'manager_email' => 'manager@test.com']);
    $user = User::factory()->create(['current_team_id' => $team->id]);
    $user->assignRole('TeamAdmin');

    // Create parent page
    $parentPage = Page::create([
        'title' => 'Research',
        'slug' => 'research',
        'content' => '<h1>Our Research</h1>',
        'is_published' => true,
        'team_id' => $team->id,
        'author_id' => $user->id,
        'sort_order' => 1,
    ]);

    // Create child page
    $childPage = Page::create([
        'title' => 'Current Projects',
        'slug' => 'current-projects',
        'content' => '<h1>Current Research Projects</h1>',
        'is_published' => true,
        'team_id' => $team->id,
        'author_id' => $user->id,
        'parent_id' => $parentPage->id,
        'sort_order' => 1,
    ]);

    expect($childPage->parent->id)->toBe($parentPage->id);
    expect($parentPage->children)->toHaveCount(1);
    expect($parentPage->children->first()->id)->toBe($childPage->id);

    // Test breadcrumbs
    $breadcrumbs = $childPage->breadcrumbs;
    expect($breadcrumbs)->toHaveCount(3); // Team home + parent + current
    expect($breadcrumbs[0]['title'])->toBe('Test Team');
    expect($breadcrumbs[1]['title'])->toBe('Research');
    expect($breadcrumbs[2]['title'])->toBe('Current Projects');
});

it('generates navigation tree correctly', function () {
    $team = Team::factory()->create(['name' => 'Test Team', 'slug' => 'test-team', 'manager_email' => 'manager@test.com']);
    $user = User::factory()->create(['current_team_id' => $team->id]);

    // Create root pages
    $page1 = Page::create([
        'title' => 'Home',
        'slug' => 'home',
        'content' => 'Home content',
        'is_published' => true,
        'team_id' => $team->id,
        'author_id' => $user->id,
        'sort_order' => 1,
    ]);

    $page2 = Page::create([
        'title' => 'About',
        'slug' => 'about',
        'content' => 'About content',
        'is_published' => true,
        'team_id' => $team->id,
        'author_id' => $user->id,
        'sort_order' => 2,
    ]);

    // Create child page
    $childPage = Page::create([
        'title' => 'Team Members',
        'slug' => 'team-members',
        'content' => 'Team members content',
        'is_published' => true,
        'team_id' => $team->id,
        'author_id' => $user->id,
        'parent_id' => $page2->id,
        'sort_order' => 1,
    ]);

    // Create unpublished page (should not appear in navigation)
    Page::create([
        'title' => 'Draft Page',
        'slug' => 'draft-page',
        'content' => 'Draft content',
        'is_published' => false,
        'team_id' => $team->id,
        'author_id' => $user->id,
        'sort_order' => 3,
    ]);

    $navigation = Page::getNavigationTree($team->id);

    expect($navigation)->toHaveCount(2); // Only published root pages
    expect($navigation[0]['title'])->toBe('Home');
    expect($navigation[1]['title'])->toBe('About');
    expect($navigation[1]['children'])->toHaveCount(1);
    expect($navigation[1]['children'][0]['title'])->toBe('Team Members');
});

it('respects publishing rules', function () {
    $team = Team::factory()->create(['name' => 'Test Team', 'slug' => 'test-team', 'manager_email' => 'manager@test.com']);
    $user = User::factory()->create(['current_team_id' => $team->id]);

    // Published page
    $publishedPage = Page::create([
        'title' => 'Published Page',
        'slug' => 'published-page',
        'content' => 'Published content',
        'is_published' => true,
        'team_id' => $team->id,
        'author_id' => $user->id,
    ]);

    // Draft page
    $draftPage = Page::create([
        'title' => 'Draft Page',
        'slug' => 'draft-page',
        'content' => 'Draft content',
        'is_published' => false,
        'team_id' => $team->id,
        'author_id' => $user->id,
    ]);

    // Future scheduled page
    $futureDate = now()->addDays(1);
    $scheduledPage = Page::create([
        'title' => 'Scheduled Page',
        'slug' => 'scheduled-page',
        'content' => 'Scheduled content',
        'is_published' => true,
        'published_at' => $futureDate,
        'team_id' => $team->id,
        'author_id' => $user->id,
    ]);

    expect($publishedPage->isPublished())->toBeTrue();
    expect($draftPage->isPublished())->toBeFalse();
    expect($scheduledPage->isPublished())->toBeFalse();

    // Test query scopes
    $publishedPages = Page::forTeam($team->id)->published()->get();
    expect($publishedPages)->toHaveCount(1);
    expect($publishedPages->first()->id)->toBe($publishedPage->id);
});

it('handles soft deletes correctly', function () {
    $team = Team::factory()->create(['name' => 'Test Team', 'slug' => 'test-team', 'manager_email' => 'manager@test.com']);
    $user = User::factory()->create(['current_team_id' => $team->id]);

    $page = Page::create([
        'title' => 'Test Delete',
        'slug' => 'test-delete',
        'content' => 'Test delete content',
        'team_id' => $team->id,
        'author_id' => $user->id,
    ]);

    // Test soft delete
    $page->delete();

    expect(Page::where('id', $page->id)->count())->toBe(0);
    expect(Page::withTrashed()->where('id', $page->id)->count())->toBe(1);

    // Test restore
    $page->restore();
    expect(Page::where('id', $page->id)->count())->toBe(1);
});

it('filters pages by team scope', function () {
    $team1 = Team::factory()->create(['name' => 'Team 1', 'slug' => 'team1', 'manager_email' => 'manager1@test.com']);
    $team2 = Team::factory()->create(['name' => 'Team 2', 'slug' => 'team2', 'manager_email' => 'manager2@test.com']);
    $user = User::factory()->create();

    Page::create([
        'title' => 'Team 1 Page',
        'slug' => 'team1-page',
        'content' => 'Team 1 content',
        'team_id' => $team1->id,
        'author_id' => $user->id,
    ]);

    Page::create([
        'title' => 'Team 2 Page',
        'slug' => 'team2-page',
        'content' => 'Team 2 content',
        'team_id' => $team2->id,
        'author_id' => $user->id,
    ]);

    $team1Pages = Page::forTeam($team1->id)->get();
    $team2Pages = Page::forTeam($team2->id)->get();

    expect($team1Pages)->toHaveCount(1);
    expect($team2Pages)->toHaveCount(1);
    expect($team1Pages->first()->title)->toBe('Team 1 Page');
    expect($team2Pages->first()->title)->toBe('Team 2 Page');
});
