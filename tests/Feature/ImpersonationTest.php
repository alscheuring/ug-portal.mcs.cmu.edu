<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create roles needed for testing
    Role::create(['name' => 'SuperAdmin']);
    Role::create(['name' => 'TeamAdmin']);
    Role::create(['name' => 'Student']);
});

it('allows super admin to impersonate users', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('SuperAdmin');

    // SuperAdmin has general impersonation permission
    expect($superAdmin->canImpersonate())->toBeTrue();
});

it('allows team admin to impersonate users', function () {
    $teamAdmin = User::factory()->create();
    $teamAdmin->assignRole('TeamAdmin');

    // TeamAdmin has general impersonation permission
    expect($teamAdmin->canImpersonate())->toBeTrue();
});

it('prevents students from impersonating others', function () {
    $student = User::factory()->create();
    $student->assignRole('Student');

    // Students cannot impersonate anyone
    expect($student->canImpersonate())->toBeFalse();
});

it('prevents impersonation of super admins', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('SuperAdmin');

    // SuperAdmins cannot be impersonated
    expect($superAdmin->canBeImpersonated())->toBeFalse();
});

it('allows impersonation of team admins and students', function () {
    $teamAdmin = User::factory()->create();
    $teamAdmin->assignRole('TeamAdmin');

    $student = User::factory()->create();
    $student->assignRole('Student');

    // TeamAdmins and Students can be impersonated
    expect($teamAdmin->canBeImpersonated())->toBeTrue();
    expect($student->canBeImpersonated())->toBeTrue();
});