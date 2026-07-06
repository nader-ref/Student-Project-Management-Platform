<?php

use App\Models\User;
use Laratrust\Models\Role;

beforeEach(function () {
    foreach (['admin', 'supervisor', 'student'] as $roleName) {
        Role::firstOrCreate(
            ['name' => $roleName],
            ['display_name' => ucfirst($roleName), 'description' => $roleName.' role'],
        );
    }
});

function createEmailStatusAdmin(): User
{
    $admin = User::factory()->create([
        'name' => 'Email Status Admin',
        'university_number' => 'ADM-EMAIL-STATUS',
        'email' => 'admin.email.status@test.local',
    ]);
    $admin->addRole('admin');

    return $admin;
}

it('shows email and email status columns on the admin users page', function () {
    $admin = createEmailStatusAdmin();

    $response = $this->actingAs($admin)->get(route('admin.users'));

    $response->assertOk();
    $response->assertSee('Email', false);
    $response->assertSee('Email status', false);
});

it('shows email address and complete status for users with email', function () {
    $admin = createEmailStatusAdmin();

    $student = User::factory()->create([
        'name' => 'Complete Email Student',
        'university_number' => 'STU-EMAIL-COMPLETE',
        'email' => 'complete.student@test.local',
    ]);
    $student->addRole('student');

    $response = $this->actingAs($admin)->get(route('admin.users'));

    $response->assertOk();
    $response->assertSee('complete.student@test.local');
    $response->assertSee('Complete');
});

it('shows not set and pending status for users without email', function () {
    $admin = createEmailStatusAdmin();

    $student = User::factory()->create([
        'name' => 'Pending Email Student',
        'university_number' => 'STU-EMAIL-PENDING',
        'email' => null,
    ]);
    $student->addRole('student');

    $response = $this->actingAs($admin)->get(route('admin.users'));

    $response->assertOk();
    $response->assertSee('Pending Email Student');
    $response->assertSee('Not set');
    $response->assertSee('Pending');
});

it('shows pending email users count on the admin dashboard', function () {
    $admin = createEmailStatusAdmin();

    User::factory()->create([
        'university_number' => 'STU-PENDING-COUNT-1',
        'email' => null,
    ])->addRole('student');

    User::factory()->create([
        'university_number' => 'STU-PENDING-COUNT-2',
        'email' => null,
    ])->addRole('student');

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertOk();
    $response->assertSee('Pending email');
    $response->assertSee('2', false);
});

it('shows email status in the dashboard latest users table', function () {
    $admin = createEmailStatusAdmin();

    User::factory()->create([
        'name' => 'Latest Pending Student',
        'university_number' => 'STU-LATEST-PENDING',
        'email' => null,
    ])->addRole('student');

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertOk();
    $response->assertSee('Latest Pending Student');
    $response->assertSee('Email status', false);
    $response->assertSee('Pending');
});

it('keeps account status separate from email status on the users page', function () {
    $admin = createEmailStatusAdmin();

    $student = User::factory()->create([
        'name' => 'Separate Status Student',
        'university_number' => 'STU-SEPARATE-STATUS',
        'email' => 'separate.status@test.local',
    ]);
    $student->addRole('student');

    $response = $this->actingAs($admin)->get(route('admin.users'));

    $response->assertOk();
    $response->assertSee('Account status', false);
    $response->assertSee('Email status', false);
    $response->assertSee('Active');
    $response->assertSee('Complete');
});
