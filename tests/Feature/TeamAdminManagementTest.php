<?php

use App\Filament\Resources\Teams\Pages\EditTeam;
use App\Models\Team;
use App\Models\User;
use Filament\Facades\Filament;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create roles needed for testing
    Role::create(['name' => 'SuperAdmin']);
    Role::create(['name' => 'TeamAdmin']);
    Role::create(['name' => 'Student']);
});

it('allows team admin to add other users as team admins to their own team', function () {
    // Create a team
    $team = Team::factory()->create(['name' => 'Biological Sciences']);

    // Create a team admin user
    $teamAdmin = User::factory()->create([
        'name' => 'Kristof Kovacs',
        'email' => 'kkovacs@andrew.cmu.edu',
        'current_team_id' => $team->id,
    ]);
    $teamAdmin->assignRole('TeamAdmin');

    // Create a regular user to be promoted to team admin
    $newUser = User::factory()->create([
        'name' => 'Jane Doe',
        'email' => 'janedoe@andrew.cmu.edu',
    ]);
    $newUser->assignRole('Student');

    // Acting as the team admin
    $this->actingAs($teamAdmin);

    // Simulate the EditTeam page save operation
    $editTeamPage = new EditTeam;
    $editTeamPage->record = $team;

    // Use reflection to set the protected property
    $reflection = new ReflectionClass($editTeamPage);
    $property = $reflection->getProperty('teamAdminAssignments');
    $property->setAccessible(true);
    $property->setValue($editTeamPage, [
        ['user_id' => $teamAdmin->id], // Existing team admin
        ['user_id' => $newUser->id], // New team admin
    ]);

    // Call the afterSave method using reflection
    $method = $reflection->getMethod('afterSave');
    $method->setAccessible(true);
    $method->invoke($editTeamPage);

    // Verify the new user was assigned TeamAdmin role and correct team
    $newUser->refresh();
    expect($newUser->hasRole('TeamAdmin'))->toBeTrue();
    expect($newUser->current_team_id)->toBe($team->id);
});

it('allows team admin to remove team admins from their own team', function () {
    // Create a team
    $team = Team::factory()->create(['name' => 'Biological Sciences']);

    // Create team admin users
    $teamAdmin1 = User::factory()->create([
        'name' => 'Kristof Kovacs',
        'email' => 'kkovacs@andrew.cmu.edu',
        'current_team_id' => $team->id,
    ]);
    $teamAdmin1->assignRole('TeamAdmin');

    $teamAdmin2 = User::factory()->create([
        'name' => 'John Smith',
        'email' => 'jsmith@andrew.cmu.edu',
        'current_team_id' => $team->id,
    ]);
    $teamAdmin2->assignRole('TeamAdmin');

    // Acting as the first team admin
    $this->actingAs($teamAdmin1);

    // Simulate the EditTeam page save operation
    $editTeamPage = new EditTeam;
    $editTeamPage->record = $team;

    // Use reflection to set the protected property
    $reflection = new ReflectionClass($editTeamPage);
    $property = $reflection->getProperty('teamAdminAssignments');
    $property->setAccessible(true);
    $property->setValue($editTeamPage, [
        ['user_id' => $teamAdmin1->id], // Keep first admin
        // Remove second admin by not including them
    ]);

    // Call the afterSave method using reflection
    $method = $reflection->getMethod('afterSave');
    $method->setAccessible(true);
    $method->invoke($editTeamPage);

    // Verify the second user's TeamAdmin role was removed
    $teamAdmin2->refresh();
    expect($teamAdmin2->hasRole('TeamAdmin'))->toBeFalse();
    expect($teamAdmin2->current_team_id)->toBeNull();
});

it('prevents team admin from managing admins of different teams', function () {
    // Create two teams
    $team1 = Team::factory()->create(['name' => 'Biological Sciences']);
    $team2 = Team::factory()->create(['name' => 'Computer Science']);

    // Create team admin for team1
    $teamAdmin1 = User::factory()->create([
        'name' => 'Kristof Kovacs',
        'email' => 'kkovacs@andrew.cmu.edu',
        'current_team_id' => $team1->id,
    ]);
    $teamAdmin1->assignRole('TeamAdmin');

    // Create a user to be added to team2
    $newUser = User::factory()->create([
        'name' => 'Jane Doe',
        'email' => 'janedoe@andrew.cmu.edu',
    ]);
    $newUser->assignRole('Student');

    // Acting as team1 admin, try to add user to team2
    $this->actingAs($teamAdmin1);

    // Simulate trying to edit team2 - the afterSave method should prevent this
    $editTeamPage = new EditTeam;
    $editTeamPage->record = $team2; // Different team!

    // Use reflection to set the protected property
    $reflection = new ReflectionClass($editTeamPage);
    $property = $reflection->getProperty('teamAdminAssignments');
    $property->setAccessible(true);
    $property->setValue($editTeamPage, [
        ['user_id' => $newUser->id],
    ]);

    // Call the afterSave method using reflection
    $method = $reflection->getMethod('afterSave');
    $method->setAccessible(true);
    $method->invoke($editTeamPage);

    // Verify the user was NOT assigned to team2
    $newUser->refresh();
    expect($newUser->current_team_id)->not->toBe($team2->id);
    expect($newUser->hasRole('TeamAdmin'))->toBeFalse();
});

it('prevents super admins from being assigned as team admins', function () {
    // Create a team
    $team = Team::factory()->create(['name' => 'Biological Sciences']);

    // Create a team admin user
    $teamAdmin = User::factory()->create([
        'name' => 'Kristof Kovacs',
        'email' => 'kkovacs@andrew.cmu.edu',
        'current_team_id' => $team->id,
    ]);
    $teamAdmin->assignRole('TeamAdmin');

    // Create a super admin user
    $superAdmin = User::factory()->create([
        'name' => 'Super Admin',
        'email' => 'admin@andrew.cmu.edu',
    ]);
    $superAdmin->assignRole('SuperAdmin');

    // Acting as the team admin
    $this->actingAs($teamAdmin);

    // Simulate the EditTeam page save operation
    $editTeamPage = new EditTeam;
    $editTeamPage->record = $team;

    // Use reflection to set the protected property
    $reflection = new ReflectionClass($editTeamPage);
    $property = $reflection->getProperty('teamAdminAssignments');
    $property->setAccessible(true);
    $property->setValue($editTeamPage, [
        ['user_id' => $teamAdmin->id], // Existing team admin
        ['user_id' => $superAdmin->id], // Super admin (should be ignored)
    ]);

    // Call the afterSave method using reflection
    $method = $reflection->getMethod('afterSave');
    $method->setAccessible(true);
    $method->invoke($editTeamPage);

    // Verify the super admin was NOT assigned to the team
    $superAdmin->refresh();
    expect($superAdmin->current_team_id)->not->toBe($team->id);
    expect($superAdmin->hasRole('SuperAdmin'))->toBeTrue();
    expect($superAdmin->hasRole('TeamAdmin'))->toBeFalse();
});

it('loads existing team admins correctly in the form', function () {
    // Create a team
    $team = Team::factory()->create(['name' => 'Biological Sciences']);

    // Create team admin users
    $teamAdmin1 = User::factory()->create([
        'name' => 'Kristof Kovacs',
        'email' => 'kkovacs@andrew.cmu.edu',
        'current_team_id' => $team->id,
    ]);
    $teamAdmin1->assignRole('TeamAdmin');

    $teamAdmin2 = User::factory()->create([
        'name' => 'John Smith',
        'email' => 'jsmith@andrew.cmu.edu',
        'current_team_id' => $team->id,
    ]);
    $teamAdmin2->assignRole('TeamAdmin');

    // Acting as the first team admin
    $this->actingAs($teamAdmin1);

    // Test the mutateFormDataBeforeFill method using reflection
    $editTeamPage = new EditTeam;
    $editTeamPage->record = $team;

    $reflection = new ReflectionClass($editTeamPage);
    $method = $reflection->getMethod('mutateFormDataBeforeFill');
    $method->setAccessible(true);
    $formData = $method->invoke($editTeamPage, []);

    expect($formData['team_admins'])->toHaveCount(2);
    $userIds = collect($formData['team_admins'])->pluck('user_id')->toArray();
    expect($userIds)->toContain($teamAdmin1->id, $teamAdmin2->id);
});

it('validates team admin can access team administrators section', function () {
    // Create a team
    $team = Team::factory()->create(['name' => 'Biological Sciences']);

    // Create a team admin user
    $teamAdmin = User::factory()->create([
        'name' => 'Kristof Kovacs',
        'email' => 'kkovacs@andrew.cmu.edu',
        'current_team_id' => $team->id,
    ]);
    $teamAdmin->assignRole('TeamAdmin');

    // Acting as the team admin
    $this->actingAs($teamAdmin);

    // Test that the team admin can access the admin panel
    expect($teamAdmin->isTeamAdmin())->toBeTrue();
    expect($teamAdmin->canAccessPanel(Filament::getPanel('admin')))->toBeTrue();
});

it('validates students cannot access team administrators section', function () {
    // Create a team
    $team = Team::factory()->create(['name' => 'Biological Sciences']);

    // Create a student user
    $student = User::factory()->create([
        'name' => 'Student User',
        'email' => 'student@andrew.cmu.edu',
        'current_team_id' => $team->id,
    ]);
    $student->assignRole('Student');

    // Acting as the student
    $this->actingAs($student);

    // Test that the student cannot access the admin panel
    expect($student->isTeamAdmin())->toBeFalse();
    expect($student->canAccessPanel(Filament::getPanel('admin')))->toBeFalse();
});
