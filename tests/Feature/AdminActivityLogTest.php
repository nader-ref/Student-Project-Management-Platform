<?php

use App\Models\ActivityLog;
use App\Models\Idea;
use App\Models\IdeaMember;
use App\Models\ProjectMember;
use App\Models\Projectrequest;
use App\Models\ProjectRequestMember;
use App\Models\ProjectSubmission;
use App\Models\Supervisor;
use App\Models\UniProject;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laratrust\Models\Role;

beforeEach(function () {
    foreach (['admin', 'supervisor', 'student'] as $roleName) {
        Role::firstOrCreate(
            ['name' => $roleName],
            ['display_name' => ucfirst($roleName), 'description' => $roleName.' role'],
        );
    }

    Storage::fake('public');
});

it('redirects guests away from the activity log page', function () {
    $this->get(route('admin.activity'))->assertRedirect(route('login'));
});

it('redirects students away from the activity log page', function () {
    $student = createActivityStudent();

    $this->actingAs($student)
        ->get(route('admin.activity'))
        ->assertRedirect('/StudentDashboard');
});

it('redirects supervisors away from the activity log page', function () {
    [, $supervisorUser] = createActivitySupervisorPair();

    $this->actingAs($supervisorUser)
        ->get(route('admin.activity'))
        ->assertRedirect('/supervisorDashboard');
});

it('allows admins to access the activity log page', function () {
    $admin = createActivityAdmin();

    $this->actingAs($admin)
        ->get(route('admin.activity'))
        ->assertOk()
        ->assertSee('Activity Log')
        ->assertSee('Read-only audit trail');
});

it('creates an activity log when an admin creates a supervisor', function () {
    $admin = createActivityAdmin();

    $this->actingAs($admin)
        ->post(route('admin.supervisors.store'), activitySupervisorPayload([
            'university_number' => 'ACT-SUP-001',
            'email' => 'act.supervisor@test.local',
        ]))
        ->assertRedirect(route('admin.users'));

    $createdUser = User::where('university_number', 'ACT-SUP-001')->firstOrFail();

    $log = ActivityLog::query()->where('action', ActivityLogger::USER_SUPERVISOR_CREATED)->first();

    expect($log)->not->toBeNull();
    expect($log->actor_user_id)->toBe($admin->id);
    expect($log->target_user_id)->toBe($createdUser->id);
    expect($log->metadata['role'])->toBe('supervisor');
});

it('creates an activity log when an admin creates a student', function () {
    $admin = createActivityAdmin();

    $this->actingAs($admin)
        ->post(route('admin.students.store'), activityStudentPayload([
            'university_number' => 'ACT-STU-001',
            'email' => 'act.student@test.local',
        ]))
        ->assertRedirect(route('admin.users'));

    $createdUser = User::where('university_number', 'ACT-STU-001')->firstOrFail();

    $log = ActivityLog::query()->where('action', ActivityLogger::USER_STUDENT_CREATED)->first();

    expect($log)->not->toBeNull();
    expect($log->actor_user_id)->toBe($admin->id);
    expect($log->target_user_id)->toBe($createdUser->id);
    expect($log->metadata['role'])->toBe('student');
});

it('creates an activity log when an admin activates a user', function () {
    $admin = createActivityAdmin();
    $student = createActivityStudent(['is_active' => false]);

    $this->actingAs($admin)
        ->post(route('admin.users.activate', $student))
        ->assertRedirect(route('admin.users'));

    $log = ActivityLog::query()->where('action', ActivityLogger::USER_ACTIVATED)->first();

    expect($log)->not->toBeNull();
    expect($log->actor_user_id)->toBe($admin->id);
    expect($log->target_user_id)->toBe($student->id);
});

it('creates an activity log when an admin deactivates a user', function () {
    $admin = createActivityAdmin();
    $student = createActivityStudent();

    $this->actingAs($admin)
        ->post(route('admin.users.deactivate', $student))
        ->assertRedirect(route('admin.users'));

    $log = ActivityLog::query()->where('action', ActivityLogger::USER_DEACTIVATED)->first();

    expect($log)->not->toBeNull();
    expect($log->actor_user_id)->toBe($admin->id);
    expect($log->target_user_id)->toBe($student->id);
});

it('creates an activity log when an admin resets a password without storing the password', function () {
    $admin = createActivityAdmin();
    $student = createActivityStudent(['university_number' => 'ACT-PW-RESET']);

    $this->actingAs($admin)
        ->post(route('admin.users.reset-password.store', $student), [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])
        ->assertRedirect(route('admin.users'));

    $log = ActivityLog::query()->where('action', ActivityLogger::USER_PASSWORD_RESET)->first();

    expect($log)->not->toBeNull();
    expect($log->metadata['university_number'])->toBe('ACT-PW-RESET');
    expect($log->description)->not->toContain('newpassword123');
    expect(json_encode($log->metadata))->not->toContain('newpassword123');
    expect(collect($log->metadata)->keys())->not->toContain('password');
});

it('creates an activity log when a student submits a project request', function () {
    [$supervisorUser, $supervisor, $student] = createActivityWorkflowFixture();
    $project = createActivityProject($supervisor);

    $this->actingAs($student)
        ->post('/RequstAdd', [
            'project_id' => $project->id,
            'count' => 1,
            'oneid' => $student->university_number,
        ])
        ->assertSessionHas('success');

    $log = ActivityLog::query()->where('action', ActivityLogger::PROJECT_REQUEST_SUBMITTED)->first();

    expect($log)->not->toBeNull();
    expect($log->actor_user_id)->toBe($student->id);
    expect($log->metadata['project_id'])->toBe($project->id);
});

it('creates an activity log when a student submits an idea', function () {
    [$supervisorUser, $supervisor, $student] = createActivityWorkflowFixture();

    $this->actingAs($student)
        ->post('/RequstIdea', [
            'projectname' => 'Activity Idea',
            'count' => 1,
            'supervisor_id' => $supervisor->id,
            'oneid' => $student->university_number,
        ])
        ->assertSessionHas('success');

    $log = ActivityLog::query()->where('action', ActivityLogger::IDEA_SUBMITTED)->first();

    expect($log)->not->toBeNull();
    expect($log->actor_user_id)->toBe($student->id);
    expect($log->metadata['title'])->toBe('Activity Idea');
});

it('creates an activity log when a student uploads a submission', function () {
    [$supervisorUser, $supervisor, $student, $project] = createActivitySubmissionFixture();

    $this->actingAs($student)
        ->post('/student/submission', [
            'milestone' => 'seminar_1',
            'title' => 'Activity Upload',
            'file' => UploadedFile::fake()->create('activity.pdf', 100, 'application/pdf'),
        ])
        ->assertSessionHas('success');

    $log = ActivityLog::query()->where('action', ActivityLogger::SUBMISSION_UPLOADED)->first();

    expect($log)->not->toBeNull();
    expect($log->actor_user_id)->toBe($student->id);
    expect($log->metadata['title'])->toBe('Activity Upload');
});

it('creates an activity log when a supervisor accepts a project request', function () {
    [$supervisorUser, $supervisor, $student] = createActivityWorkflowFixture();
    $project = createActivityProject($supervisor);
    $projectRequest = createActivityProjectRequest($project, $student);

    $this->actingAs($supervisorUser)
        ->post('/acceptrequest', ['request' => $projectRequest->id])
        ->assertSessionHas('success');

    $log = ActivityLog::query()->where('action', ActivityLogger::PROJECT_REQUEST_ACCEPTED)->first();

    expect($log)->not->toBeNull();
    expect($log->actor_user_id)->toBe($supervisorUser->id);
    expect($log->metadata['request_id'])->toBe($projectRequest->id);
});

it('creates an activity log when a supervisor rejects a project request', function () {
    [$supervisorUser, $supervisor, $student] = createActivityWorkflowFixture();
    $project = createActivityProject($supervisor);
    $projectRequest = createActivityProjectRequest($project, $student);

    $this->actingAs($supervisorUser)
        ->post('/rejectrequest', [
            'request' => $projectRequest->id,
            'reason' => 'Scope is too broad.',
        ])
        ->assertSessionHas('success');

    $log = ActivityLog::query()->where('action', ActivityLogger::PROJECT_REQUEST_REJECTED)->first();

    expect($log)->not->toBeNull();
    expect($log->actor_user_id)->toBe($supervisorUser->id);
    expect($log->metadata['reason'])->toBe('Scope is too broad.');
});

it('creates an activity log when a supervisor accepts an idea', function () {
    [$supervisorUser, $supervisor, $student] = createActivityWorkflowFixture();
    $idea = createActivityIdea($supervisor, $student);

    $this->actingAs($supervisorUser)
        ->post('/acceptidea', ['idea' => $idea->id])
        ->assertSessionHas('success');

    $log = ActivityLog::query()->where('action', ActivityLogger::IDEA_ACCEPTED)->first();

    expect($log)->not->toBeNull();
    expect($log->actor_user_id)->toBe($supervisorUser->id);
    expect($log->metadata['created_project_id'])->not->toBeNull();
});

it('creates an activity log when a supervisor rejects an idea', function () {
    [$supervisorUser, $supervisor, $student] = createActivityWorkflowFixture();
    $idea = createActivityIdea($supervisor, $student);

    $this->actingAs($supervisorUser)
        ->post('/rejectidea', [
            'idea' => $idea->id,
            'reason' => 'Needs more detail.',
        ])
        ->assertSessionHas('success');

    $log = ActivityLog::query()->where('action', ActivityLogger::IDEA_REJECTED)->first();

    expect($log)->not->toBeNull();
    expect($log->actor_user_id)->toBe($supervisorUser->id);
    expect($log->metadata['reason'])->toBe('Needs more detail.');
});

it('creates an activity log when a supervisor reviews a submission', function () {
    [$supervisorUser, $supervisor, $student, $project] = createActivitySubmissionFixture();
    $submission = createActivitySubmission($project, $student);

    $this->actingAs($supervisorUser)
        ->post('/supervisor/submission/review', [
            'submission_id' => $submission->id,
            'status' => 'approved',
            'supervisor_feedback' => 'Well done.',
        ])
        ->assertSessionHas('success');

    $log = ActivityLog::query()->where('action', ActivityLogger::SUBMISSION_REVIEWED)->first();

    expect($log)->not->toBeNull();
    expect($log->actor_user_id)->toBe($supervisorUser->id);
    expect($log->target_user_id)->toBe($student->id);
    expect($log->metadata['new_status'])->toBe('approved');
});

it('does not create an activity log for a no-op submission review', function () {
    [$supervisorUser, $supervisor, $student, $project] = createActivitySubmissionFixture();
    $submission = createActivitySubmission($project, $student, [
        'status' => 'approved',
        'supervisor_feedback' => 'Already approved.',
        'reviewed_at' => now(),
        'reviewed_by_user_id' => $supervisorUser->id,
    ]);

    $this->actingAs($supervisorUser)
        ->post('/supervisor/submission/review', [
            'submission_id' => $submission->id,
            'status' => 'approved',
            'supervisor_feedback' => 'Already approved.',
        ])
        ->assertSessionHas('success');

    expect(ActivityLog::query()->where('action', ActivityLogger::SUBMISSION_REVIEWED)->count())->toBe(0);
});

it('shows newest activity logs first with pagination', function () {
    $admin = createActivityAdmin();

    for ($index = 1; $index <= 26; $index++) {
        ActivityLogger::log(
            ActivityLogger::USER_ACTIVATED,
            sprintf('Synthetic log %02d', $index),
            actor: $admin,
        );
    }

    $paginator = ActivityLog::query()->latest('created_at')->paginate(25);

    expect($paginator->total())->toBe(26);
    expect($paginator->hasPages())->toBeTrue();
    expect($paginator->first()->description)->toBe('Synthetic log 26');
    expect($paginator->last()->description)->toBe('Synthetic log 02');

    $this->actingAs($admin)
        ->get(route('admin.activity'))
        ->assertOk()
        ->assertSee('Synthetic log 26')
        ->assertSee('Synthetic log 02')
        ->assertDontSee('Synthetic log 01');
});

it('does not create an activity log when a guarded admin action fails', function () {
    $admin = createActivityAdmin();

    $this->actingAs($admin)
        ->post(route('admin.users.deactivate', $admin))
        ->assertRedirect(route('admin.users'))
        ->assertSessionHasErrors('user');

    expect(ActivityLog::query()->where('action', ActivityLogger::USER_DEACTIVATED)->count())->toBe(0);
});

function createActivityAdmin(): User
{
    $admin = User::factory()->create([
        'name' => 'Activity Admin',
        'university_number' => 'ACT-ADM-001',
        'email' => 'activity.admin@test.local',
    ]);
    $admin->addRole('admin');

    return $admin;
}

function createActivityStudent(array $overrides = []): User
{
    $student = User::factory()->create(array_merge([
        'name' => 'Activity Student',
        'university_number' => 'ACT-STU-'.uniqid(),
        'email' => 'activity.student@test.local',
    ], $overrides));
    $student->addRole('student');

    return $student;
}

function activitySupervisorPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Dr. Activity Supervisor',
        'university_number' => 'ACT-SUP-DEFAULT',
        'email' => 'activity.supervisor@test.local',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ], $overrides);
}

function activityStudentPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Activity Student',
        'university_number' => 'ACT-STU-DEFAULT',
        'email' => 'activity.student.default@test.local',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ], $overrides);
}

/**
 * @return array{0: User, 1: Supervisor, 2: User}
 */
function createActivityWorkflowFixture(): array
{
    $student = createActivityStudent(['university_number' => 'ACT-WF-STU-'.uniqid()]);

    $supervisorUser = User::factory()->create([
        'name' => 'Activity Supervisor',
        'university_number' => 'ACT-WF-SUP-'.uniqid(),
        'email' => 'activity.workflow.supervisor@test.local',
    ]);
    $supervisorUser->addRole('supervisor');

    $supervisor = Supervisor::create([
        'name' => $supervisorUser->name,
        'email' => $supervisorUser->email,
        'user_id' => $supervisorUser->id,
    ]);

    return [$supervisorUser, $supervisor, $student];
}

/**
 * @return array{0: User, 1: Supervisor}
 */
function createActivitySupervisorPair(): array
{
    [$supervisorUser, $supervisor] = createActivityWorkflowFixture();

    return [$supervisor, $supervisorUser];
}

function createActivityProject(Supervisor $supervisor, bool $taken = false): UniProject
{
    return UniProject::create([
        'name' => 'Activity Project '.uniqid(),
        'description' => 'Project used for activity log tests.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => $taken,
    ]);
}

function createActivityProjectRequest(UniProject $project, User $student): Projectrequest
{
    $projectRequest = Projectrequest::create([
        'project_id' => $project->id,
        'requested_by_user_id' => $student->id,
        'count' => 1,
        'accepted' => false,
        'rejected' => false,
    ]);

    ProjectRequestMember::create([
        'project_request_id' => $projectRequest->id,
        'user_id' => $student->id,
        'position' => 1,
    ]);

    return $projectRequest;
}

function createActivityIdea(Supervisor $supervisor, User $student): Idea
{
    $idea = Idea::create([
        'projectname' => 'Activity Idea '.uniqid(),
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

    return $idea;
}

/**
 * @return array{0: User, 1: Supervisor, 2: User, 3: UniProject}
 */
function createActivitySubmissionFixture(): array
{
    [$supervisorUser, $supervisor, $student] = createActivityWorkflowFixture();

    $project = UniProject::create([
        'name' => 'Activity Submission Project',
        'description' => 'Project used for submission activity tests.',
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

    return [$supervisorUser, $supervisor, $student, $project];
}

function createActivitySubmission(UniProject $project, User $student, array $overrides = []): ProjectSubmission
{
    return ProjectSubmission::create(array_merge([
        'project_id' => $project->id,
        'submitted_by_user_id' => $student->id,
        'milestone' => 'seminar_1',
        'title' => 'Activity Submission',
        'file_path' => 'submissions/'.$project->id.'/activity.pdf',
        'original_filename' => 'activity.pdf',
        'status' => 'submitted',
    ], $overrides));
}
