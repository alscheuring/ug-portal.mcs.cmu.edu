<?php

use App\Models\LayupPage;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('menu item can reference layup page', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create();
    $menu = Menu::factory()->create(['team_id' => $team->id]);

    $layupPage = LayupPage::factory()->create([
        'team_id' => $team->id,
        'author_id' => $user->id,
        'title' => 'Test Page',
        'slug' => 'test-page',
    ]);

    $menuItem = MenuItem::create([
        'menu_id' => $menu->id,
        'title' => 'Test Menu Item',
        'link_type' => 'page',
        'page_id' => $layupPage->id,
        'sort_order' => 1,
        'is_visible' => true,
    ]);

    // Test the relationship
    expect($menuItem->page)->not->toBeNull();
    expect($menuItem->page->id)->toBe($layupPage->id);
    expect($menuItem->page->title)->toBe('Test Page');
});

test('menu item generates correct url for layup page', function () {
    $team = Team::factory()->create(['slug' => 'test-team']);
    $user = User::factory()->create();
    $menu = Menu::factory()->create(['team_id' => $team->id]);

    $layupPage = LayupPage::factory()->create([
        'team_id' => $team->id,
        'author_id' => $user->id,
        'slug' => 'test-page',
    ]);

    $menuItem = MenuItem::create([
        'menu_id' => $menu->id,
        'title' => 'Test Menu Item',
        'link_type' => 'page',
        'page_id' => $layupPage->id,
        'sort_order' => 1,
        'is_visible' => true,
    ]);

    $expectedUrl = route('public.pages.show', ['test-team', 'test-page']);
    expect($menuItem->getUrl())->toBe($expectedUrl);
});

test('menu item can be created and updated with layup page reference', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create();
    $menu = Menu::factory()->create(['team_id' => $team->id]);

    $layupPage1 = LayupPage::factory()->create([
        'team_id' => $team->id,
        'author_id' => $user->id,
    ]);

    $layupPage2 = LayupPage::factory()->create([
        'team_id' => $team->id,
        'author_id' => $user->id,
    ]);

    // Create menu item
    $menuItem = MenuItem::create([
        'menu_id' => $menu->id,
        'title' => 'Test Menu Item',
        'link_type' => 'page',
        'page_id' => $layupPage1->id,
        'sort_order' => 1,
        'is_visible' => true,
    ]);

    expect($menuItem->page_id)->toBe($layupPage1->id);

    // Update menu item to reference different layup page
    $menuItem->update(['page_id' => $layupPage2->id]);
    $menuItem->refresh();

    expect($menuItem->page_id)->toBe($layupPage2->id);
    expect($menuItem->page->id)->toBe($layupPage2->id);
});

test('menu item with null page_id works correctly', function () {
    $team = Team::factory()->create();
    $menu = Menu::factory()->create(['team_id' => $team->id]);

    $menuItem = MenuItem::create([
        'menu_id' => $menu->id,
        'title' => 'Parent Item',
        'link_type' => 'parent',
        'page_id' => null,
        'sort_order' => 1,
        'is_visible' => true,
    ]);

    expect($menuItem->page_id)->toBeNull();
    expect($menuItem->page)->toBeNull();
    expect($menuItem->getUrl())->toBeNull();
});
