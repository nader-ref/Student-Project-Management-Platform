<?php

use App\Models\Idea;
use App\Models\IdeaMember;
use App\Models\Projectrequest;
use App\Models\ProjectRequestMember;
use App\Models\Supervisor;
use App\Models\UniProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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

it('authorizes supervisor project updates from auth without session identity keys', function () {
    [$supervisorUser, $supervisor, $project] = createSupervisorSessionFixture();

    $this->actingAs($supervisorUser)
        ->post('/updateproject', [
            'project_id' => $project->id,
            'project_name' => 'Updated Project Name',
            'description' => 'Updated description.',
            'department' => 'software',
            'taken' => 'Yes',
            'students_number' => 1,
            'student_one_id' => "2026001-main",
            'seminar1_date' => now()->addWeeks(2)->toDateString(),
            'seminar2_date' => now()->addWeeks(6)->toDateString(),
            'seminar3_date' => now()->addWeeks(10)->toDateString(),
            'final_date' => now()->addWeeks(14)->toDateString(),
        ])
        ->assertSessionHas('success');

    expect($project->fresh()->name)->toBe('Updated Project Name');
});

it('prevents supervisors from updating another supervisors project without session identity keys', function () {
    [, , $project] = createSupervisorSessionFixture();
    [$otherSupervisorUser] = createSupervisorSessionFixture('other');

    $this->actingAs($otherSupervisorUser)
        ->post('/updateproject', [
            'project_id' => $project->id,
            'project_name' => 'Blocked Update',
            'description' => 'Should not apply.',
            'department' => 'software',
            'taken' => 'Yes',
            'seminar1_date' => now()->addWeeks(2)->toDateString(),
            'seminar2_date' => now()->addWeeks(6)->toDateString(),
            'seminar3_date' => now()->addWeeks(10)->toDateString(),
            'final_date' => now()->addWeeks(14)->toDateString(),
        ])
        ->assertSessionHas('error');

    expect($project->fresh()->name)->not->toBe('Blocked Update');
});

it('accepts ideas using authenticated supervisor identity without session keys', function () {
    [$supervisorUser, $supervisor] = createSupervisorSessionFixture();
    $student = User::factory()->create(['university_number' => '2026101']);
    $student->addRole('student');

    $idea = Idea::create([
        'projectname' => 'Auth-Only Idea Project',
        'count' => 1,
        'supervisor_id' => $supervisor->id,
        'requested_by_user_id' => $student->id,
        'accepted' => 0,
        'rejected' => 0,
    ]);
    IdeaMember::create([
        'idea_id' => $idea->id,
        'user_id' => $student->id,
        'position' => 1,
    ]);

    $this->actingAs($supervisorUser)
        ->post('/acceptidea', ['idea' => $idea->id])
        ->assertSessionHas('success');

    $this->assertDatabaseHas('uni_projects', [
        'name' => 'Auth-Only Idea Project',
        'supervisor_id' => $supervisor->id,
    ]);
});

it('rejects project requests using authenticated supervisor identity without session keys', function () {
    [$supervisorUser, $supervisor, $project] = createSupervisorSessionFixture();
    $student = User::factory()->create(['university_number' => '2026201']);
    $student->addRole('student');

    $projectRequest = Projectrequest::create([
        'project_id' => $project->id,
        'requested_by_user_id' => $student->id,
        'count' => 1,
    ]);
    ProjectRequestMember::create([
        'project_request_id' => $projectRequest->id,
        'user_id' => $student->id,
        'position' => 1,
    ]);

    $this->actingAs($supervisorUser)
        ->post('/rejectrequest', [
            'request' => $projectRequest->id,
            'reason' => 'Team size does not fit.',
        ])
        ->assertSessionHas('success');

    expect($projectRequest->fresh()->rejected)->toBe(1);
});

it('changes supervisor password using authenticated user without session email', function () {
    [$supervisorUser] = createSupervisorSessionFixture();

    $this->actingAs($supervisorUser)
        ->post('/supervisor/changepassword', [
            'old_password' => 'password',
            'new_password' => 'new-password-1',
            'new_password_confirmation' => 'new-password-1',
        ])
        ->assertSessionHas('success');

    expect(Hash::check('new-password-1', $supervisorUser->fresh()->password))->toBeTrue();
});

function createSupervisorSessionFixture(string $suffix = 'main'): array
{
    $student = User::factory()->create([
        'name' => 'Member Student',
        'university_number' => "2026001-{$suffix}",
    ]);
    $student->addRole('student');

    $supervisorUser = User::factory()->create([
        'name' => "Supervisor {$suffix}",
        'university_number' => "SUP-SES-{$suffix}",
    ]);
    $supervisorUser->addRole('supervisor');

    $supervisor = Supervisor::create([
        'name' => $supervisorUser->name,
        'email' => $supervisorUser->email,
        'user_id' => $supervisorUser->id,
    ]);

    $project = UniProject::create([
        'name' => "Session Cleanup Project {$suffix}",
        'description' => 'Project used for session cleanup tests.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => true,
        'student_count' => 1,
        'seminar_1' => now()->addWeeks(2)->toDateString(),
        'seminar_2' => now()->addWeeks(6)->toDateString(),
        'seminar_3' => now()->addWeeks(10)->toDateString(),
        'final' => now()->addWeeks(14)->toDateString(),
    ]);

    $project->members()->create([
        'user_id' => $student->id,
        'position' => 1,
    ]);

    return [$supervisorUser, $supervisor, $project];
}
