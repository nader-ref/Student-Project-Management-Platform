<?php

use App\Models\Supervisor;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laratrust\Models\Role;

beforeEach(function () {
    foreach (['admin', 'supervisor', 'student'] as $roleName) {
        Role::firstOrCreate(
            ['name' => $roleName],
            ['display_name' => ucfirst($roleName), 'description' => $roleName.' role'],
        );
    }
});

function createPasswordResetAdmin(array $overrides = []): User
{
    $admin = User::factory()->create(array_merge([
        'name' => 'Password Reset Admin',
        'university_number' => 'ADM-PWD-RESET-001',
        'email' => 'admin.password.reset@test.local',
    ], $overrides));
    $admin->addRole('admin');

    return $admin;
}

function createPasswordResetStudent(array $overrides = []): User
{
    $student = User::factory()->create(array_merge([
        'name' => 'Password Reset Student',
        'university_number' => 'STU-PWD-RESET-001',
        'email' => 'student.password.reset@test.local',
    ], $overrides));
    $student->addRole('student');

    return $student;
}

function createPasswordResetSupervisor(array $overrides = []): User
{
    $supervisorUser = User::factory()->create(array_merge([
        'name' => 'Dr. Password Reset Supervisor',
        'university_number' => 'SUP-PWD-RESET-001',
        'email' => 'supervisor.password.reset@test.local',
    ], $overrides));
    $supervisorUser->addRole('supervisor');

    Supervisor::create([
        'name' => $supervisorUser->name,
        'email' => $supervisorUser->email,
        'user_id' => $supervisorUser->id,
    ]);

    return $supervisorUser;
}

it('allows an admin to open the reset password form for a student', function () {
    $admin = createPasswordResetAdmin();
    $student = createPasswordResetStudent();

    $response = $this->actingAs($admin)->get(route('admin.users.reset-password', $student));

    $response->assertOk();
    $response->assertSee('Reset Password');
    $response->assertSee($student->name);
    $response->assertSee($student->university_number);
    $response->assertSee('Set a temporary password and share it securely with the user.');
});

it('allows an admin to reset a student password', function () {
    $admin = createPasswordResetAdmin();
    $student = createPasswordResetStudent([
        'university_number' => 'STU-PWD-RESET-NEW',
    ]);

    $this->actingAs($admin)
        ->post(route('admin.users.reset-password.store', $student), [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])
        ->assertRedirect(route('admin.users'))
        ->assertSessionHas('success', 'Password reset successfully. Share the temporary password securely with the user.');

    expect(Hash::check('newpassword123', $student->fresh()->password))->toBeTrue();
});

it('allows a student to log in with the new password after admin reset', function () {
    $admin = createPasswordResetAdmin();
    $student = createPasswordResetStudent([
        'university_number' => 'STU-PWD-LOGIN-NEW',
    ]);

    $this->actingAs($admin)
        ->post(route('admin.users.reset-password.store', $student), [
            'password' => 'freshpassword123',
            'password_confirmation' => 'freshpassword123',
        ]);

    Auth::logout();

    $response = $this->post('/Login', [
        'university_number' => $student->university_number,
        'password' => 'freshpassword123',
    ]);

    $response->assertRedirect('/StudentDashboard');
    $this->assertAuthenticatedAs($student);
});

it('rejects the old password after admin reset', function () {
    $admin = createPasswordResetAdmin();
    $student = createPasswordResetStudent([
        'university_number' => 'STU-PWD-OLD-FAIL',
        'password' => 'oldpassword123',
    ]);

    $this->actingAs($admin)
        ->post(route('admin.users.reset-password.store', $student), [
            'password' => 'replacement123',
            'password_confirmation' => 'replacement123',
        ]);

    Auth::logout();

    $response = $this->post('/Login', [
        'university_number' => $student->university_number,
        'password' => 'oldpassword123',
    ]);

    $response->assertSessionHasErrors('university_number');
    $this->assertGuest();
});

it('allows an admin to reset a supervisor password and the supervisor can log in', function () {
    $admin = createPasswordResetAdmin();
    $supervisor = createPasswordResetSupervisor([
        'university_number' => 'SUP-PWD-LOGIN-NEW',
    ]);

    $this->actingAs($admin)
        ->post(route('admin.users.reset-password.store', $supervisor), [
            'password' => 'superpass12345',
            'password_confirmation' => 'superpass12345',
        ]);

    Auth::logout();

    $response = $this->post('/Login', [
        'university_number' => $supervisor->university_number,
        'password' => 'superpass12345',
    ]);

    $response->assertRedirect('/supervisorDashboard');
    $this->assertAuthenticatedAs($supervisor);
});

it('allows admin to reset a no-email user password and login redirects to complete-email', function () {
    $admin = createPasswordResetAdmin();
    $supervisor = createPasswordResetSupervisor([
        'email' => null,
        'university_number' => 'SUP-PWD-NO-EMAIL',
    ]);

    $this->actingAs($admin)
        ->post(route('admin.users.reset-password.store', $supervisor), [
            'password' => 'noemailpass123',
            'password_confirmation' => 'noemailpass123',
        ]);

    Auth::logout();

    $response = $this->post('/Login', [
        'university_number' => $supervisor->university_number,
        'password' => 'noemailpass123',
    ]);

    $response->assertRedirect(route('profile.complete-email'));
    $this->assertAuthenticatedAs($supervisor);
});

it('allows admin to reset an inactive user password but login remains blocked until activation', function () {
    $admin = createPasswordResetAdmin();
    $student = createPasswordResetStudent([
        'university_number' => 'STU-PWD-INACTIVE',
        'is_active' => false,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.users.reset-password.store', $student), [
            'password' => 'inactivepass123',
            'password_confirmation' => 'inactivepass123',
        ])
        ->assertRedirect(route('admin.users'));

    Auth::logout();

    $response = $this->post('/Login', [
        'university_number' => $student->university_number,
        'password' => 'inactivepass123',
    ]);

    $response->assertSessionHasErrors('university_number');
    $this->assertGuest();
});

it('prevents an admin from opening the reset password form for himself', function () {
    $admin = createPasswordResetAdmin();

    $this->actingAs($admin)
        ->get(route('admin.users.reset-password', $admin))
        ->assertRedirect(route('admin.users'))
        ->assertSessionHasErrors('user');
});

it('prevents an admin from posting a password reset for himself', function () {
    $admin = createPasswordResetAdmin();

    $this->actingAs($admin)
        ->post(route('admin.users.reset-password.store', $admin), [
            'password' => 'selfreset12345',
            'password_confirmation' => 'selfreset12345',
        ])
        ->assertRedirect(route('admin.users'))
        ->assertSessionHasErrors('user');

    expect(Hash::check('selfreset12345', $admin->fresh()->password))->toBeFalse();
});

it('rejects password confirmation mismatches', function () {
    $admin = createPasswordResetAdmin();
    $student = createPasswordResetStudent();

    $this->actingAs($admin)
        ->post(route('admin.users.reset-password.store', $student), [
            'password' => 'validpassword1',
            'password_confirmation' => 'differentpassword1',
        ])
        ->assertSessionHasErrors('password');

    expect(Hash::check('validpassword1', $student->fresh()->password))->toBeFalse();
});

it('rejects passwords shorter than 8 characters', function () {
    $admin = createPasswordResetAdmin();
    $student = createPasswordResetStudent();

    $this->actingAs($admin)
        ->post(route('admin.users.reset-password.store', $student), [
            'password' => 'short1',
            'password_confirmation' => 'short1',
        ])
        ->assertSessionHasErrors('password');
});

it('redirects guests away from the reset password form', function () {
    $student = createPasswordResetStudent();

    $this->get(route('admin.users.reset-password', $student))
        ->assertRedirect(route('login'));
});

it('redirects students away from the reset password form', function () {
    $admin = createPasswordResetAdmin();
    $student = createPasswordResetStudent();
    $otherStudent = createPasswordResetStudent([
        'university_number' => 'STU-PWD-OTHER',
        'email' => 'other.student@test.local',
    ]);

    $this->actingAs($student)
        ->get(route('admin.users.reset-password', $otherStudent))
        ->assertRedirect('/StudentDashboard');
});

it('redirects supervisors away from the reset password form', function () {
    $supervisor = createPasswordResetSupervisor();
    $student = createPasswordResetStudent();

    $this->actingAs($supervisor)
        ->get(route('admin.users.reset-password', $student))
        ->assertRedirect('/supervisorDashboard');
});

it('keeps the admin authenticated after resetting another user password', function () {
    $admin = createPasswordResetAdmin();
    $student = createPasswordResetStudent();

    $this->actingAs($admin)
        ->post(route('admin.users.reset-password.store', $student), [
            'password' => 'anotherpass123',
            'password_confirmation' => 'anotherpass123',
        ])
        ->assertRedirect(route('admin.users'));

    $this->assertAuthenticatedAs($admin);
});
