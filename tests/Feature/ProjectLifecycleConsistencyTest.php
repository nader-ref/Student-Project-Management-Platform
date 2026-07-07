<?php

use App\Models\Idea;
use App\Models\IdeaMember;
use App\Models\Projectrequest;
use App\Models\ProjectRequestMember;
use App\Models\Supervisor;
use App\Models\UniProject;
use App\Models\User;
use App\Services\StudentEnrollmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Laratrust\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    foreach (['student', 'supervisor', 'admin'] as $roleName) {
        Role::firstOrCreate(
            ['name' => $roleName],
            ['display_name' => ucfirst($roleName), 'description' => $roleName.' role'],
        );
    }
});

it('accepting a project request creates consistent assignment state', function () {
    [$supervisorUser, $supervisor, $student] = createLifecycleSupervisorFixture();
    $project = createLifecycleAvailableProject($supervisor);
    $projectRequest = createLifecycleProjectRequest($project, $student);

    $this->actingAs($supervisorUser)
        ->post('/acceptrequest', [
            'project' => $project->id,
            'request' => $projectRequest->id,
        ])
        ->assertSessionHas('success');

    $project->refresh();

    expect($project->taken)->toBeTruthy();
    expect($project->student_count)->toBe(1);
    expect($project->members()->count())->toBe(1);
    expect($project->isAssigned())->toBeTrue();
    expect($project->isLifecycleConsistent())->toBeTrue();
    expect($project->lifecycleLabel())->toBe('Assigned');
});

it('accepting an idea creates consistent assignment state', function () {
    [$supervisorUser, $supervisor, $student] = createLifecycleSupervisorFixture();
    $idea = createLifecycleIdea($supervisor, $student, 'Lifecycle Accepted Idea');

    $this->actingAs($supervisorUser)
        ->withSession(['id' => $supervisor->id])
        ->post('/acceptidea', ['idea' => $idea->id])
        ->assertSessionHas('success');

    $project = UniProject::where('name', 'Lifecycle Accepted Idea')->first();

    expect($project)->not->toBeNull();
    expect($project->taken)->toBeTruthy();
    expect($project->student_count)->toBe(1);
    expect($project->members()->where('user_id', $student->id)->exists())->toBeTrue();
    expect($project->isAssigned())->toBeTrue();
    expect($project->lifecycleLabel())->toBe('Assigned');
});

it('manual supervisor create with members results in taken true', function () {
    [$supervisorUser, , $student] = createLifecycleSupervisorFixture();

    $this->actingAs($supervisorUser)
        ->post('/addproject', array_merge(validLifecycleProjectPayload('Assigned Manual Project'), [
            'taken' => 'Yes',
            'student_one_id' => $student->university_number,
        ]))
        ->assertSessionHas('success');

    $project = UniProject::where('name', 'Assigned Manual Project')->first();

    expect($project->taken)->toBeTruthy();
    expect($project->student_count)->toBe(1);
    expect($project->members()->count())->toBe(1);
    expect($project->isAssigned())->toBeTrue();
});

it('manual supervisor create with no members results in taken false', function () {
    [$supervisorUser] = createLifecycleSupervisorFixture();

    $this->actingAs($supervisorUser)
        ->post('/addproject', array_merge(validLifecycleProjectPayload('Available Manual Project'), [
            'taken' => 'No',
        ]))
        ->assertSessionHas('success');

    $project = UniProject::where('name', 'Available Manual Project')->first();

    expect($project->taken)->toBeFalsy();
    expect($project->members()->count())->toBe(0);
    expect($project->isAvailable())->toBeTrue();
    expect($project->lifecycleLabel())->toBe('Available');
});

it('rejects manual supervisor create when taken flag disagrees with members', function () {
    [$supervisorUser, , $student] = createLifecycleSupervisorFixture();

    $this->actingAs($supervisorUser)
        ->post('/addproject', array_merge(validLifecycleProjectPayload('Mismatch Manual Project'), [
            'taken' => 'No',
            'student_one_id' => $student->university_number,
        ]))
        ->assertSessionHas('error');

    expect(UniProject::where('name', 'Mismatch Manual Project')->exists())->toBeFalse();
});

it('rejects manual supervisor update when taken false while keeping members', function () {
    [$supervisorUser, $supervisor, $student] = createLifecycleSupervisorFixture();
    $project = UniProject::create(array_merge([
        'name' => 'Lifecycle Update Target',
        'description' => 'Project with an existing member.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => true,
        'student_count' => 1,
    ], validLifecycleProjectDates()));
    $project->members()->create(['user_id' => $student->id, 'position' => 1]);

    $this->actingAs($supervisorUser)
        ->post('/updateproject', array_merge(validLifecycleProjectPayload('Lifecycle Update Target'), [
            'project_id' => $project->id,
            'taken' => 'No',
            'student_one_id' => $student->university_number,
        ]))
        ->assertSessionHas('error');

    $project->refresh();

    expect($project->taken)->toBeTruthy();
    expect($project->members()->count())->toBe(1);
});

it('still resolves student enrolled mode from taken and project members', function () {
    [$supervisorUser, $supervisor, $student] = createLifecycleSupervisorFixture();
    $project = UniProject::create([
        'name' => 'Enrolled Lifecycle Project',
        'description' => 'Enrolled project.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => true,
        'student_count' => 1,
    ]);
    $project->members()->create(['user_id' => $student->id, 'position' => 1]);

    $enrollment = StudentEnrollmentService::resolve($student);

    expect($enrollment['mode'])->toBe(StudentEnrollmentService::MODE_ENROLLED);
    expect($enrollment['project']->id)->toBe($project->id);
});

it('admin projects page uses lifecycle labels instead of the status column', function () {
    $admin = User::factory()->create();
    $admin->addRole('admin');

    $supervisor = Supervisor::create([
        'name' => 'Dr. Lifecycle Admin',
        'email' => 'lifecycle.admin@test.local',
        'user_id' => User::factory()->create()->id,
    ]);

    $student = User::factory()->create(['university_number' => 'LC-STU-001']);
    $student->addRole('student');

    $assigned = UniProject::create([
        'name' => 'Assigned Lifecycle Project',
        'description' => 'Assigned project.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => true,
        'student_count' => 1,
        'status' => 'in_progress',
    ]);
    $assigned->members()->create(['user_id' => $student->id, 'position' => 1]);

    UniProject::create([
        'name' => 'Available Lifecycle Project',
        'description' => 'Available project.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => false,
        'student_count' => 0,
        'status' => 'available',
    ]);

    $response = $this->actingAs($admin)->get('/admin/projects');

    $response->assertOk();
    $response->assertSee('Assigned');
    $response->assertSee('Available');
    $response->assertDontSee('in_progress');
});

it('admin dashboard counts still rely on taken for available and taken projects', function () {
    $admin = User::factory()->create();
    $admin->addRole('admin');

    $supervisor = Supervisor::create([
        'name' => 'Dr. Lifecycle Reports',
        'email' => 'lifecycle.reports@test.local',
        'user_id' => User::factory()->create()->id,
    ]);

    UniProject::create([
        'name' => 'Available Count Project',
        'description' => 'Available.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => false,
    ]);

    $taken = UniProject::create([
        'name' => 'Taken Count Project',
        'description' => 'Taken.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => true,
        'student_count' => 1,
    ]);
    $student = User::factory()->create();
    $student->addRole('student');
    $taken->members()->create(['user_id' => $student->id, 'position' => 1]);

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertOk();
    $response->assertSee('Available projects');
    $response->assertSee('Taken projects');
    $response->assertSee('1', false);
});

it('does not let the project status column override progress current phase', function () {
    $project = UniProject::make([
        'taken' => true,
        'status' => 'in_progress',
    ]);

    $progress = StudentEnrollmentService::computeProgress(
        $project,
        collect([
            ['key' => 'seminar_1', 'label' => 'Seminar 1', 'is_past' => false, 'formatted' => 'Jan 1'],
        ]),
        new Collection,
    );

    expect($progress['current_phase'])->toBe('Seminar 1');
    expect($progress['current_phase'])->not->toBe('In Progress');
});

it('derives lifecycle label from taken and members not status', function () {
    $project = UniProject::make([
        'taken' => true,
        'status' => 'available',
    ]);

    expect($project->memberCount())->toBe(0);
    expect($project->lifecycleLabel())->toBe('Available');
});

function createLifecycleSupervisorFixture(): array
{
    $student = User::factory()->create([
        'name' => 'Lifecycle Student',
        'university_number' => 'LC-STU-MAIN',
    ]);
    $student->addRole('student');

    $supervisorUser = User::factory()->create([
        'name' => 'Lifecycle Supervisor',
        'university_number' => 'LC-SUP-MAIN',
    ]);
    $supervisorUser->addRole('supervisor');

    $supervisor = Supervisor::create([
        'name' => $supervisorUser->name,
        'email' => $supervisorUser->email,
        'user_id' => $supervisorUser->id,
    ]);

    return [$supervisorUser, $supervisor, $student];
}

function createLifecycleAvailableProject(Supervisor $supervisor, string $name = 'Lifecycle Available Project'): UniProject
{
    return UniProject::create([
        'name' => $name,
        'description' => 'Available project for lifecycle tests.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => false,
    ]);
}

function createLifecycleProjectRequest(UniProject $project, User $student): Projectrequest
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

function createLifecycleIdea(Supervisor $supervisor, User $student, string $projectName): Idea
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

function validLifecycleProjectDates(): array
{
    return [
        'seminar_1' => now()->addWeeks(2)->toDateString(),
        'seminar_2' => now()->addWeeks(6)->toDateString(),
        'seminar_3' => now()->addWeeks(10)->toDateString(),
        'final' => now()->addWeeks(14)->toDateString(),
    ];
}

function validLifecycleProjectPayload(string $projectName): array
{
    return array_merge([
        'project_name' => $projectName,
        'description' => 'Project used for lifecycle consistency tests.',
        'department' => 'software',
        'seminar1_date' => now()->addWeeks(2)->toDateString(),
        'seminar2_date' => now()->addWeeks(6)->toDateString(),
        'seminar3_date' => now()->addWeeks(10)->toDateString(),
        'final_date' => now()->addWeeks(14)->toDateString(),
    ], []);
}
