<?php

use App\Models\Announcement;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Models\Team;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create roles
    Role::firstOrCreate(['name' => 'SuperAdmin']);
    Role::firstOrCreate(['name' => 'TeamAdmin']);
    Role::firstOrCreate(['name' => 'Student']);
});

it('creates announcements with proper team scoping', function () {
    $team = Team::factory()->create(['name' => 'Test Team', 'slug' => 'test-team', 'manager_email' => 'manager@test.com']);
    $user = User::factory()->create(['current_team_id' => $team->id]);
    $user->assignRole('TeamAdmin');

    $announcement = Announcement::create([
        'title' => 'Test Announcement',
        'slug' => 'test-announcement',
        'content' => 'This is a test announcement content.',
        'is_published' => true,
        'published_at' => now(),
        'team_id' => $team->id,
        'author_id' => $user->id,
    ]);

    expect($announcement->title)->toBe('Test Announcement');
    expect($announcement->team_id)->toBe($team->id);
    expect($announcement->author_id)->toBe($user->id);
    expect($announcement->isPublished())->toBeTrue();
    expect($announcement->team->name)->toBe('Test Team');
    expect($announcement->author->id)->toBe($user->id);
});

it('creates polls with options correctly', function () {
    $team = Team::factory()->create(['name' => 'Test Team', 'slug' => 'test-team', 'manager_email' => 'manager@test.com']);
    $user = User::factory()->create(['current_team_id' => $team->id]);
    $user->assignRole('TeamAdmin');

    $poll = Poll::create([
        'title' => 'Test Poll',
        'description' => 'This is a test poll',
        'is_active' => true,
        'allows_multiple_votes' => false,
        'team_id' => $team->id,
        'created_by' => $user->id,
    ]);

    // Create poll options
    $option1 = PollOption::create([
        'title' => 'Option 1',
        'poll_id' => $poll->id,
        'sort_order' => 1,
    ]);

    $option2 = PollOption::create([
        'title' => 'Option 2',
        'poll_id' => $poll->id,
        'sort_order' => 2,
    ]);

    expect($poll->title)->toBe('Test Poll');
    expect($poll->team_id)->toBe($team->id);
    expect($poll->created_by)->toBe($user->id);
    expect($poll->isRunning())->toBeTrue();
    expect($poll->options)->toHaveCount(2);
    expect($poll->options->first()->title)->toBe('Option 1');
});

it('handles poll voting correctly', function () {
    $team = Team::factory()->create(['name' => 'Test Team', 'slug' => 'test-team', 'manager_email' => 'manager@test.com']);
    $creator = User::factory()->create(['current_team_id' => $team->id]);
    $voter = User::factory()->create(['current_team_id' => $team->id]);

    $creator->assignRole('TeamAdmin');
    $voter->assignRole('Student');

    $poll = Poll::create([
        'title' => 'Voting Test Poll',
        'is_active' => true,
        'allows_multiple_votes' => false,
        'team_id' => $team->id,
        'created_by' => $creator->id,
    ]);

    $option1 = PollOption::create([
        'title' => 'Option A',
        'poll_id' => $poll->id,
        'sort_order' => 1,
    ]);

    $option2 = PollOption::create([
        'title' => 'Option B',
        'poll_id' => $poll->id,
        'sort_order' => 2,
    ]);

    // User should be able to vote initially
    expect($poll->canUserVote($voter))->toBeTrue();

    // Cast a vote
    PollVote::create([
        'poll_id' => $poll->id,
        'poll_option_id' => $option1->id,
        'user_id' => $voter->id,
    ]);

    // Refresh poll to get updated relationships
    $poll->refresh();

    // User should not be able to vote again (single vote poll)
    expect($poll->canUserVote($voter))->toBeFalse();
    expect($poll->total_votes)->toBe(1);
    expect($option1->vote_count)->toBe(1);
    expect($option2->vote_count)->toBe(0);
});

it('handles multiple vote polls correctly', function () {
    $team = Team::factory()->create(['name' => 'Test Team', 'slug' => 'test-team', 'manager_email' => 'manager@test.com']);
    $creator = User::factory()->create(['current_team_id' => $team->id]);
    $voter = User::factory()->create(['current_team_id' => $team->id]);

    $creator->assignRole('TeamAdmin');
    $voter->assignRole('Student');

    $poll = Poll::create([
        'title' => 'Multiple Vote Poll',
        'is_active' => true,
        'allows_multiple_votes' => true,
        'team_id' => $team->id,
        'created_by' => $creator->id,
    ]);

    $option1 = PollOption::create([
        'title' => 'Option A',
        'poll_id' => $poll->id,
        'sort_order' => 1,
    ]);

    $option2 = PollOption::create([
        'title' => 'Option B',
        'poll_id' => $poll->id,
        'sort_order' => 2,
    ]);

    // Cast first vote
    PollVote::create([
        'poll_id' => $poll->id,
        'poll_option_id' => $option1->id,
        'user_id' => $voter->id,
    ]);

    // User should still be able to vote again (multiple vote poll)
    expect($poll->canUserVote($voter))->toBeTrue();

    // Cast second vote
    PollVote::create([
        'poll_id' => $poll->id,
        'poll_option_id' => $option2->id,
        'user_id' => $voter->id,
    ]);

    $poll->refresh();

    expect($poll->total_votes)->toBe(2);
});

it('respects poll time constraints', function () {
    $team = Team::factory()->create(['name' => 'Test Team', 'slug' => 'test-team', 'manager_email' => 'manager@test.com']);
    $user = User::factory()->create(['current_team_id' => $team->id]);
    $user->assignRole('TeamAdmin');

    // Future poll
    $futurePoll = Poll::create([
        'title' => 'Future Poll',
        'is_active' => true,
        'starts_at' => now()->addDays(1),
        'team_id' => $team->id,
        'created_by' => $user->id,
    ]);

    expect($futurePoll->isRunning())->toBeFalse();

    // Past poll
    $pastPoll = Poll::create([
        'title' => 'Past Poll',
        'is_active' => true,
        'ends_at' => now()->subDays(1),
        'team_id' => $team->id,
        'created_by' => $user->id,
    ]);

    expect($pastPoll->isRunning())->toBeFalse();

    // Current poll
    $currentPoll = Poll::create([
        'title' => 'Current Poll',
        'is_active' => true,
        'starts_at' => now()->subHours(1),
        'ends_at' => now()->addHours(1),
        'team_id' => $team->id,
        'created_by' => $user->id,
    ]);

    expect($currentPoll->isRunning())->toBeTrue();
});

it('generates proper URLs for team content', function () {
    $team = Team::factory()->create(['name' => 'Test Team', 'slug' => 'test-team', 'manager_email' => 'manager@test.com']);
    $user = User::factory()->create(['current_team_id' => $team->id]);
    $user->assignRole('TeamAdmin');

    $announcement = Announcement::create([
        'title' => 'Test Announcement',
        'slug' => 'test-announcement',
        'content' => 'Content',
        'team_id' => $team->id,
        'author_id' => $user->id,
    ]);

    $poll = Poll::create([
        'title' => 'Test Poll',
        'team_id' => $team->id,
        'created_by' => $user->id,
    ]);

    expect($announcement->url)->toBe('/test-team/announcements/test-announcement');
    expect($poll->url)->toBe("/test-team/polls/{$poll->id}");
});
