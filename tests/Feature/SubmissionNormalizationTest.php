<?php

use App\Models\ProjectMember;
use App\Models\ProjectSubmission;
use App\Models\Supervisor;
use App\Models\UniProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laratrust\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    foreach (['student', 'supervisor'] as $roleName) {
        Role::firstOrCreate(
            ['name' => $roleName],
            ['display_name' => ucfirst($roleName), 'description' => $roleName.' role'],
        );
    }

    Storage::fake('local');
});

it('creates submissions with relational submitter references', function () {
    [$student, $project] = createSubmissionFixture();

    $this->actingAs($student)
        ->post('/student/submission', [
            'milestone' => 'seminar_1',
            'title' => 'Seminar One Report',
            'file' => UploadedFile::fake()->create('seminar-one.pdf', 100, 'application/pdf'),
            'notes' => 'First draft',
        ])
        ->assertSessionHas('success');

    $this->assertDatabaseHas('project_submissions', [
        'project_id' => $project->id,
        'submitted_by_user_id' => $student->id,
        'title' => 'Seminar One Report',
        'status' => 'submitted',
    ]);
});

it('prevents uploads from students who are not project members', function () {
    [, $project] = createSubmissionFixture();
    /** @var \App\Models\User $otherStudent */
    $otherStudent = User::factory()->create(['university_number' => 'STU-SUB-002']);
    $otherStudent->addRole('student');

    $this->actingAs($otherStudent)
        ->post('/student/submission', [
            'milestone' => 'seminar_1',
            'title' => 'Unauthorized Upload',
            'file' => UploadedFile::fake()->create('report.pdf', 100, 'application/pdf'),
        ])
        ->assertSessionHas('error')
        ->assertSessionHas('active_tab', 'submissions');

    $this->assertDatabaseMissing('project_submissions', [
        'project_id' => $project->id,
        'title' => 'Unauthorized Upload',
    ]);
});

it('keeps the submissions tab active when validation fails', function () {
    [$student] = createSubmissionFixture();

    $this->actingAs($student)
        ->post('/student/submission', [
            'milestone' => 'seminar_1',
            'title' => '',
        ])
        ->assertSessionHasErrors(['title', 'file'])
        ->assertSessionHas('active_tab', 'submissions');
});

it('allows project members to download team submissions', function () {
    [$student, $project] = createSubmissionFixture();
    /** @var \App\Models\User $teammate */
    $teammate = User::factory()->create([
        'name' => 'Teammate Student',
        'university_number' => 'STU-SUB-003',
    ]);
    $teammate->addRole('student');

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $teammate->id,
        'position' => 2,
    ]);

    $submission = ProjectSubmission::create([
        'project_id' => $project->id,
        'submitted_by_user_id' => $student->id,
        'milestone' => 'seminar_1',
        'title' => 'Shared Report',
        'file_path' => 'submissions/'.$project->id.'/shared-report.pdf',
        'original_filename' => 'shared-report.pdf',
        'status' => 'submitted',
    ]);

    Storage::disk('local')->put($submission->file_path, 'sample file contents');

    $this->actingAs($teammate)
        ->get("/student/submission/{$submission->id}/download")
        ->assertOk();
});

it('prevents submission downloads without project membership', function () {
    [, $project] = createSubmissionFixture();
    /** @var \App\Models\User $otherStudent */
    $otherStudent = User::factory()->create(['university_number' => 'STU-SUB-004']);
    $otherStudent->addRole('student');

    $submission = ProjectSubmission::create([
        'project_id' => $project->id,
        'submitted_by_user_id' => User::factory()->create()->id,
        'milestone' => 'seminar_1',
        'title' => 'Protected Report',
        'file_path' => 'submissions/'.$project->id.'/protected-report.pdf',
        'original_filename' => 'protected-report.pdf',
        'status' => 'submitted',
    ]);

    Storage::disk('local')->put($submission->file_path, 'sample file contents');

    $this->actingAs($otherStudent)
        ->get("/student/submission/{$submission->id}/download")
        ->assertForbidden();
});

it('allows supervisors to review submissions for their own projects only', function () {
    [$student, $project, , $supervisorUser] = createSubmissionFixture();
    [, , , $otherSupervisorUser] = createSubmissionFixture('other');

    $submission = ProjectSubmission::create([
        'project_id' => $project->id,
        'submitted_by_user_id' => $student->id,
        'milestone' => 'seminar_1',
        'title' => 'Review Target',
        'file_path' => 'submissions/'.$project->id.'/review-target.pdf',
        'original_filename' => 'review-target.pdf',
        'status' => 'submitted',
    ]);

    $this->actingAs($otherSupervisorUser)
        ->post('/supervisor/submission/review', [
            'submission_id' => $submission->id,
            'status' => 'approved',
            'supervisor_feedback' => 'Should not apply.',
        ])
        ->assertSessionHas('error');

    expect($submission->fresh()->status)->toBe('submitted');

    $this->actingAs($supervisorUser)
        ->post('/supervisor/submission/review', [
            'submission_id' => $submission->id,
            'status' => 'approved',
            'supervisor_feedback' => 'Looks good.',
        ])
        ->assertSessionHas('success');

    expect($submission->fresh()->status)->toBe('approved')
        ->and($submission->fresh()->supervisor_feedback)->toBe('Looks good.');
});

function createSubmissionFixture(string $suffix = 'main'): array
{
    $student = User::factory()->create([
        'name' => "Student {$suffix}",
        'university_number' => "STU-SUB-{$suffix}",
    ]);
    $student->addRole('student');

    $supervisorUser = User::factory()->create([
        'name' => "Supervisor {$suffix}",
        'university_number' => "SUP-SUB-{$suffix}",
    ]);
    $supervisorUser->addRole('supervisor');

    $supervisor = Supervisor::create([
        'name' => $supervisorUser->name,
        'email' => $supervisorUser->email,
        'user_id' => $supervisorUser->id,
    ]);

    $project = UniProject::create([
        'name' => "Submission Project {$suffix}",
        'description' => 'Project used for submission normalization tests.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => true,
        'student_count' => 1,
    ]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $student->id,
        'position' => 1,
    ]);

    return [$student, $project, $supervisor, $supervisorUser];
}
