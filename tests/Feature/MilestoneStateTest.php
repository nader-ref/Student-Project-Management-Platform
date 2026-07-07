<?php

use App\Models\ProjectSubmission;
use App\Models\Supervisor;
use App\Models\UniProject;
use App\Models\User;
use App\Services\StudentEnrollmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;

uses(RefreshDatabase::class);

it('does not mark a past milestone date alone as done', function () {
    $project = createMilestoneProject([
        'seminar_1' => now()->subWeek()->toDateString(),
    ]);

    $milestones = StudentEnrollmentService::buildMilestones($project);
    $progress = StudentEnrollmentService::computeProgress($project, $milestones, collect());

    $seminarStep = $progress['steps']->firstWhere('key', 'seminar_1');
    expect($seminarStep)->not->toBeNull();
    expect($seminarStep['done'])->toBeFalse();
});

it('resolves a past milestone without approved submission as overdue', function () {
    $project = createMilestoneProject([
        'seminar_1' => now()->subWeek()->toDateString(),
    ]);

    $state = StudentEnrollmentService::resolveMilestoneStates($project, collect())
        ->firstWhere('key', 'seminar_1');

    expect($state['status_key'])->toBe('overdue');
    expect($state['status_label'])->toBe('Overdue');
    expect($state['is_done'])->toBeFalse();
    expect($state['is_overdue'])->toBeTrue();
});

it('marks a milestone done when the latest submission is approved', function () {
    $project = createMilestoneProject([
        'seminar_1' => now()->addWeeks(2)->toDateString(),
    ]);

    $submission = createMilestoneSubmission($project, 'seminar_1', 'approved');

    $state = StudentEnrollmentService::resolveMilestoneStates($project, collect([$submission]))
        ->firstWhere('key', 'seminar_1');

    expect($state['status_key'])->toBe('approved');
    expect($state['status_label'])->toBe('Completed');
    expect($state['is_done'])->toBeTrue();
});

it('resolves the latest submitted submission as pending review', function () {
    $project = createMilestoneProject([
        'seminar_1' => now()->addWeeks(2)->toDateString(),
    ]);

    $submission = createMilestoneSubmission($project, 'seminar_1', 'submitted');

    $state = StudentEnrollmentService::resolveMilestoneStates($project, collect([$submission]))
        ->firstWhere('key', 'seminar_1');

    expect($state['status_key'])->toBe('pending_review');
    expect($state['status_label'])->toBe('Pending Review');
});

it('resolves the latest needs revision submission as revision required', function () {
    $project = createMilestoneProject([
        'seminar_1' => now()->addWeeks(2)->toDateString(),
    ]);

    $submission = createMilestoneSubmission($project, 'seminar_1', 'needs_revision', 'Expand the scope.');

    $state = StudentEnrollmentService::resolveMilestoneStates($project, collect([$submission]))
        ->firstWhere('key', 'seminar_1');

    expect($state['status_key'])->toBe('revision_required');
    expect($state['status_label'])->toBe('Revision Required');
});

it('suppresses stale revision next steps when a newer submitted resubmission exists', function () {
    $project = createMilestoneProject([
        'seminar_1' => now()->addWeeks(2)->toDateString(),
        'seminar_2' => now()->addWeeks(6)->toDateString(),
        'seminar_3' => now()->addWeeks(10)->toDateString(),
        'final' => now()->addWeeks(14)->toDateString(),
    ]);

    $oldRevision = createMilestoneSubmission($project, 'seminar_1', 'needs_revision', 'Please revise.');
    $oldRevision->forceFill(['created_at' => now()->subDays(3)])->save();

    $newSubmission = createMilestoneSubmission($project, 'seminar_1', 'submitted', null, 'Resubmitted Report');
    $newSubmission->forceFill(['created_at' => now()->subDay()])->save();

    $submissions = collect([$oldRevision, $newSubmission]);
    $nextSteps = StudentEnrollmentService::buildNextSteps(
        $project,
        StudentEnrollmentService::buildMilestones($project),
        $submissions,
        StudentEnrollmentService::nextUpcomingMilestone(StudentEnrollmentService::buildMilestones($project)),
        collect(),
    );

    expect($nextSteps->pluck('title')->join(' '))->not->toContain('Revise your Seminar 1 submission');
    expect($nextSteps->pluck('title')->join(' '))->toContain('Seminar 1 submission under review');
});

it('handles milestones without scheduled dates as not scheduled', function () {
    $project = createMilestoneProject([]);

    $states = StudentEnrollmentService::resolveMilestoneStates($project, collect());

    expect($states)->toHaveCount(4);
    expect($states->every(fn (array $state) => $state['status_key'] === 'not_scheduled'))->toBeTrue();
    expect($states->first()['status_label'])->toBe('Not scheduled');
});

it('resolves a future milestone within fourteen days as due soon', function () {
    $project = createMilestoneProject([
        'seminar_1' => now()->addDays(10)->toDateString(),
    ]);

    $state = StudentEnrollmentService::resolveMilestoneStates($project, collect())
        ->firstWhere('key', 'seminar_1');

    expect($state['status_key'])->toBe('due_soon');
    expect($state['status_label'])->toBe('Due soon');
});

it('resolves a future milestone beyond fourteen days as upcoming', function () {
    $project = createMilestoneProject([
        'seminar_1' => now()->addDays(21)->toDateString(),
    ]);

    $state = StudentEnrollmentService::resolveMilestoneStates($project, collect())
        ->firstWhere('key', 'seminar_1');

    expect($state['status_key'])->toBe('upcoming');
    expect($state['status_label'])->toBe('Upcoming');
});

it('calculates progress percent from approved submissions only not past dates', function () {
    $project = createMilestoneProject([
        'seminar_1' => now()->subWeek()->toDateString(),
        'seminar_2' => now()->addWeeks(4)->toDateString(),
        'seminar_3' => now()->addWeeks(8)->toDateString(),
        'final' => now()->addWeeks(12)->toDateString(),
    ]);

    $milestones = StudentEnrollmentService::buildMilestones($project);
    $withoutApproval = StudentEnrollmentService::computeProgress($project, $milestones, collect());
    expect($withoutApproval['percent'])->toBe(20);

    $approved = createMilestoneSubmission($project, 'seminar_1', 'approved');
    $withApproval = StudentEnrollmentService::computeProgress($project, $milestones, collect([$approved]));
    expect($withApproval['percent'])->toBe(40);
    expect($withApproval['steps']->firstWhere('key', 'seminar_1')['done'])->toBeTrue();
});

it('returns the latest submission per milestone by created_at', function () {
    $project = createMilestoneProject([
        'seminar_1' => now()->addWeeks(2)->toDateString(),
    ]);

    $older = createMilestoneSubmission($project, 'seminar_1', 'needs_revision', 'Old feedback');
    $older->forceFill(['created_at' => now()->subDays(2)])->save();

    $latest = createMilestoneSubmission($project, 'seminar_1', 'submitted', null, 'Latest Upload');
    $latest->forceFill(['created_at' => now()->subDay()])->save();

    $latestByMilestone = StudentEnrollmentService::latestSubmissionsByMilestone(collect([$older, $latest]));

    expect($latestByMilestone->get('seminar_1')->id)->toBe($latest->id);
    expect($latestByMilestone->get('seminar_1')->status)->toBe('submitted');
});

function createMilestoneProject(array $dates): UniProject
{
    return UniProject::create(array_merge([
        'name' => 'Milestone State Project',
        'description' => 'Project used for milestone state tests.',
        'supervisor_id' => Supervisor::create([
            'name' => 'Milestone Supervisor',
            'email' => 'milestone.supervisor@test.local',
            'user_id' => User::factory()->create()->id,
        ])->id,
        'department' => 'software',
        'taken' => true,
    ], $dates));
}

function createMilestoneSubmission(
    UniProject $project,
    string $milestone,
    string $status,
    ?string $feedback = null,
    string $title = 'Milestone Report',
): ProjectSubmission {
    return ProjectSubmission::create([
        'project_id' => $project->id,
        'submitted_by_user_id' => User::factory()->create()->id,
        'milestone' => $milestone,
        'title' => $title,
        'file_path' => 'submissions/'.$project->id.'/'.$milestone.'.pdf',
        'original_filename' => $milestone.'.pdf',
        'status' => $status,
        'supervisor_feedback' => $feedback,
    ]);
}
