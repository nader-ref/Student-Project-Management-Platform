<?php

use App\Models\contact;
use App\Models\Idea;
use App\Models\IdeaMember;
use App\Models\ProjectMember;
use App\Models\Projectrequest;
use App\Models\ProjectRequestMember;
use App\Models\ProjectSubmission;
use App\Models\Supervisor;
use App\Models\UniProject;
use App\Models\User;
use App\Notifications\WorkflowNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laratrust\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    foreach (['student', 'supervisor', 'admin'] as $roleName) {
        Role::firstOrCreate(
            ['name' => $roleName],
            ['display_name' => ucfirst($roleName), 'description' => $roleName.' role'],
        );
    }

    Storage::fake('local');
});

it('notifies supervisor when student submits project request', function () {
    [$supervisorUser, $supervisor, $student] = createNotificationFixture();
    $project = createNotificationProject($supervisor);

    $this->actingAs($student)
        ->post('/RequstAdd', [
            'project_id' => $project->id,
            'count' => 1,
            'oneid' => $student->university_number,
        ])
        ->assertSessionHas('success');

    $supervisorUser->refresh();
    expect($supervisorUser->notifications)->toHaveCount(1);
    expect($supervisorUser->notifications->first()->data['type'])->toBe('request_submitted');
});

it('notifies all request members when supervisor accepts project request', function () {
    [$supervisorUser, $supervisor, $student] = createNotificationFixture();
    $teammate = User::factory()->create(['university_number' => 'NOTIF-STU-TEAM']);
    $teammate->addRole('student');

    $project = createNotificationProject($supervisor);
    $projectRequest = Projectrequest::create([
        'project_id' => $project->id,
        'requested_by_user_id' => $student->id,
        'count' => 2,
    ]);

    ProjectRequestMember::create([
        'project_request_id' => $projectRequest->id,
        'user_id' => $student->id,
        'position' => 1,
    ]);
    ProjectRequestMember::create([
        'project_request_id' => $projectRequest->id,
        'user_id' => $teammate->id,
        'position' => 2,
    ]);

    $this->actingAs($supervisorUser)
        ->post('/acceptrequest', ['request' => $projectRequest->id])
        ->assertSessionHas('success');

    $student->refresh();
    $teammate->refresh();

    expect($student->notifications)->toHaveCount(1);
    expect($teammate->notifications)->toHaveCount(1);
    expect($student->notifications->first()->data['type'])->toBe('request_accepted');
    expect($teammate->notifications->first()->data['type'])->toBe('request_accepted');
});

it('notifies request members when supervisor rejects project request', function () {
    [$supervisorUser, $supervisor, $student] = createNotificationFixture();
    $project = createNotificationProject($supervisor);
    $projectRequest = createNotificationProjectRequest($project, $student);

    $this->actingAs($supervisorUser)
        ->post('/rejectrequest', [
            'request' => $projectRequest->id,
            'reason' => 'Scope is too broad.',
        ])
        ->assertSessionHas('success');

    $student->refresh();
    expect($student->notifications)->toHaveCount(1);
    expect($student->notifications->first()->data['type'])->toBe('request_rejected');
});

it('notifies supervisor when student submits idea', function () {
    [$supervisorUser, $supervisor, $student] = createNotificationFixture();

    $this->actingAs($student)
        ->post('/RequstIdea', [
            'projectname' => 'Smart Campus App',
            'count' => 1,
            'supervisor_id' => $supervisor->id,
            'oneid' => $student->university_number,
        ])
        ->assertSessionHas('success');

    $supervisorUser->refresh();
    expect($supervisorUser->notifications)->toHaveCount(1);
    expect($supervisorUser->notifications->first()->data['type'])->toBe('idea_submitted');
});

it('notifies idea members when supervisor accepts idea', function () {
    [$supervisorUser, $supervisor, $student] = createNotificationFixture();
    $idea = createNotificationIdea($supervisor, $student);

    $this->actingAs($supervisorUser)
        ->post('/acceptidea', ['idea' => $idea->id])
        ->assertSessionHas('success');

    $student->refresh();
    expect($student->notifications)->toHaveCount(1);
    expect($student->notifications->first()->data['type'])->toBe('idea_accepted');
});

it('notifies idea members when supervisor rejects idea', function () {
    [$supervisorUser, $supervisor, $student] = createNotificationFixture();
    $idea = createNotificationIdea($supervisor, $student);

    $this->actingAs($supervisorUser)
        ->post('/rejectidea', [
            'idea' => $idea->id,
            'reason' => 'Needs more detail.',
        ])
        ->assertSessionHas('success');

    $student->refresh();
    expect($student->notifications)->toHaveCount(1);
    expect($student->notifications->first()->data['type'])->toBe('idea_rejected');
});

it('notifies supervisor when student sends message', function () {
    [$supervisorUser, $supervisor, $student] = createNotificationFixture();

    $this->actingAs($student)
        ->post('/Message', [
            'supervisor_id' => $supervisor->id,
            'subject' => 'Seminar question',
            'Message' => 'Can we review seminar one requirements?',
        ])
        ->assertSessionHas('success');

    $supervisorUser->refresh();
    expect($supervisorUser->notifications)->toHaveCount(1);
    expect($supervisorUser->notifications->first()->data['type'])->toBe('message_received');
});

it('notifies student when supervisor replies to message', function () {
    [$supervisorUser, $supervisor, $student] = createNotificationFixture();
    $message = contact::create([
        'student_user_id' => $student->id,
        'supervisor_id' => $supervisor->id,
        'subject' => 'Project scope',
        'Message' => 'Can you review our scope?',
    ]);

    $this->actingAs($supervisorUser)
        ->post('/supervisor/reply', [
            'contact_id' => $message->id,
            'replay' => 'Please narrow the scope.',
        ])
        ->assertSessionHas('success');

    $student->refresh();
    expect($student->notifications)->toHaveCount(1);
    expect($student->notifications->first()->data['type'])->toBe('message_reply');
});

it('notifies supervisor when student uploads submission', function () {
    [$supervisorUser, $supervisor, $student, $project] = createNotificationSubmissionFixture();

    $this->actingAs($student)
        ->post('/student/submission', [
            'milestone' => 'seminar_1',
            'title' => 'Seminar One Report',
            'file' => UploadedFile::fake()->create('seminar-one.pdf', 100, 'application/pdf'),
        ])
        ->assertSessionHas('success');

    $supervisorUser->refresh();
    expect($supervisorUser->notifications)->toHaveCount(1);
    expect($supervisorUser->notifications->first()->data['type'])->toBe('submission_uploaded');
});

it('notifies submitter when supervisor reviews submission', function () {
    [$supervisorUser, $supervisor, $student, $project] = createNotificationSubmissionFixture();
    $submission = ProjectSubmission::create([
        'project_id' => $project->id,
        'submitted_by_user_id' => $student->id,
        'milestone' => 'seminar_1',
        'title' => 'Review Target',
        'file_path' => 'submissions/'.$project->id.'/review-target.pdf',
        'original_filename' => 'review-target.pdf',
        'status' => 'submitted',
    ]);

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
    expect($student->notifications->first()->data['body'])->toContain('approved');
});

it('shows only current user notifications on index', function () {
    [$supervisorUser, , $student] = createNotificationFixture();

    $student->notify(new WorkflowNotification(
        type: 'request_accepted',
        title: 'Student notification',
        body: 'For student only.',
        actionUrl: '/StudentDashboard/acceptance',
    ));

    $supervisorUser->notify(new WorkflowNotification(
        type: 'request_submitted',
        title: 'Supervisor notification',
        body: 'For supervisor only.',
        actionUrl: '/supervisorDashboard',
    ));

    $this->actingAs($student)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertSee('Student notification')
        ->assertDontSee('Supervisor notification');

    $this->actingAs($supervisorUser)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertSee('Supervisor notification')
        ->assertDontSee('Student notification');
});

it('prevents users from marking another users notification as read', function () {
    [$supervisorUser, , $student] = createNotificationFixture();

    $student->notify(new WorkflowNotification(
        type: 'request_accepted',
        title: 'Private student notification',
        body: 'Only the student should access this.',
        actionUrl: '/StudentDashboard/acceptance',
    ));

    $notificationId = $student->notifications()->first()->id;

    $this->actingAs($supervisorUser)
        ->post(route('notifications.read', $notificationId))
        ->assertNotFound();

    expect($student->fresh()->unreadNotifications)->toHaveCount(1);
});

it('marks one notification as read', function () {
    [, , $student] = createNotificationFixture();

    $student->notify(new WorkflowNotification(
        type: 'request_accepted',
        title: 'Mark me read',
        body: 'This should become read.',
        actionUrl: '/StudentDashboard/acceptance',
    ));

    $notificationId = $student->notifications()->first()->id;

    $this->actingAs($student)
        ->post(route('notifications.read', $notificationId))
        ->assertRedirect(route('notifications.index'));

    expect($student->fresh()->unreadNotifications)->toHaveCount(0);
    expect($student->fresh()->readNotifications)->toHaveCount(1);
});

it('marks all notifications as read', function () {
    [, , $student] = createNotificationFixture();

    $student->notify(new WorkflowNotification(
        type: 'request_accepted',
        title: 'First notification',
        body: 'Unread one.',
        actionUrl: '/StudentDashboard/acceptance',
    ));
    $student->notify(new WorkflowNotification(
        type: 'idea_accepted',
        title: 'Second notification',
        body: 'Unread two.',
        actionUrl: '/StudentDashboard/acceptanceidea',
    ));

    $this->actingAs($student)
        ->post(route('notifications.read-all'))
        ->assertRedirect(route('notifications.index'));

    expect($student->fresh()->unreadNotifications)->toHaveCount(0);
    expect($student->fresh()->readNotifications)->toHaveCount(2);
});

it('allows no-email users to receive database notifications', function () {
    /** @var \App\Models\User $student */
    $student = User::factory()->create([
        'name' => 'Email Complete Student',
        'university_number' => 'NOTIF-STU-WITH-EMAIL',
    ]);
    $student->addRole('student');

    $supervisorUser = User::factory()->create([
        'name' => 'No Email Supervisor',
        'university_number' => 'NOTIF-SUP-NO-EMAIL',
        'email' => null,
    ]);
    $supervisorUser->addRole('supervisor');

    $supervisor = Supervisor::create([
        'name' => $supervisorUser->name,
        'email' => null,
        'user_id' => $supervisorUser->id,
    ]);

    $project = createNotificationProject($supervisor);

    $this->actingAs($student)
        ->post('/RequstAdd', [
            'project_id' => $project->id,
            'count' => 1,
            'oneid' => $student->university_number,
        ])
        ->assertSessionHas('success');

    $supervisorUser->refresh();
    expect($supervisorUser->email)->toBeNull();
    expect($supervisorUser->notifications)->toHaveCount(1);
    expect($supervisorUser->notifications->first()->data['type'])->toBe('request_submitted');
});

it('does not notify when workflow action is rejected by guards', function () {
    [$supervisorUser, $supervisor, $student] = createNotificationFixture();
    $project = createNotificationProject($supervisor, taken: true);
    $projectRequest = createNotificationProjectRequest($project, $student);

    $this->actingAs($supervisorUser)
        ->post('/acceptrequest', ['request' => $projectRequest->id])
        ->assertSessionHas('error');

    $student->refresh();
    $supervisorUser->refresh();
    expect($student->notifications)->toHaveCount(0);
    expect($supervisorUser->notifications)->toHaveCount(0);
});

it('does not create duplicate notifications for already processed requests', function () {
    [$supervisorUser, $supervisor, $student] = createNotificationFixture();
    $project = createNotificationProject($supervisor);
    $projectRequest = createNotificationProjectRequest($project, $student, accepted: true);

    $this->actingAs($supervisorUser)
        ->post('/acceptrequest', ['request' => $projectRequest->id])
        ->assertSessionHas('error');

    $student->refresh();
    expect($student->notifications)->toHaveCount(0);
});

function createNotificationFixture(string $suffix = 'main'): array
{
    $student = User::factory()->create([
        'name' => "Notification Student {$suffix}",
        'university_number' => "NOTIF-STU-{$suffix}",
    ]);
    $student->addRole('student');

    $supervisorUser = User::factory()->create([
        'name' => "Notification Supervisor {$suffix}",
        'university_number' => "NOTIF-SUP-{$suffix}",
    ]);
    $supervisorUser->addRole('supervisor');

    $supervisor = Supervisor::create([
        'name' => $supervisorUser->name,
        'email' => $supervisorUser->email,
        'user_id' => $supervisorUser->id,
    ]);

    return [$supervisorUser, $supervisor, $student];
}

function createNotificationProject(Supervisor $supervisor, bool $taken = false): UniProject
{
    return UniProject::create([
        'name' => 'Notification Test Project',
        'description' => 'Project used for internal notification tests.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => $taken,
    ]);
}

function createNotificationProjectRequest(UniProject $project, User $student, bool $accepted = false): Projectrequest
{
    $projectRequest = Projectrequest::create([
        'project_id' => $project->id,
        'requested_by_user_id' => $student->id,
        'count' => 1,
        'accepted' => $accepted,
        'rejected' => false,
    ]);

    ProjectRequestMember::create([
        'project_request_id' => $projectRequest->id,
        'user_id' => $student->id,
        'position' => 1,
    ]);

    return $projectRequest;
}

function createNotificationIdea(Supervisor $supervisor, User $student): Idea
{
    $idea = Idea::create([
        'projectname' => 'Notification Idea',
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

function createNotificationSubmissionFixture(): array
{
    [$supervisorUser, $supervisor, $student] = createNotificationFixture();

    $project = UniProject::create([
        'name' => 'Notification Submission Project',
        'description' => 'Project used for submission notification tests.',
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

    return [$supervisorUser, $supervisor, $student, $project];
}
