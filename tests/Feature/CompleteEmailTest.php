<?php

use App\Models\Supervisor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Laratrust\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    foreach (['admin', 'supervisor', 'student'] as $roleName) {
        Role::firstOrCreate(
            ['name' => $roleName],
            ['display_name' => ucfirst($roleName), 'description' => $roleName.' role'],
        );
    }
});

function provisionAdmin(): User
{
    $admin = User::factory()->create([
        'university_number' => 'ADM-COMPLETE-001',
        'email' => 'admin.complete@test.local',
    ]);
    $admin->addRole('admin');

    return $admin;
}

function supervisorPayloadWithoutEmail(array $overrides = []): array
{
    $payload = [
        'name' => 'Dr. No Email',
        'university_number' => 'SUP-NO-EMAIL-001',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    return array_merge($payload, $overrides);
}

function createNoEmailSupervisorViaAdmin(array $overrides = []): User
{
    $admin = provisionAdmin();
    $payload = supervisorPayloadWithoutEmail($overrides);

    test()->actingAs($admin)
        ->post(route('admin.supervisors.store'), $payload)
        ->assertRedirect(route('admin.users'));

    return User::query()->where('university_number', $payload['university_number'])->firstOrFail();
}

it('allows admin to create supervisor without email', function () {
    $user = createNoEmailSupervisorViaAdmin([
        'university_number' => 'SUP-NO-EMAIL-CREATE',
    ]);

    expect($user->hasRole('supervisor'))->toBeTrue();
    expect($user->email)->toBeNull();

    $supervisor = Supervisor::query()->where('user_id', $user->id)->first();
    expect($supervisor)->not->toBeNull();
    expect($supervisor->email)->toBeNull();
});

it('redirects no-email supervisor to complete-email on login', function () {
    $user = createNoEmailSupervisorViaAdmin([
        'university_number' => 'SUP-NO-EMAIL-LOGIN',
    ]);

    Auth::logout();

    $response = $this->post('/Login', [
        'university_number' => $user->university_number,
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('profile.complete-email'));
    $this->assertAuthenticatedAs($user);
});

it('blocks no-email supervisor from supervisor dashboard', function () {
    $user = createNoEmailSupervisorViaAdmin([
        'university_number' => 'SUP-NO-EMAIL-BLOCK',
    ]);

    $this->actingAs($user)
        ->get('/supervisorDashboard')
        ->assertRedirect(route('profile.complete-email'));
});

it('updates user and supervisor email on complete-email submission', function () {
    $user = createNoEmailSupervisorViaAdmin([
        'university_number' => 'SUP-NO-EMAIL-SAVE',
    ]);

    $this->actingAs($user)
        ->post(route('profile.complete-email.store'), [
            'email' => 'saved.supervisor@test.local',
        ])
        ->assertRedirect('/supervisorDashboard')
        ->assertSessionHas('success', 'Email saved successfully.');

    $user->refresh();
    $supervisor = Supervisor::query()->where('user_id', $user->id)->first();

    expect($user->email)->toBe('saved.supervisor@test.local');
    expect($supervisor->email)->toBe('saved.supervisor@test.local');
});

it('rejects duplicate email in users on complete-email', function () {
    User::factory()->create(['email' => 'taken.user@test.local']);
    $user = createNoEmailSupervisorViaAdmin([
        'university_number' => 'SUP-DUP-USER',
    ]);

    $this->actingAs($user)
        ->post(route('profile.complete-email.store'), [
            'email' => 'taken.user@test.local',
        ])
        ->assertSessionHasErrors('email');

    expect($user->fresh()->email)->toBeNull();
});

it('rejects duplicate email in supervisors on complete-email', function () {
    $owner = User::factory()->create([
        'email' => 'owner.supervisor@test.local',
        'university_number' => 'SUP-OWNER-DUP',
    ]);
    $owner->addRole('supervisor');

    Supervisor::create([
        'name' => $owner->name,
        'email' => 'taken.supervisor@test.local',
        'user_id' => $owner->id,
    ]);

    $user = createNoEmailSupervisorViaAdmin([
        'university_number' => 'SUP-DUP-SUP',
    ]);

    $this->actingAs($user)
        ->post(route('profile.complete-email.store'), [
            'email' => 'taken.supervisor@test.local',
        ])
        ->assertSessionHasErrors('email');

    expect($user->fresh()->email)->toBeNull();
});

it('allows logout for user without email', function () {
    $user = createNoEmailSupervisorViaAdmin([
        'university_number' => 'SUP-NO-EMAIL-LOGOUT',
    ]);

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect('/');

    $this->assertGuest();
});

it('blocks student with null email from student dashboard', function () {
    /** @var User $student */
    $student = User::factory()->create([
        'email' => null,
        'university_number' => 'STU-NO-EMAIL',
    ]);
    $student->addRole('student');

    $this->actingAs($student)
        ->get('/StudentDashboard')
        ->assertRedirect(route('profile.complete-email'));
});

it('redirects logged-in no-email user away from login to complete-email', function () {
    $user = createNoEmailSupervisorViaAdmin([
        'university_number' => 'SUP-NO-EMAIL-GUEST',
    ]);

    $this->actingAs($user)
        ->get(route('login'))
        ->assertRedirect(route('profile.complete-email'));
});

it('still redirects users with email directly to their dashboards on login', function (
    string $role,
    string $expectedRedirect,
) {
    $user = User::factory()->create([
        'university_number' => "LOGIN-{$role}-EMAIL",
        'email' => "{$role}.with.email@test.local",
    ]);
    $user->addRole($role);

    if ($role === 'supervisor') {
        Supervisor::create([
            'name' => $user->name,
            'email' => $user->email,
            'user_id' => $user->id,
        ]);
    }

    Auth::logout();

    $this->post('/Login', [
        'university_number' => $user->university_number,
        'password' => 'password',
    ])->assertRedirect($expectedRedirect);
})->with([
    'student' => ['student', '/StudentDashboard'],
    'supervisor' => ['supervisor', '/supervisorDashboard'],
    'admin' => ['admin', '/admin'],
]);

it('shows the complete-email form to authenticated users without email', function () {
    $user = createNoEmailSupervisorViaAdmin([
        'university_number' => 'SUP-NO-EMAIL-FORM',
    ]);

    $this->actingAs($user)
        ->get(route('profile.complete-email'))
        ->assertOk()
        ->assertSee('Add your email');
});

it('redirects users with email away from complete-email form', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'email' => 'already.set@test.local',
        'university_number' => 'STU-HAS-EMAIL',
    ]);
    $user->addRole('student');

    $this->actingAs($user)
        ->get(route('profile.complete-email'))
        ->assertRedirect('/StudentDashboard');
});
