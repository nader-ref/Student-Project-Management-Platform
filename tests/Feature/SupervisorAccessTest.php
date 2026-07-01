<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laratrust\Models\Role;

uses(RefreshDatabase::class);

it('rejects supervisor role users without a linked supervisor profile', function () {
    Role::firstOrCreate(
        ['name' => 'supervisor'],
        ['display_name' => 'Supervisor', 'description' => 'Supervisor role'],
    );

    $user = User::factory()->create();
    $user->addRole('supervisor');

    $response = $this->actingAs($user)->get('/supervisorDashboard');

    $response->assertRedirect('/supervisorSignup');
    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});
