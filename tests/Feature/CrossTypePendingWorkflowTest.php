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

it('blocks idea submission when the team already has a pending project request', function () {
    [$supervisorUser, $supervisor, $student] = createCrossTypeFixture();
    $project = createCrossTypeProject($supervisor);
    createCrossTypeProjectRequest($project, $student);

    $this->actingAs($student)
        ->post('/RequstIdea', [
            'projectname' => 'Blocked By Pending Request',
            'count' => 1,
            'supervisor_id' => $supervisor->id,
            'oneid' => $student->university_number,
        ])
        ->assertSessionHas('faild2', 'One or more team members already has a pending request or idea.');

    expect(Idea::count())->toBe(0);
});

it('blocks project request submission when the team already has a pending idea', function () {
    [$supervisorUser, $supervisor, $student] = createCrossTypeFixture();
    $project = createCrossTypeProject($supervisor);
    createCrossTypeIdea($supervisor, $student, 'Blocking Pending Idea');

    $this->actingAs($student)
        ->post('/RequstAdd', [
            'project_id' => $project->id,
            'count' => 1,
            'oneid' => $student->university_number,
        ])
        ->assertSessionHas('faild2', 'One or more team members already has a pending request or idea.');

    expect(Projectrequest::count())->toBe(0);
});

it('blocks duplicate pending ideas for the same team', function () {
    [$supervisorUser, $supervisor, $student] = createCrossTypeFixture();
    createCrossTypeIdea($supervisor, $student, 'First Pending Idea');

    $this->actingAs($student)
        ->post('/RequstIdea', [
            'projectname' => 'Second Pending Idea',
            'count' => 1,
            'supervisor_id' => $supervisor->id,
            'oneid' => $student->university_number,
        ])
        ->assertSessionHas('faild2', 'One or more team members already has a pending request or idea.');

    expect(Idea::count())->toBe(1);
});

it('still blocks duplicate pending project requests for the same team', function () {
    [$supervisorUser, $supervisor, $student] = createCrossTypeFixture();
    $firstProject = createCrossTypeProject($supervisor, 'First Available Project');
    $secondProject = createCrossTypeProject($supervisor, 'Second Available Project');

    createCrossTypeProjectRequest($firstProject, $student);

    $this->actingAs($student)
        ->post('/RequstAdd', [
            'project_id' => $secondProject->id,
            'count' => 1,
            'oneid' => $student->university_number,
        ])
        ->assertSessionHas('faild2', 'One or more team members already has a pending request or idea.');

    expect(Projectrequest::count())->toBe(1);
});

function createCrossTypeFixture(string $suffix = 'main'): array
{
    $student = User::factory()->create([
        'name' => "Cross Type Student {$suffix}",
        'university_number' => "CROSS-STU-{$suffix}",
    ]);
    $student->addRole('student');

    $supervisorUser = User::factory()->create([
        'name' => "Cross Type Supervisor {$suffix}",
        'university_number' => "CROSS-SUP-{$suffix}",
    ]);
    $supervisorUser->addRole('supervisor');

    $supervisor = Supervisor::create([
        'name' => $supervisorUser->name,
        'email' => $supervisorUser->email,
        'user_id' => $supervisorUser->id,
    ]);

    return [$supervisorUser, $supervisor, $student];
}

function createCrossTypeProject(Supervisor $supervisor, string $name = 'Cross Type Project'): UniProject
{
    return UniProject::create([
        'name' => $name,
        'description' => 'Project used for cross-type pending workflow tests.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => false,
    ]);
}

function createCrossTypeProjectRequest(UniProject $project, User $student): Projectrequest
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

function createCrossTypeIdea(Supervisor $supervisor, User $student, string $title): Idea
{
    $idea = Idea::create([
        'projectname' => $title,
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
