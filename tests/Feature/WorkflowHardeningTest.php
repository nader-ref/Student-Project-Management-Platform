<?php

use App\Models\Idea;
use App\Models\IdeaMember;
use App\Models\Projectrequest;
use App\Models\ProjectRequestMember;
use App\Models\Supervisor;
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

it('prevents supervisors from accepting requests for taken projects', function () {
    [$supervisorUser, $supervisor, $student] = createWorkflowSupervisorFixture();
    $project = createAvailableProject($supervisor, taken: true);

    $projectRequest = createProjectRequest($project, $student);

    $this->actingAs($supervisorUser)
        ->post('/acceptrequest', ['request' => $projectRequest->id])
        ->assertSessionHas('error');

    expect($projectRequest->fresh()->accepted)->toBeFalsy();
    expect($project->members()->count())->toBe(0);
});

it('prevents supervisors from accepting requests when a member is already enrolled elsewhere', function () {
    [$supervisorUser, $supervisor, $student] = createWorkflowSupervisorFixture();
    $otherSupervisor = Supervisor::create([
        'name' => 'Other Supervisor',
        'email' => 'other.supervisor@test.local',
        'user_id' => User::factory()->create()->id,
    ]);

    $enrolledProject = UniProject::create([
        'name' => 'Existing Enrolled Project',
        'description' => 'Student is already on this team.',
        'supervisor_id' => $otherSupervisor->id,
        'department' => 'software',
        'taken' => true,
    ]);
    $enrolledProject->members()->create(['user_id' => $student->id, 'position' => 1]);

    $availableProject = createAvailableProject($supervisor);
    $projectRequest = createProjectRequest($availableProject, $student);

    $this->actingAs($supervisorUser)
        ->post('/acceptrequest', ['request' => $projectRequest->id])
        ->assertSessionHas('error');

    expect($projectRequest->fresh()->accepted)->toBeFalsy();
    expect($availableProject->members()->count())->toBe(0);
});

it('prevents supervisors from accepting already processed project requests', function () {
    [$supervisorUser, $supervisor, $student] = createWorkflowSupervisorFixture();
    $project = createAvailableProject($supervisor);
    $projectRequest = createProjectRequest($project, $student, accepted: true);

    $this->actingAs($supervisorUser)
        ->post('/acceptrequest', ['request' => $projectRequest->id])
        ->assertSessionHas('error');
});

it('prevents supervisors from accepting ideas when a member is already enrolled elsewhere', function () {
    [$supervisorUser, $supervisor, $student] = createWorkflowSupervisorFixture();
    $otherSupervisor = Supervisor::create([
        'name' => 'Other Supervisor',
        'email' => 'other.idea.supervisor@test.local',
        'user_id' => User::factory()->create()->id,
    ]);

    $enrolledProject = UniProject::create([
        'name' => 'Existing Idea Conflict Project',
        'description' => 'Student is already on this team.',
        'supervisor_id' => $otherSupervisor->id,
        'department' => 'software',
        'taken' => true,
    ]);
    $enrolledProject->members()->create(['user_id' => $student->id, 'position' => 1]);

    $idea = createIdea($supervisor, $student, 'Blocked Idea Acceptance');

    $this->actingAs($supervisorUser)
        ->post('/acceptidea', ['idea' => $idea->id])
        ->assertSessionHas('error');

    expect($idea->fresh()->accepted)->toBeFalsy();
    expect(UniProject::where('name', 'Blocked Idea Acceptance')->exists())->toBeFalse();
});

it('prevents students from creating multiple pending project requests', function () {
    [$supervisorUser, $supervisor, $student] = createWorkflowSupervisorFixture();
    $firstProject = createAvailableProject($supervisor, 'First Available Project');
    $secondProject = createAvailableProject($supervisor, 'Second Available Project');

    createProjectRequest($firstProject, $student);

    $this->actingAs($student)
        ->post('/RequstAdd', [
            'project_id' => $secondProject->id,
            'count' => 1,
            'oneid' => $student->university_number,
        ])
        ->assertSessionHas('faild2');

    expect(Projectrequest::count())->toBe(1);
});

it('prevents enrolled students from submitting new project requests', function () {
    [$supervisorUser, $supervisor, $student] = createWorkflowSupervisorFixture();
    $enrolledProject = UniProject::create([
        'name' => 'Current Team Project',
        'description' => 'Student is already enrolled.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => true,
    ]);
    $enrolledProject->members()->create(['user_id' => $student->id, 'position' => 1]);

    $availableProject = createAvailableProject($supervisor, 'Another Available Project');

    $this->actingAs($student)
        ->post('/RequstAdd', [
            'project_id' => $availableProject->id,
            'count' => 1,
            'oneid' => $student->university_number,
        ])
        ->assertSessionHas('faild2');

    expect(Projectrequest::count())->toBe(0);
});

it('prevents supervisors from accepting another supervisors project request', function () {
    [$supervisorUser, , $student] = createWorkflowSupervisorFixture();
    [$otherSupervisorUser, $otherSupervisor] = createWorkflowSupervisorFixture('other');
    $project = createAvailableProject($otherSupervisor);
    $projectRequest = createProjectRequest($project, $student);

    $this->actingAs($supervisorUser)
        ->post('/acceptrequest', ['request' => $projectRequest->id])
        ->assertSessionHas('error');

    expect($projectRequest->fresh()->accepted)->toBeFalsy();
});

it('prevents supervisors from accepting another supervisors idea', function () {
    [$supervisorUser, , $student] = createWorkflowSupervisorFixture();
    [, $otherSupervisor] = createWorkflowSupervisorFixture('other');
    $idea = createIdea($otherSupervisor, $student, 'Foreign Supervisor Idea');

    $this->actingAs($supervisorUser)
        ->post('/acceptidea', ['idea' => $idea->id])
        ->assertSessionHas('error');

    expect($idea->fresh()->accepted)->toBeFalsy();
    expect(UniProject::where('name', 'Foreign Supervisor Idea')->exists())->toBeFalse();
});

function createWorkflowSupervisorFixture(string $suffix = 'main'): array
{
    $student = User::factory()->create([
        'name' => "Workflow Student {$suffix}",
        'university_number' => "WF-STU-{$suffix}",
    ]);
    $student->addRole('student');

    $supervisorUser = User::factory()->create([
        'name' => "Workflow Supervisor {$suffix}",
        'university_number' => "WF-SUP-{$suffix}",
    ]);
    $supervisorUser->addRole('supervisor');

    $supervisor = Supervisor::create([
        'name' => $supervisorUser->name,
        'email' => $supervisorUser->email,
        'user_id' => $supervisorUser->id,
    ]);

    return [$supervisorUser, $supervisor, $student];
}

function createAvailableProject(Supervisor $supervisor, string $name = 'Workflow Available Project', bool $taken = false): UniProject
{
    return UniProject::create([
        'name' => $name,
        'description' => 'Project used for workflow hardening tests.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => $taken,
    ]);
}

function createProjectRequest(UniProject $project, User $student, bool $accepted = false): Projectrequest
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

function createIdea(Supervisor $supervisor, User $student, string $projectName): Idea
{
    $idea = Idea::create([
        'projectname' => $projectName,
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
