<?php

use App\Models\Supervisor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laratrust\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(
        ['name' => 'supervisor'],
        ['display_name' => 'Supervisor', 'description' => 'Supervisor role'],
    );
});

function createSupervisorNavigationUser(array $overrides = []): User
{
    $user = User::factory()->create(array_merge([
        'university_number' => '200000',
        'email' => 'supervisor@example.com',
    ], $overrides));
    $user->addRole('supervisor');

    Supervisor::create([
        'name' => $user->name,
        'email' => $user->email,
        'user_id' => $user->id,
    ]);

    return $user;
}

it('renders supervisor navbar links for notifications and logout', function () {
    $user = createSupervisorNavigationUser();

    $response = $this->actingAs($user)
        ->get('/supervisorDashboard')
        ->assertOk()
        ->assertSee(route('notifications.index'), false)
        ->assertSee('method="POST"', false)
        ->assertSee(route('logout'), false)
        ->assertSee('name="_token"', false)
        ->assertDontSee('/ChangePassword', false);

    $this->actingAs($user)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertSee('Notifications')
        ->assertDontSee(route('login'), false);
});

it('does not send supervisors to the student change password route from the navbar', function () {
    $user = createSupervisorNavigationUser();

    $this->actingAs($user)
        ->get('/ChangePassword')
        ->assertRedirect('/Login');

    $this->assertGuest();
});

it('allows an authenticated supervisor to open notifications', function () {
    $user = createSupervisorNavigationUser();

    $this->actingAs($user)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertSee('Notifications');
});

it('allows an authenticated supervisor to logout without csrf errors', function () {
    $user = createSupervisorNavigationUser();

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect('/');

    $this->assertGuest();
});
