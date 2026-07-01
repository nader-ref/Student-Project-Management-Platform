<?php

use App\Models\Supervisor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laratrust\Models\Role;

uses(RefreshDatabase::class);

it('ignores submitted role values and redirects by the authenticated user role', function (
    string $actualRole,
    string $submittedRole,
    string $expectedRedirect,
) {
    foreach (['student', 'supervisor', 'admin'] as $roleName) {
        Role::firstOrCreate(
            ['name' => $roleName],
            ['display_name' => ucfirst($roleName), 'description' => $roleName.' role'],
        );
    }

    $user = User::factory()->create();
    $user->addRole($actualRole);

    if ($actualRole === 'supervisor') {
        Supervisor::create([
            'name' => $user->name,
            'email' => $user->email,
            'user_id' => $user->id,
        ]);
    }

    $response = $this->post('/Login', [
        'university_number' => $user->university_number,
        'password' => 'password',
        'role' => $submittedRole,
    ]);

    $response->assertRedirect($expectedRedirect);
    $this->assertAuthenticatedAs($user);
})->with([
    'student ignores supervisor payload' => ['student', 'supervisor', '/StudentDashboard'],
    'supervisor ignores admin payload' => ['supervisor', 'admin', '/supervisorDashboard'],
    'admin ignores student payload' => ['admin', 'student', '/admin'],
]);
