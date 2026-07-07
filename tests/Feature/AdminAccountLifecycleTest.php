<?php

use App\Models\Supervisor;
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

function createLifecycleAdmin(array $overrides = []): User
{
    $admin = User::factory()->create(array_merge([
        'name' => 'Lifecycle Admin',
        'university_number' => 'ADM-LIFECYCLE-001',
        'email' => 'admin.lifecycle@test.local',
    ], $overrides));
    $admin->addRole('admin');

    return $admin;
}

function createLifecycleStudent(array $overrides = []): User
{
    $student = User::factory()->create(array_merge([
        'name' => 'Lifecycle Student',
        'university_number' => 'STU-LIFECYCLE-001',
        'email' => 'student.lifecycle@test.local',
    ], $overrides));
    $student->addRole('student');

    return $student;
}

function createLifecycleSupervisor(array $overrides = []): User
{
    $supervisorUser = User::factory()->create(array_merge([
        'name' => 'Dr. Lifecycle Supervisor',
        'university_number' => 'SUP-LIFECYCLE-001',
        'email' => 'supervisor.lifecycle@test.local',
    ], $overrides));
    $supervisorUser->addRole('supervisor');

    Supervisor::create([
        'name' => $supervisorUser->name,
        'email' => $supervisorUser->email,
        'user_id' => $supervisorUser->id,
    ]);

    return $supervisorUser;
}

it('allows an admin to deactivate a student', function () {
    $admin = createLifecycleAdmin();
    $student = createLifecycleStudent();

    $this->actingAs($admin)
        ->post(route('admin.users.deactivate', $student))
        ->assertRedirect(route('admin.users'))
        ->assertSessionHas('success', 'User account deactivated successfully.');

    expect($student->fresh()->is_active)->toBeFalse();
});

it('allows an admin to deactivate a supervisor', function () {
    $admin = createLifecycleAdmin();
    $supervisor = createLifecycleSupervisor();

    $this->actingAs($admin)
        ->post(route('admin.users.deactivate', $supervisor))
        ->assertRedirect(route('admin.users'))
        ->assertSessionHas('success', 'User account deactivated successfully.');

    expect($supervisor->fresh()->is_active)->toBeFalse();
});

it('allows an admin to reactivate an inactive user', function () {
    $admin = createLifecycleAdmin();
    $student = createLifecycleStudent(['is_active' => false]);

    $this->actingAs($admin)
        ->post(route('admin.users.activate', $student))
        ->assertRedirect(route('admin.users'))
        ->assertSessionHas('success', 'User account activated successfully.');

    expect($student->fresh()->is_active)->toBeTrue();
});

it('shows an inactive badge on the admin users page', function () {
    $admin = createLifecycleAdmin();
    $student = createLifecycleStudent(['is_active' => false]);

    $response = $this->actingAs($admin)->get(route('admin.users'));

    $response->assertOk();
    $response->assertSee($student->name);
    $response->assertSee('Inactive');
});

it('prevents an inactive user from logging in', function () {
    $student = createLifecycleStudent([
        'university_number' => 'STU-INACTIVE-LOGIN',
        'is_active' => false,
    ]);

    $response = $this->post('/Login', [
        'university_number' => $student->university_number,
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors('university_number');
    $this->assertGuest();
});

it('allows an active user to log in', function () {
    $student = createLifecycleStudent([
        'university_number' => 'STU-ACTIVE-LOGIN',
    ]);

    $response = $this->post('/Login', [
        'university_number' => $student->university_number,
        'password' => 'password',
    ]);

    $response->assertRedirect('/StudentDashboard');
    $this->assertAuthenticatedAs($student);
});

it('prevents an admin from deactivating himself', function () {
    $admin = createLifecycleAdmin();

    $this->actingAs($admin)
        ->post(route('admin.users.deactivate', $admin))
        ->assertRedirect(route('admin.users'))
        ->assertSessionHasErrors('user');

    expect($admin->fresh()->is_active)->toBeTrue();
});

it('prevents an admin from deactivating the last active admin', function () {
    $admin = createLifecycleAdmin([
        'university_number' => 'ADM-SOLE-ACTIVE',
    ]);

    $this->actingAs($admin)
        ->post(route('admin.users.deactivate', $admin))
        ->assertRedirect(route('admin.users'))
        ->assertSessionHasErrors('user');

    expect($admin->fresh()->is_active)->toBeTrue();
});

it('allows an admin to deactivate another admin when another active admin remains', function () {
    $primaryAdmin = createLifecycleAdmin([
        'university_number' => 'ADM-PRIMARY',
        'email' => 'primary.admin@test.local',
    ]);

    $secondaryAdmin = createLifecycleAdmin([
        'name' => 'Secondary Admin',
        'university_number' => 'ADM-SECONDARY',
        'email' => 'secondary.admin@test.local',
    ]);

    $this->actingAs($primaryAdmin)
        ->post(route('admin.users.deactivate', $secondaryAdmin))
        ->assertRedirect(route('admin.users'))
        ->assertSessionHas('success', 'User account deactivated successfully.');

    expect($secondaryAdmin->fresh()->is_active)->toBeFalse();
    expect($primaryAdmin->fresh()->is_active)->toBeTrue();
});

it('logs out an inactive authenticated user on the next protected request', function () {
    $student = createLifecycleStudent(['is_active' => false]);

    $this->actingAs($student)
        ->get('/StudentDashboard')
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

it('prevents inactive users from accessing role dashboards', function (string $role, string $path) {
  if ($role === 'supervisor') {
      $user = createLifecycleSupervisor(['is_active' => false]);
  } elseif ($role === 'admin') {
      $user = createLifecycleAdmin(['is_active' => false]);
  } else {
      $user = createLifecycleStudent(['is_active' => false]);
  }

  $this->actingAs($user)
      ->get($path)
      ->assertRedirect(route('login'));

  $this->assertGuest();
})->with([
    'student dashboard' => ['student', '/StudentDashboard'],
    'supervisor dashboard' => ['supervisor', '/supervisorDashboard'],
    'admin dashboard' => ['admin', '/admin'],
]);

it('creates provisioned students as active by default', function () {
    $admin = createLifecycleAdmin();

    $this->actingAs($admin)
        ->post(route('admin.students.store'), [
            'name' => 'Provisioned Student',
            'university_number' => 'STU-PROV-ACTIVE',
            'email' => 'provisioned.student@test.local',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->assertRedirect(route('admin.users'));

    $user = User::query()->where('university_number', 'STU-PROV-ACTIVE')->first();

    expect($user)->not->toBeNull();
    expect($user->is_active)->toBeTrue();
});

it('creates provisioned supervisors as active by default', function () {
    $admin = createLifecycleAdmin();

    $this->actingAs($admin)
        ->post(route('admin.supervisors.store'), [
            'name' => 'Dr. Provisioned Supervisor',
            'university_number' => 'SUP-PROV-ACTIVE',
            'email' => 'provisioned.supervisor@test.local',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->assertRedirect(route('admin.users'));

    $user = User::query()->where('university_number', 'SUP-PROV-ACTIVE')->first();

    expect($user)->not->toBeNull();
    expect($user->is_active)->toBeTrue();
});

it('creates self-registered students as active by default', function () {
    $this->post('/signup', [
        'name' => 'Self Registered Student',
        'university_number' => 'STU-SELF-ACTIVE',
        'email' => 'self.registered@test.local',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertRedirect('/StudentDashboard');

    $user = User::query()->where('university_number', 'STU-SELF-ACTIVE')->first();

    expect($user)->not->toBeNull();
    expect($user->is_active)->toBeTrue();
});

it('blocks inactive users from completing email on protected routes', function () {
    $supervisor = createLifecycleSupervisor([
        'email' => null,
        'university_number' => 'SUP-INACTIVE-NO-EMAIL',
        'is_active' => false,
    ]);

    $this->actingAs($supervisor)
        ->get(route('profile.complete-email'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});
