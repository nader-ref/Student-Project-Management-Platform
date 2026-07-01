<?php

use App\Models\ProjectSubmission;
use App\Models\Projectrequest;
use App\Models\Supervisor;
use App\Models\UniProject;
use App\Models\User;
use App\Services\StudentEnrollmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

it('resolves student enrollment from project members', function () {
    [$student, $project] = createMembershipFixture();

    $enrollment = StudentEnrollmentService::resolve(null, $student);

    expect($enrollment['mode'])->toBe(StudentEnrollmentService::MODE_ENROLLED);
    expect($enrollment['project']->id)->toBe($project->id);
    expect($enrollment['teamMembers'])->toHaveCount(1);
    expect($enrollment['teamMembers']->first()['id'])->toBe($student->university_number);
});

it('prevents duplicate project members for the same project and user', function () {
    [$student, $project] = createMembershipFixture();

    expect(fn () => $project->members()->create([
        'user_id' => $student->id,
        'position' => 2,
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});

it('prevents student submission downloads without project membership', function () {
    [, $project] = createMembershipFixture();
    $otherStudent = User::factory()->create(['university_number' => '2026002']);
    $otherStudent->addRole('student');

    $submission = ProjectSubmission::create([
        'project_id' => $project->id,
        'student_email' => 'member@test.local',
        'student_name' => 'Member Student',
        'milestone' => 'seminar_1',
        'title' => 'Initial Proposal',
        'file_path' => 'submissions/test-file.txt',
        'original_filename' => 'test-file.txt',
        'status' => 'submitted',
    ]);

    $this->actingAs($otherStudent)
        ->get("/student/submission/{$submission->id}/download")
        ->assertForbidden();
});

it('creates project members when a supervisor accepts a project request', function () {
    $student = User::factory()->create([
        'name' => 'Ahmad Al Ali',
        'university_number' => '2026001',
    ]);
    $student->addRole('student');

    $supervisorUser = User::factory()->create();
    $supervisorUser->addRole('supervisor');
    $supervisor = Supervisor::create([
        'name' => $supervisorUser->name,
        'email' => $supervisorUser->email,
        'user_id' => $supervisorUser->id,
    ]);

    $project = UniProject::create([
        'name' => 'Available Project',
        'description' => 'Project for request acceptance.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => false,
    ]);

    $projectRequest = Projectrequest::create([
        'projectid' => $project->id,
        'nameone' => $student->name,
        'oneid' => 2026001,
        'project_id' => $project->id,
        'requested_by_user_id' => $student->id,
        'count' => 1,
    ]);
    $projectRequest->members()->create([
        'user_id' => $student->id,
        'position' => 1,
    ]);

    $this->actingAs($supervisorUser)
        ->post('/acceptrequest', [
            'project' => $project->id,
            'request' => $projectRequest->id,
        ])
        ->assertSessionHas('success');

    $this->assertDatabaseHas('project_members', [
        'project_id' => $project->id,
        'user_id' => $student->id,
        'position' => 1,
    ]);

    expect($project->fresh()->student_one_id)->toBeNull();
});

function createMembershipFixture(): array
{
    $student = User::factory()->create([
        'name' => 'Ahmad Al Ali',
        'university_number' => '2026001',
    ]);
    $student->addRole('student');

    $supervisorUser = User::factory()->create();
    $supervisor = Supervisor::create([
        'name' => $supervisorUser->name,
        'email' => $supervisorUser->email,
        'user_id' => $supervisorUser->id,
    ]);

    $project = UniProject::create([
        'name' => 'AI-Assisted Graduation Project Tracker',
        'description' => 'Sample membership project.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => true,
        'student_count' => 1,
    ]);

    $project->members()->create([
        'user_id' => $student->id,
        'position' => 1,
    ]);

    return [$student, $project];
}
