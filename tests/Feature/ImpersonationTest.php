<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create roles needed for testing
    Role::create(['name' => 'SuperAdmin']);
    Role::create(['name' => 'TeamAdmin']);
    Role::create(['name' => 'Student']);
});

it('allows super admin to impersonate any user', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('SuperAdmin');

    $student = User::factory()->create();
    $student->assignRole('Student');

    $teamAdmin = User::factory()->create();
    $teamAdmin->assignRole('TeamAdmin');

    // SuperAdmin can impersonate anyone
    expect($superAdmin->canImpersonate($student))->toBeTrue();
    expect($superAdmin->canImpersonate($teamAdmin))->toBeTrue();

    // Test impersonation route access
    $this->actingAs($superAdmin)
        ->get(route('impersonate.take', $student->id))
        ->assertRedirect('/');
});

it('allows team admin to impersonate students only', function () {
    $teamAdmin = User::factory()->create();
    $teamAdmin->assignRole('TeamAdmin');

    $student = User::factory()->create();
    $student->assignRole('Student');

    $anotherTeamAdmin = User::factory()->create();
    $anotherTeamAdmin->assignRole('TeamAdmin');

    // TeamAdmin can impersonate students
    expect($teamAdmin->canImpersonate($student))->toBeTrue();

    // TeamAdmin cannot impersonate other TeamAdmins
    expect($teamAdmin->canImpersonate($anotherTeamAdmin))->toBeFalse();
});

it('prevents students from impersonating anyone', function () {
    $student = User::factory()->create();
    $student->assignRole('Student');

    $anotherStudent = User::factory()->create();
    $anotherStudent->assignRole('Student');

    // Students cannot impersonate anyone
    expect($student->canImpersonate($anotherStudent))->toBeFalse();
});

it('prevents super admins from being impersonated', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('SuperAdmin');

    // SuperAdmins cannot be impersonated
    expect($superAdmin->canBeImpersonated())->toBeFalse();
});

it('allows team admins and students to be impersonated', function () {
    $teamAdmin = User::factory()->create();
    $teamAdmin->assignRole('TeamAdmin');

    $student = User::factory()->create();
    $student->assignRole('Student');

    // TeamAdmins and Students can be impersonated
    expect($teamAdmin->canBeImpersonated())->toBeTrue();
    expect($student->canBeImpersonated())->toBeTrue();
});

it('prevents unauthorized impersonation attempts', function () {
    $student = User::factory()->create();
    $student->assignRole('Student');

    $targetUser = User::factory()->create();
    $targetUser->assignRole('Student');

    // Student trying to impersonate another student should fail
    $this->actingAs($student)
        ->get(route('impersonate.take', $targetUser->id))
        ->assertStatus(403);
});

it('allows leaving impersonation', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('SuperAdmin');

    // Test leaving impersonation when not impersonating
    $this->actingAs($superAdmin)
        ->get(route('impersonate.leave'))
        ->assertRedirect('/');
});
