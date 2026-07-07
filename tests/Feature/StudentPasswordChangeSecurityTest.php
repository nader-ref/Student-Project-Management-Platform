<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laratrust\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(
        ['name' => 'student'],
        ['display_name' => 'Student', 'description' => 'Student role'],
    );
});

function createPasswordChangeStudent(array $overrides = []): User
{
    $student = User::factory()->create(array_merge([
        'university_number' => 'PWD-STU-'.uniqid(),
        'email' => 'pwd.student@test.local',
        'password' => 'password',
    ], $overrides));
    $student->addRole('student');

    return $student;
}

it('allows a student to change their own password with the correct current password', function () {
    $student = createPasswordChangeStudent([
        'university_number' => 'PWD-STU-OWN',
    ]);

    $this->actingAs($student)
        ->post('/change', [
            'old' => 'password',
            'new' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Password changed successfully!');

    expect(Hash::check('newpassword123', $student->fresh()->password))->toBeTrue();
});

it('does not change password when the current password is wrong', function () {
    $student = createPasswordChangeStudent([
        'university_number' => 'PWD-STU-WRONG',
    ]);

    $this->actingAs($student)
        ->post('/change', [
            'old' => 'wrong-password',
            'new' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])
        ->assertSessionHasErrors('old');

    expect(Hash::check('password', $student->fresh()->password))->toBeTrue();
});

it('does not allow a student to change another users password by submitting another email', function () {
    $student = createPasswordChangeStudent([
        'university_number' => 'PWD-STU-ATTACKER',
    ]);

    $victim = createPasswordChangeStudent([
        'university_number' => 'PWD-STU-VICTIM',
        'email' => 'victim.password@test.local',
    ]);

    $this->actingAs($student)
        ->post('/change', [
            'email' => $victim->email,
            'old' => 'password',
            'new' => 'hackedpassword1',
            'password_confirmation' => 'hackedpassword1',
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Password changed successfully!');

    expect(Hash::check('hackedpassword1', $student->fresh()->password))->toBeTrue();
    expect(Hash::check('password', $victim->fresh()->password))->toBeTrue();
});
