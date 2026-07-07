<?php

use App\Models\Supervisor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Notifications\ResetPassword;
use Laratrust\Models\Role;

uses(RefreshDatabase::class);

const GENERIC_RESET_MESSAGE = 'If this university number is linked to an email address, a password reset link will be sent.';
const GENERIC_RESET_ERROR = 'Unable to reset password. Please check your information or request a new reset link.';

beforeEach(function () {
    foreach (['admin', 'supervisor', 'student'] as $roleName) {
        Role::firstOrCreate(
            ['name' => $roleName],
            ['display_name' => ucfirst($roleName), 'description' => $roleName.' role'],
        );
    }
});

it('redirects public registration routes to login', function () {
    $this->get('/signup')->assertRedirect(route('login'));
    $this->post('/signup', [
        'name' => 'Self Registered',
        'university_number' => 'SELF-REG-001',
        'email' => 'self.registered@test.local',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertRedirect(route('login'));

    expect(User::where('university_number', 'SELF-REG-001')->exists())->toBeFalse();
});

it('does not show public registration links on the welcome page', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertDontSee('Create student account', false);
    $response->assertDontSee('Create account', false);
    $response->assertDontSee(route('register'), false);
});

it('shows a single login entry point on the welcome page', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee(route('login'), false);
    $response->assertDontSee('Student sign in', false);
    $response->assertDontSee('Supervisor sign in', false);
    $response->assertDontSee('Supervisor portal', false);
});

it('redirects student supervisor and admin logins to the correct dashboards', function (
    string $role,
    string $expectedRedirect,
) {
    $user = User::factory()->create([
        'university_number' => "LOGIN-{$role}-FLOW",
        'email' => "{$role}.flow@test.local",
    ]);
    $user->addRole($role);

    if ($role === 'supervisor') {
        Supervisor::create([
            'name' => $user->name,
            'email' => $user->email,
            'user_id' => $user->id,
        ]);
    }

    $this->post('/Login', [
        'university_number' => $user->university_number,
        'password' => 'password',
        'remember' => 'on',
    ])->assertRedirect($expectedRedirect);
})->with([
    'student' => ['student', '/StudentDashboard'],
    'supervisor' => ['supervisor', '/supervisorDashboard'],
    'admin' => ['admin', '/admin'],
]);

it('shows the forgot password link and remember me checkbox on the login page', function () {
    $response = $this->get(route('login'));

    $response->assertOk();
    $response->assertSee('css/auth/auth.css', false);
    $response->assertSee(route('password.request'), false);
    $response->assertSee('Remember me', false);
    $response->assertSee('name="remember"', false);
});

it('uses the same auth layout and styling on the forgot password page as login', function () {
    $response = $this->get(route('password.request'));

    $response->assertOk();
    $response->assertSee('css/auth/auth.css', false);
    $response->assertSee('auth-card', false);
    $response->assertSee('Forgot Password', false);
    $response->assertSee('Enter your university number and we will send a reset link to the email linked to your account.', false);
});

it('shows university number field on the forgot password page', function () {
    $response = $this->get(route('password.request'));

    $response->assertOk();
    $response->assertSee('name="university_number"', false);
    $response->assertSee('University Number', false);
    $response->assertDontSee('name="email"', false);
});

it('shows a back to sign in link on the forgot password page', function () {
    $this->get(route('password.request'))
        ->assertOk()
        ->assertSee('Back to Sign In', false)
        ->assertSee(route('login'), false);
});

it('shows university number and password fields on the reset password page', function () {
    $response = $this->get(route('password.reset', ['token' => 'sample-token']));

    $response->assertOk();
    $response->assertSee('css/auth/auth.css', false);
    $response->assertSee('auth-card', false);
    $response->assertSee('Reset Password', false);
    $response->assertSee('Enter your university number and choose a new password.', false);
    $response->assertSee('name="university_number"', false);
    $response->assertSee('name="password"', false);
    $response->assertSee('name="password_confirmation"', false);
    $response->assertSee('name="token"', false);
    $response->assertSee('value="sample-token"', false);
    $response->assertDontSee('name="email"', false);
});

it('shows a back to sign in link on the reset password page', function () {
    $this->get(route('password.reset', ['token' => 'sample-token']))
        ->assertOk()
        ->assertSee('Back to Sign In', false)
        ->assertSee(route('login'), false);
});

it('sends a reset link for an existing user with email when university number is submitted', function () {
    Notification::fake();

    $user = User::factory()->create([
        'university_number' => 'RESET-WITH-EMAIL',
        'email' => 'reset.with.email@test.local',
    ]);

    $this->post(route('password.email'), [
        'university_number' => $user->university_number,
    ])
        ->assertRedirect()
        ->assertSessionHas('status', GENERIC_RESET_MESSAGE);

    Notification::assertSentTo($user, ResetPassword::class);
});

it('shows the same generic response for a non-existing university number', function () {
    Notification::fake();

    $this->post(route('password.email'), [
        'university_number' => 'MISSING-UNI-NUMBER',
    ])
        ->assertRedirect()
        ->assertSessionHas('status', GENERIC_RESET_MESSAGE);

    Notification::assertNothingSent();
});

it('shows the same generic response for an existing user without email', function () {
    Notification::fake();

    $user = User::factory()->create([
        'university_number' => 'RESET-NO-EMAIL',
        'email' => null,
    ]);

    $this->post(route('password.email'), [
        'university_number' => $user->university_number,
    ])
        ->assertRedirect()
        ->assertSessionHas('status', GENERIC_RESET_MESSAGE);

    Notification::assertNothingSent();
});

it('does not honor arbitrary email fields in forgot password submissions', function () {
    Notification::fake();

    $user = User::factory()->create([
        'university_number' => 'RESET-REAL-USER',
        'email' => 'real.user@test.local',
    ]);

    $this->post(route('password.email'), [
        'email' => 'attacker@test.local',
        'university_number' => $user->university_number,
    ])
        ->assertRedirect()
        ->assertSessionHas('status', GENERIC_RESET_MESSAGE);

    Notification::assertSentTo($user, ResetPassword::class);
    Notification::assertCount(1);
});

it('allows a user to reset password with a valid token and correct university number', function () {
    $user = User::factory()->create([
        'university_number' => 'RESET-TOKEN-OK',
        'email' => 'reset.token.ok@test.local',
    ]);

    $token = Password::createToken($user);

    $this->get(route('password.reset', ['token' => $token]))
        ->assertOk();

    $this->post(route('password.update'), [
        'token' => $token,
        'university_number' => $user->university_number,
        'password' => 'newresetpass123',
        'password_confirmation' => 'newresetpass123',
    ])
        ->assertRedirect(route('login'))
        ->assertSessionHas('status', 'Password reset successful!');

    expect(Hash::check('newresetpass123', $user->fresh()->password))->toBeTrue();
});

it('fails reset when university number does not match the token owner', function () {
    $user = User::factory()->create([
        'university_number' => 'RESET-TOKEN-OWNER',
        'email' => 'reset.token.owner@test.local',
    ]);

    $otherUser = User::factory()->create([
        'university_number' => 'RESET-TOKEN-OTHER',
        'email' => 'reset.token.other@test.local',
    ]);

    $token = Password::createToken($user);

    $this->post(route('password.update'), [
        'token' => $token,
        'university_number' => $otherUser->university_number,
        'password' => 'newresetpass123',
        'password_confirmation' => 'newresetpass123',
    ])
        ->assertRedirect()
        ->assertSessionHasErrors(['reset' => GENERIC_RESET_ERROR]);

    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
    expect(Hash::check('password', $otherUser->fresh()->password))->toBeTrue();
});

it('shows a generic error for an unknown university number on reset password', function () {
    $user = User::factory()->create([
        'university_number' => 'RESET-TOKEN-MISSING',
        'email' => 'reset.token.missing@test.local',
    ]);

    $token = Password::createToken($user);

    $this->post(route('password.update'), [
        'token' => $token,
        'university_number' => 'UNKNOWN-RESET-NUMBER',
        'password' => 'newresetpass123',
        'password_confirmation' => 'newresetpass123',
    ])
        ->assertRedirect()
        ->assertSessionHasErrors(['reset' => GENERIC_RESET_ERROR]);
});

it('shows a generic error when resetting an account without email', function () {
    $user = User::factory()->create([
        'university_number' => 'RESET-NO-EMAIL-TOKEN',
        'email' => null,
    ]);

    $this->post(route('password.update'), [
        'token' => 'unused-token',
        'university_number' => $user->university_number,
        'password' => 'newresetpass123',
        'password_confirmation' => 'newresetpass123',
    ])
        ->assertRedirect()
        ->assertSessionHasErrors(['reset' => GENERIC_RESET_ERROR]);
});

it('does not honor arbitrary email fields in reset password submissions', function () {
    $user = User::factory()->create([
        'university_number' => 'RESET-EMAIL-BYPASS',
        'email' => 'reset.email.bypass@test.local',
    ]);

    $otherUser = User::factory()->create([
        'university_number' => 'RESET-EMAIL-VICTIM',
        'email' => 'reset.email.victim@test.local',
    ]);

    $token = Password::createToken($otherUser);

    $this->post(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'university_number' => $user->university_number,
        'password' => 'newresetpass123',
        'password_confirmation' => 'newresetpass123',
    ])
        ->assertRedirect()
        ->assertSessionHasErrors(['reset' => GENERIC_RESET_ERROR]);

    expect(Hash::check('password', $otherUser->fresh()->password))->toBeTrue();
});

it('keeps admin password reset working', function () {
    /** @var \App\Models\User $admin */
    $admin = User::factory()->create([
        'university_number' => 'ADM-PUBLIC-FLOW',
        'email' => 'admin.public.flow@test.local',
    ]);
    $admin->addRole('admin');

    $student = User::factory()->create([
        'university_number' => 'STU-PUBLIC-FLOW',
        'email' => 'student.public.flow@test.local',
    ]);
    $student->addRole('student');

    $this->actingAs($admin)
        ->post(route('admin.users.reset-password.store', $student), [
            'password' => 'adminreset123',
            'password_confirmation' => 'adminreset123',
        ])
        ->assertRedirect(route('admin.users'))
        ->assertSessionHas('success', 'Password reset successfully. Share the temporary password securely with the user.');

    expect(Hash::check('adminreset123', $student->fresh()->password))->toBeTrue();
});
