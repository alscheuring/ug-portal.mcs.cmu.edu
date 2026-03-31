<?php

use App\Models\LayupPage;
use App\Models\Team;
use App\Models\User;

test('published_at is set when creating published page', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    $page = LayupPage::create([
        'title' => 'Test Page',
        'slug' => 'test-page',
        'content' => ['rows' => []],
        'status' => 'published',
        'team_id' => $team->id,
        'author_id' => $user->id,
    ]);

    expect($page->status)->toBe('published');
    expect($page->published_at)->not()->toBeNull();
    expect($page->isPublished())->toBeTrue();
});

test('published_at is null when creating draft page', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    $page = LayupPage::create([
        'title' => 'Draft Page',
        'slug' => 'draft-page',
        'content' => ['rows' => []],
        'status' => 'draft',
        'team_id' => $team->id,
        'author_id' => $user->id,
    ]);

    expect($page->status)->toBe('draft');
    expect($page->published_at)->toBeNull();
    expect($page->isPublished())->toBeFalse();
});

test('published_at is set when changing status to published', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    $page = LayupPage::create([
        'title' => 'Test Page',
        'slug' => 'test-page',
        'content' => ['rows' => []],
        'status' => 'draft',
        'team_id' => $team->id,
        'author_id' => $user->id,
    ]);

    expect($page->published_at)->toBeNull();

    // Change status to published
    $page->status = 'published';
    $page->save();

    expect($page->fresh()->status)->toBe('published');
    expect($page->fresh()->published_at)->not()->toBeNull();
    expect($page->fresh()->isPublished())->toBeTrue();
});

test('published_at is cleared when changing status from published', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    $page = LayupPage::create([
        'title' => 'Test Page',
        'slug' => 'test-page',
        'content' => ['rows' => []],
        'status' => 'published',
        'team_id' => $team->id,
        'author_id' => $user->id,
    ]);

    expect($page->published_at)->not()->toBeNull();

    // Change status to draft
    $page->status = 'draft';
    $page->save();

    expect($page->fresh()->status)->toBe('draft');
    expect($page->fresh()->published_at)->toBeNull();
    expect($page->fresh()->isPublished())->toBeFalse();
});

test('publish method sets both status and published_at', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    $page = LayupPage::create([
        'title' => 'Test Page',
        'slug' => 'test-page',
        'content' => ['rows' => []],
        'status' => 'draft',
        'team_id' => $team->id,
        'author_id' => $user->id,
    ]);

    expect($page->published_at)->toBeNull();

    $page->publish();

    expect($page->fresh()->status)->toBe('published');
    expect($page->fresh()->published_at)->not()->toBeNull();
    expect($page->fresh()->isPublished())->toBeTrue();
});

test('unpublish method clears both status and published_at', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    $page = LayupPage::create([
        'title' => 'Test Page',
        'slug' => 'test-page',
        'content' => ['rows' => []],
        'status' => 'published',
        'team_id' => $team->id,
        'author_id' => $user->id,
    ]);

    expect($page->published_at)->not()->toBeNull();

    $page->unpublish();

    expect($page->fresh()->status)->toBe('draft');
    expect($page->fresh()->published_at)->toBeNull();
    expect($page->fresh()->isPublished())->toBeFalse();
});

test('bulk update handles published_at correctly', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    $page = LayupPage::create([
        'title' => 'Test Page',
        'slug' => 'test-page',
        'content' => ['rows' => []],
        'status' => 'draft',
        'team_id' => $team->id,
        'author_id' => $user->id,
    ]);

    expect($page->published_at)->toBeNull();

    // Bulk update
    $page->update([
        'title' => 'Updated Title',
        'status' => 'published',
    ]);

    expect($page->fresh()->status)->toBe('published');
    expect($page->fresh()->title)->toBe('Updated Title');
    expect($page->fresh()->published_at)->not()->toBeNull();
    expect($page->fresh()->isPublished())->toBeTrue();
});
