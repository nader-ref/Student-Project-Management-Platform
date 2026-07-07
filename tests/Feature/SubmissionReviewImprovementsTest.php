<?php

use App\Models\ProjectMember;
use App\Models\ProjectSubmission;
use App\Models\Supervisor;
use App\Models\UniProject;
use App\Models\User;
use App\Services\StudentEnrollmentService;
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

    Storage::fake('public');
});

it('rejects needs_revision review without supervisor feedback', function () {
    [$student, $project, , $supervisorUser] = createReviewFixture();
    $submission = createReviewSubmission($project, $student);

    $this->actingAs($supervisorUser)
        ->post('/supervisor/submission/review', [
            'submission_id' => $submission->id,
            'status' => 'needs_revision',
        ])
        ->assertSessionHasErrors('supervisor_feedback');

    expect($submission->fresh()->status)->toBe('submitted');
});

it('allows needs_revision review with supervisor feedback', function () {
    [$student, $project, , $supervisorUser] = createReviewFixture();
    $submission = createReviewSubmission($project, $student);

    $this->actingAs($supervisorUser)
        ->post('/supervisor/submission/review', [
            'submission_id' => $submission->id,
            'status' => 'needs_revision',
            'supervisor_feedback' => 'Please expand the methodology section.',
        ])
        ->assertSessionHas('success');

    $submission->refresh();
    expect($submission->status)->toBe('needs_revision');
    expect($submission->supervisor_feedback)->toBe('Please expand the methodology section.');
});

it('sets reviewed_at when a supervisor reviews a submission', function () {
    [$student, $project, , $supervisorUser] = createReviewFixture();
    $submission = createReviewSubmission($project, $student);

    expect($submission->reviewed_at)->toBeNull();

    $this->actingAs($supervisorUser)
        ->post('/supervisor/submission/review', [
            'submission_id' => $submission->id,
            'status' => 'approved',
            'supervisor_feedback' => 'Well done.',
        ])
        ->assertSessionHas('success');

    expect($submission->fresh()->reviewed_at)->not->toBeNull();
});

it('sets reviewed_by_user_id when a supervisor reviews a submission', function () {
    [$student, $project, , $supervisorUser] = createReviewFixture();
    $submission = createReviewSubmission($project, $student);

    $this->actingAs($supervisorUser)
        ->post('/supervisor/submission/review', [
            'submission_id' => $submission->id,
            'status' => 'approved',
            'supervisor_feedback' => 'Well done.',
        ])
        ->assertSessionHas('success');

    expect($submission->fresh()->reviewed_by_user_id)->toBe($supervisorUser->id);
});

it('notifies the student when a submission is approved', function () {
    [$student, $project, , $supervisorUser] = createReviewFixture();
    $submission = createReviewSubmission($project, $student);

    $this->actingAs($supervisorUser)
        ->post('/supervisor/submission/review', [
            'submission_id' => $submission->id,
            'status' => 'approved',
            'supervisor_feedback' => 'Looks good.',
        ])
        ->assertSessionHas('success');

    $student->refresh();
    expect($student->notifications)->toHaveCount(1);
    expect($student->notifications->first()->data['type'])->toBe('submission_reviewed');
    expect($student->notifications->first()->data['body'])->toContain('Seminar 1');
    expect($student->notifications->first()->data['body'])->toContain('approved');
});

it('notifies the student when revision is required', function () {
    [$student, $project, , $supervisorUser] = createReviewFixture();
    $submission = createReviewSubmission($project, $student);

    $this->actingAs($supervisorUser)
        ->post('/supervisor/submission/review', [
            'submission_id' => $submission->id,
            'status' => 'needs_revision',
            'supervisor_feedback' => 'Add more detail to the literature review.',
        ])
        ->assertSessionHas('success');

    $student->refresh();
    expect($student->notifications)->toHaveCount(1);
    expect($student->notifications->first()->data['body'])->toContain('Revision required');
    expect($student->notifications->first()->data['body'])->toContain('Seminar 1');
});

it('does not send a duplicate notification on no-op review save', function () {
    [$student, $project, , $supervisorUser] = createReviewFixture();
    $submission = createReviewSubmission($project, $student);

    $payload = [
        'submission_id' => $submission->id,
        'status' => 'approved',
        'supervisor_feedback' => 'Looks good.',
    ];

    $this->actingAs($supervisorUser)
        ->post('/supervisor/submission/review', $payload)
        ->assertSessionHas('success');

    $this->actingAs($supervisorUser)
        ->post('/supervisor/submission/review', $payload)
        ->assertSessionHas('success');

    $student->refresh();
    expect($student->notifications)->toHaveCount(1);
});

it('blocks duplicate submitted uploads for the same project and milestone', function () {
    [$student, $project] = createReviewFixture();
    createReviewSubmission($project, $student);

    $this->actingAs($student)
        ->post('/student/submission', [
            'milestone' => 'seminar_1',
            'title' => 'Duplicate Seminar Upload',
            'file' => UploadedFile::fake()->create('duplicate.pdf', 100, 'application/pdf'),
        ])
        ->assertSessionHasErrors('milestone');

    expect(ProjectSubmission::where('project_id', $project->id)->where('milestone', 'seminar_1')->count())->toBe(1);
});

it('blocks duplicate approved uploads for the same project and milestone', function () {
    [$student, $project, , $supervisorUser] = createReviewFixture();
    $submission = createReviewSubmission($project, $student);

    $this->actingAs($supervisorUser)
        ->post('/supervisor/submission/review', [
            'submission_id' => $submission->id,
            'status' => 'approved',
            'supervisor_feedback' => 'Approved.',
        ])
        ->assertSessionHas('success');

    $this->actingAs($student)
        ->post('/student/submission', [
            'milestone' => 'seminar_1',
            'title' => 'Another Seminar Upload',
            'file' => UploadedFile::fake()->create('another.pdf', 100, 'application/pdf'),
        ])
        ->assertSessionHasErrors('milestone');

    expect(ProjectSubmission::where('project_id', $project->id)->where('milestone', 'seminar_1')->count())->toBe(1);
});

it('allows resubmission after needs_revision for the same milestone', function () {
    [$student, $project, , $supervisorUser] = createReviewFixture();
    $submission = createReviewSubmission($project, $student);

    $this->actingAs($supervisorUser)
        ->post('/supervisor/submission/review', [
            'submission_id' => $submission->id,
            'status' => 'needs_revision',
            'supervisor_feedback' => 'Please revise the introduction.',
        ])
        ->assertSessionHas('success');

    $this->actingAs($student)
        ->post('/student/submission', [
            'milestone' => 'seminar_1',
            'title' => 'Revised Seminar One Report',
            'file' => UploadedFile::fake()->create('revised.pdf', 100, 'application/pdf'),
        ])
        ->assertSessionHas('success');

    expect(ProjectSubmission::where('project_id', $project->id)->where('milestone', 'seminar_1')->count())->toBe(2);
    expect(ProjectSubmission::where('project_id', $project->id)->where('milestone', 'seminar_1')->where('status', 'submitted')->count())->toBe(1);
});

it('marks milestone progress done when a submission is approved', function () {
    $project = UniProject::create([
        'name' => 'Progress Review Project',
        'description' => 'Project for progress review tests.',
        'supervisor_id' => Supervisor::create([
            'name' => 'Progress Supervisor',
            'email' => 'progress.supervisor@test.local',
            'user_id' => User::factory()->create()->id,
        ])->id,
        'department' => 'software',
        'taken' => true,
        'seminar_1' => now()->addWeeks(4)->toDateString(),
        'seminar_2' => now()->addWeeks(8)->toDateString(),
        'seminar_3' => now()->addWeeks(12)->toDateString(),
        'final' => now()->addWeeks(16)->toDateString(),
    ]);

    $submission = ProjectSubmission::create([
        'project_id' => $project->id,
        'submitted_by_user_id' => User::factory()->create()->id,
        'milestone' => 'seminar_1',
        'title' => 'Approved Seminar Report',
        'file_path' => 'submissions/'.$project->id.'/approved.pdf',
        'original_filename' => 'approved.pdf',
        'status' => 'approved',
    ]);

    $milestones = StudentEnrollmentService::buildMilestones($project);
    $progress = StudentEnrollmentService::computeProgress($project, $milestones, collect([$submission]));

    $seminarStep = $progress['steps']->firstWhere('key', 'seminar_1');
    expect($seminarStep)->not->toBeNull();
    expect($seminarStep['done'])->toBeTrue();
});

it('does not mark milestone progress done for needs_revision submissions', function () {
    $project = UniProject::create([
        'name' => 'Revision Progress Project',
        'description' => 'Project for revision progress tests.',
        'supervisor_id' => Supervisor::create([
            'name' => 'Revision Supervisor',
            'email' => 'revision.supervisor@test.local',
            'user_id' => User::factory()->create()->id,
        ])->id,
        'department' => 'software',
        'taken' => true,
        'seminar_1' => now()->addWeeks(4)->toDateString(),
        'seminar_2' => now()->addWeeks(8)->toDateString(),
        'seminar_3' => now()->addWeeks(12)->toDateString(),
        'final' => now()->addWeeks(16)->toDateString(),
    ]);

    $submission = ProjectSubmission::create([
        'project_id' => $project->id,
        'submitted_by_user_id' => User::factory()->create()->id,
        'milestone' => 'seminar_1',
        'title' => 'Revision Seminar Report',
        'file_path' => 'submissions/'.$project->id.'/revision.pdf',
        'original_filename' => 'revision.pdf',
        'status' => 'needs_revision',
        'supervisor_feedback' => 'Revise the scope section.',
    ]);

    $milestones = StudentEnrollmentService::buildMilestones($project);
    $progress = StudentEnrollmentService::computeProgress($project, $milestones, collect([$submission]));

    $seminarStep = $progress['steps']->firstWhere('key', 'seminar_1');
    expect($seminarStep)->not->toBeNull();
    expect($seminarStep['done'])->toBeFalse();
});

function createReviewFixture(): array
{
    $student = User::factory()->create([
        'name' => 'Review Student',
        'university_number' => 'REV-STU-001',
    ]);
    $student->addRole('student');

    $supervisorUser = User::factory()->create([
        'name' => 'Review Supervisor',
        'university_number' => 'REV-SUP-001',
    ]);
    $supervisorUser->addRole('supervisor');

    $supervisor = Supervisor::create([
        'name' => $supervisorUser->name,
        'email' => $supervisorUser->email,
        'user_id' => $supervisorUser->id,
    ]);

    $project = UniProject::create([
        'name' => 'Review Test Project',
        'description' => 'Project used for submission review improvement tests.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => true,
        'student_count' => 1,
        'seminar_1' => now()->addWeeks(4)->toDateString(),
        'seminar_2' => now()->addWeeks(8)->toDateString(),
        'seminar_3' => now()->addWeeks(12)->toDateString(),
        'final' => now()->addWeeks(16)->toDateString(),
    ]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $student->id,
        'position' => 1,
    ]);

    return [$student, $project, $supervisor, $supervisorUser];
}

function createReviewSubmission(UniProject $project, User $student): ProjectSubmission
{
    return ProjectSubmission::create([
        'project_id' => $project->id,
        'submitted_by_user_id' => $student->id,
        'milestone' => 'seminar_1',
        'title' => 'Seminar One Report',
        'file_path' => 'submissions/'.$project->id.'/seminar-one.pdf',
        'original_filename' => 'seminar-one.pdf',
        'status' => 'submitted',
    ]);
}
