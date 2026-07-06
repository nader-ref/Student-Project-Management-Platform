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

function validSupervisorPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Dr. New Supervisor',
        'university_number' => 'SUP-NEW-001',
        'email' => 'new.supervisor@test.local',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ], $overrides);
}

function createProvisioningAdmin(): User
{
    $admin = User::factory()->create([
        'name' => 'Provisioning Admin',
        'university_number' => 'ADM-PROV-001',
        'email' => 'admin.provision@test.local',
    ]);
    $admin->addRole('admin');

    return $admin;
}

it('redirects guests away from the supervisor create form', function () {
    $this->get(route('admin.supervisors.create'))
        ->assertRedirect(route('login'));
});

it('redirects guests away from supervisor store', function () {
    $this->post(route('admin.supervisors.store'), validSupervisorPayload())
        ->assertRedirect(route('login'));
});

it('redirects students away from the supervisor create form', function () {
    /** @var User $student */
    $student = User::factory()->create();
    $student->addRole('student');

    $this->actingAs($student)
        ->get(route('admin.supervisors.create'))
        ->assertRedirect('/StudentDashboard');
});

it('redirects students away from supervisor store', function () {
    /** @var User $student */
    $student = User::factory()->create();
    $student->addRole('student');

    $this->actingAs($student)
        ->post(route('admin.supervisors.store'), validSupervisorPayload())
        ->assertRedirect('/StudentDashboard');
});

it('redirects supervisors away from the supervisor create form', function () {
    /** @var User $supervisorUser */
    $supervisorUser = User::factory()->create();
    $supervisorUser->addRole('supervisor');

    Supervisor::create([
        'name' => $supervisorUser->name,
        'email' => $supervisorUser->email,
        'user_id' => $supervisorUser->id,
    ]);

    $this->actingAs($supervisorUser)
        ->get(route('admin.supervisors.create'))
        ->assertRedirect('/supervisorDashboard');
});

it('redirects supervisors away from supervisor store', function () {
    /** @var User $supervisorUser */
    $supervisorUser = User::factory()->create();
    $supervisorUser->addRole('supervisor');

    Supervisor::create([
        'name' => $supervisorUser->name,
        'email' => $supervisorUser->email,
        'user_id' => $supervisorUser->id,
    ]);

    $this->actingAs($supervisorUser)
        ->post(route('admin.supervisors.store'), validSupervisorPayload())
        ->assertRedirect('/supervisorDashboard');
});

it('shows the supervisor create form to admins with required fields', function () {
    $admin = createProvisioningAdmin();

    $response = $this->actingAs($admin)->get(route('admin.supervisors.create'));

    $response->assertOk();
    $response->assertSee('Create Supervisor');
    $response->assertSee('name="name"', false);
    $response->assertSee('name="university_number"', false);
    $response->assertSee('name="email"', false);
    $response->assertSee('name="password"', false);
    $response->assertSee('name="password_confirmation"', false);
});

it('allows admins to create a supervisor without email', function () {
    $admin = createProvisioningAdmin();
    $payload = validSupervisorPayload();
    unset($payload['email']);

    $response = $this->actingAs($admin)
        ->post(route('admin.supervisors.store'), $payload);

    $response->assertRedirect(route('admin.users'));
    $response->assertSessionHas('success', 'Supervisor account created successfully.');

    $user = User::query()->where('university_number', $payload['university_number'])->first();

    expect($user)->not->toBeNull();
    expect($user->email)->toBeNull();
    expect($user->hasRole('supervisor'))->toBeTrue();

    $supervisor = Supervisor::query()->where('user_id', $user->id)->first();

    expect($supervisor)->not->toBeNull();
    expect($supervisor->email)->toBeNull();
});

it('allows admins to create a supervisor with valid data', function () {
    $admin = createProvisioningAdmin();
    $payload = validSupervisorPayload();

    $response = $this->actingAs($admin)
        ->post(route('admin.supervisors.store'), $payload);

    $response->assertRedirect(route('admin.users'));
    $response->assertSessionHas('success', 'Supervisor account created successfully.');

    $user = User::query()->where('university_number', $payload['university_number'])->first();

    expect($user)->not->toBeNull();
    expect($user->hasRole('supervisor'))->toBeTrue();
    expect($user->hasRole('student'))->toBeFalse();

    $supervisor = Supervisor::query()->where('user_id', $user->id)->first();

    expect($supervisor)->not->toBeNull();
    expect($supervisor->email)->toBe($user->email);
    expect($supervisor->name)->toBe($user->name);
});

it('redirects a newly created no-email supervisor to complete-email on login', function () {
    $admin = createProvisioningAdmin();
    $payload = validSupervisorPayload([
        'university_number' => 'SUP-NO-EMAIL-LOGIN',
        'email' => null,
    ]);
    unset($payload['email']);

    $this->actingAs($admin)
        ->post(route('admin.supervisors.store'), $payload)
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

it('allows a newly created supervisor to log in and reach the supervisor dashboard', function () {
    $admin = createProvisioningAdmin();
    $payload = validSupervisorPayload([
        'university_number' => 'SUP-LOGIN-001',
        'email' => 'login.supervisor@test.local',
    ]);

    $this->actingAs($admin)
        ->post(route('admin.supervisors.store'), $payload)
        ->assertRedirect(route('admin.users'));

    $user = User::query()->where('university_number', $payload['university_number'])->first();

    Auth::logout();

    $response = $this->post('/Login', [
        'university_number' => $payload['university_number'],
        'password' => $payload['password'],
    ]);

    $response->assertRedirect('/supervisorDashboard');
    $this->assertAuthenticatedAs($user);
});

it('rejects duplicate university numbers', function () {
    $admin = createProvisioningAdmin();
    $existing = User::factory()->create(['university_number' => 'SUP-DUP-NUM']);

    $response = $this->actingAs($admin)
        ->post(route('admin.supervisors.store'), validSupervisorPayload([
            'university_number' => $existing->university_number,
            'email' => 'different.email@test.local',
        ]));

    $response->assertSessionHasErrors('university_number');
    expect(Supervisor::count())->toBe(0);
});

it('rejects duplicate emails in users', function () {
    $admin = createProvisioningAdmin();
    $existing = User::factory()->create(['email' => 'duplicate.user@test.local']);

    $response = $this->actingAs($admin)
        ->post(route('admin.supervisors.store'), validSupervisorPayload([
            'university_number' => 'SUP-DUP-EMAIL-USER',
            'email' => $existing->email,
        ]));

    $response->assertSessionHasErrors('email');
    expect(Supervisor::count())->toBe(0);
});

it('rejects duplicate emails in supervisors', function () {
    $admin = createProvisioningAdmin();
    $existingUser = User::factory()->create([
        'email' => 'owner.user@test.local',
        'university_number' => 'SUP-OWNER-001',
    ]);

    Supervisor::create([
        'name' => $existingUser->name,
        'email' => 'taken.supervisor.email@test.local',
        'user_id' => $existingUser->id,
    ]);

    $response = $this->actingAs($admin)
        ->post(route('admin.supervisors.store'), validSupervisorPayload([
            'university_number' => 'SUP-DUP-EMAIL-SUP',
            'email' => 'taken.supervisor.email@test.local',
        ]));

    $response->assertSessionHasErrors('email');
    expect(User::query()->where('university_number', 'SUP-DUP-EMAIL-SUP')->exists())->toBeFalse();
});

it('rejects password confirmation mismatches', function () {
    $admin = createProvisioningAdmin();

    $response = $this->actingAs($admin)
        ->post(route('admin.supervisors.store'), validSupervisorPayload([
            'password_confirmation' => 'different-password',
        ]));

    $response->assertSessionHasErrors('password');
    expect(Supervisor::count())->toBe(0);
});

it('keeps the admin authenticated after creating a supervisor', function () {
    $admin = createProvisioningAdmin();

    $this->actingAs($admin)
        ->post(route('admin.supervisors.store'), validSupervisorPayload([
            'university_number' => 'SUP-ADMIN-STAYS',
            'email' => 'admin.stays@test.local',
        ]))
        ->assertRedirect(route('admin.users'));

    $this->assertAuthenticatedAs($admin);
});
