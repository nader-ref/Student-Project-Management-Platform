<?php

use App\Models\Supervisor;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laratrust\Models\Role;

beforeEach(function () {
    foreach (['admin', 'supervisor', 'student'] as $roleName) {
        Role::firstOrCreate(
            ['name' => $roleName],
            ['display_name' => ucfirst($roleName), 'description' => $roleName.' role'],
        );
    }
});

function validStudentPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'New Student',
        'university_number' => 'STU-NEW-001',
        'email' => 'new.student@test.local',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ], $overrides);
}

function createStudentProvisioningAdmin(): User
{
    $admin = User::factory()->create([
        'name' => 'Provisioning Admin',
        'university_number' => 'ADM-STU-PROV-001',
        'email' => 'admin.student.provision@test.local',
    ]);
    $admin->addRole('admin');

    return $admin;
}

it('redirects guests away from the student create form', function () {
    $this->get(route('admin.students.create'))
        ->assertRedirect(route('login'));
});

it('redirects guests away from student store', function () {
    $this->post(route('admin.students.store'), validStudentPayload())
        ->assertRedirect(route('login'));
});

it('redirects students away from the student create form', function () {
    /** @var User $student */
    $student = User::factory()->create();
    $student->addRole('student');

    $this->actingAs($student)
        ->get(route('admin.students.create'))
        ->assertRedirect('/StudentDashboard');
});

it('redirects students away from student store', function () {
    /** @var User $student */
    $student = User::factory()->create();
    $student->addRole('student');

    $this->actingAs($student)
        ->post(route('admin.students.store'), validStudentPayload())
        ->assertRedirect('/StudentDashboard');
});

it('redirects supervisors away from the student create form', function () {
    /** @var User $supervisorUser */
    $supervisorUser = User::factory()->create();
    $supervisorUser->addRole('supervisor');

    Supervisor::create([
        'name' => $supervisorUser->name,
        'email' => $supervisorUser->email,
        'user_id' => $supervisorUser->id,
    ]);

    $this->actingAs($supervisorUser)
        ->get(route('admin.students.create'))
        ->assertRedirect('/supervisorDashboard');
});

it('redirects supervisors away from student store', function () {
    /** @var User $supervisorUser */
    $supervisorUser = User::factory()->create();
    $supervisorUser->addRole('supervisor');

    Supervisor::create([
        'name' => $supervisorUser->name,
        'email' => $supervisorUser->email,
        'user_id' => $supervisorUser->id,
    ]);

    $this->actingAs($supervisorUser)
        ->post(route('admin.students.store'), validStudentPayload())
        ->assertRedirect('/supervisorDashboard');
});

it('shows the student create form to admins with required fields', function () {
    $admin = createStudentProvisioningAdmin();

    $response = $this->actingAs($admin)->get(route('admin.students.create'));

    $response->assertOk();
    $response->assertSee('Create Student');
    $response->assertSee('name="name"', false);
    $response->assertSee('name="university_number"', false);
    $response->assertSee('name="email"', false);
    $response->assertSee('name="password"', false);
    $response->assertSee('name="password_confirmation"', false);
});

it('allows admins to create a student with email', function () {
    $admin = createStudentProvisioningAdmin();
    $payload = validStudentPayload();

    $response = $this->actingAs($admin)
        ->post(route('admin.students.store'), $payload);

    $response->assertRedirect(route('admin.users'));
    $response->assertSessionHas('success', 'Student account created successfully.');

    $user = User::query()->where('university_number', $payload['university_number'])->first();

    expect($user)->not->toBeNull();
    expect($user->email)->toBe($payload['email']);
    expect($user->hasRole('student'))->toBeTrue();
    expect($user->hasRole('supervisor'))->toBeFalse();
    expect(Supervisor::query()->where('user_id', $user->id)->exists())->toBeFalse();
});

it('allows admins to create a student without email', function () {
    $admin = createStudentProvisioningAdmin();
    $payload = validStudentPayload();
    unset($payload['email']);

    $response = $this->actingAs($admin)
        ->post(route('admin.students.store'), $payload);

    $response->assertRedirect(route('admin.users'));
    $response->assertSessionHas('success', 'Student account created successfully.');

    $user = User::query()->where('university_number', $payload['university_number'])->first();

    expect($user)->not->toBeNull();
    expect($user->email)->toBeNull();
    expect($user->hasRole('student'))->toBeTrue();
    expect(Supervisor::count())->toBe(0);
});

it('redirects a newly created no-email student to complete-email on login', function () {
    $admin = createStudentProvisioningAdmin();
    $payload = validStudentPayload([
        'university_number' => 'STU-NO-EMAIL-LOGIN',
    ]);
    unset($payload['email']);

    $this->actingAs($admin)
        ->post(route('admin.students.store'), $payload)
        ->assertRedirect(route('admin.users'));

    $user = User::query()->where('university_number', $payload['university_number'])->first();

    Auth::logout();

    $response = $this->post('/Login', [
        'university_number' => $payload['university_number'],
        'password' => $payload['password'],
    ]);

    $response->assertRedirect(route('profile.complete-email'));
    $this->assertAuthenticatedAs($user);
});

it('allows a newly created student with email to log in and reach the student dashboard', function () {
    $admin = createStudentProvisioningAdmin();
    $payload = validStudentPayload([
        'university_number' => 'STU-LOGIN-001',
        'email' => 'login.student@test.local',
    ]);

    $this->actingAs($admin)
        ->post(route('admin.students.store'), $payload)
        ->assertRedirect(route('admin.users'));

    $user = User::query()->where('university_number', $payload['university_number'])->first();

    Auth::logout();

    $response = $this->post('/Login', [
        'university_number' => $payload['university_number'],
        'password' => $payload['password'],
    ]);

    $response->assertRedirect('/StudentDashboard');
    $this->assertAuthenticatedAs($user);
});

it('rejects duplicate university numbers', function () {
    $admin = createStudentProvisioningAdmin();
    $existing = User::factory()->create(['university_number' => 'STU-DUP-NUM']);

    $response = $this->actingAs($admin)
        ->post(route('admin.students.store'), validStudentPayload([
            'university_number' => $existing->university_number,
            'email' => 'different.email@test.local',
        ]));

    $response->assertSessionHasErrors('university_number');
    expect(User::query()->where('email', 'different.email@test.local')->exists())->toBeFalse();
});

it('rejects duplicate emails', function () {
    $admin = createStudentProvisioningAdmin();
    $existing = User::factory()->create(['email' => 'duplicate.student@test.local']);

    $response = $this->actingAs($admin)
        ->post(route('admin.students.store'), validStudentPayload([
            'university_number' => 'STU-DUP-EMAIL',
            'email' => $existing->email,
        ]));

    $response->assertSessionHasErrors('email');
    expect(User::query()->where('university_number', 'STU-DUP-EMAIL')->exists())->toBeFalse();
});

it('rejects password confirmation mismatches', function () {
    $admin = createStudentProvisioningAdmin();

    $response = $this->actingAs($admin)
        ->post(route('admin.students.store'), validStudentPayload([
            'password_confirmation' => 'different-password',
        ]));

    $response->assertSessionHasErrors('password');
    expect(User::query()->where('university_number', 'STU-NEW-001')->exists())->toBeFalse();
});

it('keeps the admin authenticated after creating a student', function () {
    $admin = createStudentProvisioningAdmin();

    $this->actingAs($admin)
        ->post(route('admin.students.store'), validStudentPayload([
            'university_number' => 'STU-ADMIN-STAYS',
            'email' => 'admin.stays.student@test.local',
        ]))
        ->assertRedirect(route('admin.users'));

    $this->assertAuthenticatedAs($admin);
});
