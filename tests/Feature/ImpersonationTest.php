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

    // Login as any user to provide auth context
    $this->actingAs($superAdmin);

    // SuperAdmins cannot be impersonated by anyone
    expect($superAdmin->canBeImpersonated())->toBeFalse();
});

it('allows super admin to impersonate team admins', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('SuperAdmin');

    $teamAdmin = User::factory()->create();
    $teamAdmin->assignRole('TeamAdmin');

    // Login as SuperAdmin
    $this->actingAs($superAdmin);

    // SuperAdmin can impersonate TeamAdmin
    expect($teamAdmin->canBeImpersonated())->toBeTrue();
});

it('allows super admin to impersonate students', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('SuperAdmin');

    $student = User::factory()->create();
    $student->assignRole('Student');

    // Login as SuperAdmin
    $this->actingAs($superAdmin);

    // SuperAdmin can impersonate Student
    expect($student->canBeImpersonated())->toBeTrue();
});

it('prevents team admin from impersonating other team admins', function () {
    $teamAdmin1 = User::factory()->create();
    $teamAdmin1->assignRole('TeamAdmin');

    $teamAdmin2 = User::factory()->create();
    $teamAdmin2->assignRole('TeamAdmin');

    // Login as TeamAdmin
    $this->actingAs($teamAdmin1);

    // TeamAdmin cannot impersonate another TeamAdmin
    expect($teamAdmin2->canBeImpersonated())->toBeFalse();
});

it('allows team admin to impersonate students', function () {
    $teamAdmin = User::factory()->create();
    $teamAdmin->assignRole('TeamAdmin');

    $student = User::factory()->create();
    $student->assignRole('Student');

    // Login as TeamAdmin
    $this->actingAs($teamAdmin);

    // TeamAdmin can impersonate Student
    expect($student->canBeImpersonated())->toBeTrue();
});

it('prevents students from impersonating anyone even when logged in', function () {
    $student1 = User::factory()->create();
    $student1->assignRole('Student');

    $student2 = User::factory()->create();
    $student2->assignRole('Student');

    // Login as Student
    $this->actingAs($student1);

    // Student cannot impersonate another Student
    expect($student2->canBeImpersonated())->toBeFalse();
});
