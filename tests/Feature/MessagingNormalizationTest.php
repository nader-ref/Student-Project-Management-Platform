<?php

use App\Models\contact;
use App\Models\Supervisor;
use App\Models\supcontact;
use App\Models\UniProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laratrust\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    foreach (['student', 'supervisor'] as $roleName) {
        Role::firstOrCreate(
            ['name' => $roleName],
            ['display_name' => ucfirst($roleName), 'description' => $roleName.' role'],
        );
    }
});

it('creates student messages with relational student and supervisor references', function () {
    [$student, $supervisor] = createMessagingFixture();

    $this->actingAs($student)
        ->post('/Message', [
            'supervisor_id' => $supervisor->id,
            'subject' => 'Seminar question',
            'Message' => 'Can we review seminar one requirements?',
        ])
        ->assertSessionHas('success');

    $this->assertDatabaseHas('contacts', [
        'student_user_id' => $student->id,
        'supervisor_id' => $supervisor->id,
        'subject' => 'Seminar question',
    ]);
});

it('allows supervisors to reply only to their own relational messages', function () {
    [$student, $supervisor, $supervisorUser] = createMessagingFixture();
    [, $otherSupervisor, $otherSupervisorUser] = createMessagingFixture('other');

    $message = contact::create([
        'student_user_id' => $student->id,
        'supervisor_id' => $supervisor->id,
        'subject' => 'Project scope',
        'Message' => 'Can you review our scope?',
    ]);

    $this->actingAs($otherSupervisorUser)
        ->post('/supervisor/reply', [
            'contact_id' => $message->id,
            'replay' => 'This should not be allowed.',
        ])
        ->assertSessionHas('error');

    expect($message->fresh()->Replay)->toBeNull();

    $this->actingAs($supervisorUser)
        ->post('/supervisor/reply', [
            'contact_id' => $message->id,
            'replay' => 'Please narrow the scope.',
        ])
        ->assertSessionHas('success');

    expect($message->fresh()->Replay)->toBe('Please narrow the scope.');
});

it('creates supervisor announcements with relational supervisor and project references', function () {
    [, $supervisor, $supervisorUser, $project] = createMessagingFixture();

    $this->actingAs($supervisorUser)
        ->post('/supervisor/announce', [
            'project_id' => $project->id,
            'subject' => 'Seminar update',
            'message' => 'Seminar one will be next week.',
        ])
        ->assertSessionHas('success');

    $this->assertDatabaseHas('supcontacts', [
        'supervisor_id' => $supervisor->id,
        'project_id' => $project->id,
        'subject' => 'Seminar update',
    ]);
});

function createMessagingFixture(string $suffix = 'main'): array
{
    $student = User::factory()->create([
        'name' => "Student {$suffix}",
        'university_number' => "STU-MSG-{$suffix}",
    ]);
    $student->addRole('student');

    $supervisorUser = User::factory()->create([
        'name' => "Supervisor {$suffix}",
        'university_number' => "SUP-MSG-{$suffix}",
    ]);
    $supervisorUser->addRole('supervisor');

    $supervisor = Supervisor::create([
        'name' => $supervisorUser->name,
        'email' => $supervisorUser->email,
        'user_id' => $supervisorUser->id,
    ]);

    $project = UniProject::create([
        'name' => "Messaging Project {$suffix}",
        'description' => 'Project used for messaging normalization tests.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => true,
    ]);

    return [$student, $supervisor, $supervisorUser, $project];
}
