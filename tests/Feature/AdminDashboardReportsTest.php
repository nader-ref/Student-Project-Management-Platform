<?php

use App\Models\idea;
use App\Models\projectrequest;
use App\Models\ProjectSubmission;
use App\Models\Supervisor;
use App\Models\UniProject;
use App\Models\User;
use Laratrust\Models\Role;

beforeEach(function () {
    foreach (['admin', 'supervisor', 'student'] as $roleName) {
        Role::firstOrCreate(
            ['name' => $roleName],
            ['display_name' => ucfirst($roleName), 'description' => $roleName.' role'],
        );
    }
});

function createDashboardReportsFixture(): array
{
    $admin = User::factory()->create([
        'name' => 'Reports Admin',
        'university_number' => 'ADM-REPORTS-001',
        'email' => 'admin.reports@test.local',
    ]);
    $admin->addRole('admin');

    $supervisorUser = User::factory()->create([
        'name' => 'Dr. Report Supervisor',
        'university_number' => 'SUP-REPORTS-001',
        'email' => 'supervisor.reports@test.local',
    ]);
    $supervisorUser->addRole('supervisor');

    $supervisor = Supervisor::create([
        'name' => $supervisorUser->name,
        'email' => $supervisorUser->email,
        'user_id' => $supervisorUser->id,
    ]);

    $idleSupervisorUser = User::factory()->create([
        'name' => 'Dr. Idle Supervisor',
        'university_number' => 'SUP-REPORTS-IDLE',
        'email' => 'idle.supervisor@test.local',
    ]);
    $idleSupervisorUser->addRole('supervisor');

    $idleSupervisor = Supervisor::create([
        'name' => $idleSupervisorUser->name,
        'email' => $idleSupervisorUser->email,
        'user_id' => $idleSupervisorUser->id,
    ]);

    $student = User::factory()->create([
        'name' => 'Reports Student',
        'university_number' => 'STU-REPORTS-001',
        'email' => 'student.reports@test.local',
    ]);
    $student->addRole('student');

    UniProject::create([
        'name' => 'Available Project Alpha',
        'description' => 'Available project for reports.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => false,
        'student_count' => 0,
    ]);

    UniProject::create([
        'name' => 'Available Project Beta',
        'description' => 'Second available project.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => false,
        'student_count' => 0,
    ]);

    $takenProject = UniProject::create([
        'name' => 'Taken Project Gamma',
        'description' => 'Taken project for reports.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => true,
        'student_count' => 1,
    ]);

    $takenProject->members()->create(['user_id' => $student->id, 'position' => 1]);

    projectrequest::create([
        'project_id' => $takenProject->id,
        'requested_by_user_id' => $student->id,
        'count' => 1,
        'accepted' => false,
        'rejected' => false,
    ]);

    projectrequest::create([
        'project_id' => $takenProject->id,
        'requested_by_user_id' => $student->id,
        'count' => 1,
        'accepted' => true,
        'rejected' => false,
    ]);

    projectrequest::create([
        'project_id' => $takenProject->id,
        'requested_by_user_id' => $student->id,
        'count' => 1,
        'accepted' => false,
        'rejected' => true,
    ]);

    idea::create([
        'supervisor_id' => $supervisor->id,
        'requested_by_user_id' => $student->id,
        'projectname' => 'Pending Idea',
        'count' => 1,
        'accepted' => false,
        'rejected' => false,
    ]);

    idea::create([
        'supervisor_id' => $supervisor->id,
        'requested_by_user_id' => $student->id,
        'projectname' => 'Accepted Idea',
        'count' => 1,
        'accepted' => true,
        'rejected' => false,
    ]);

    idea::create([
        'supervisor_id' => $supervisor->id,
        'requested_by_user_id' => $student->id,
        'projectname' => 'Rejected Idea',
        'count' => 1,
        'accepted' => false,
        'rejected' => true,
    ]);

    ProjectSubmission::create([
        'project_id' => $takenProject->id,
        'submitted_by_user_id' => $student->id,
        'milestone' => 'seminar_1',
        'title' => 'Submitted Report',
        'file_path' => 'submissions/reports/submitted.txt',
        'original_filename' => 'submitted.txt',
        'status' => 'submitted',
    ]);

    ProjectSubmission::create([
        'project_id' => $takenProject->id,
        'submitted_by_user_id' => $student->id,
        'milestone' => 'seminar_2',
        'title' => 'Approved Report',
        'file_path' => 'submissions/reports/approved.txt',
        'original_filename' => 'approved.txt',
        'status' => 'approved',
    ]);

    ProjectSubmission::create([
        'project_id' => $takenProject->id,
        'submitted_by_user_id' => $student->id,
        'milestone' => 'seminar_3',
        'title' => 'Revision Report',
        'file_path' => 'submissions/reports/revision.txt',
        'original_filename' => 'revision.txt',
        'status' => 'needs_revision',
    ]);

    return [$admin, $supervisor, $idleSupervisor, $student];
}

it('shows available and taken project counts on the dashboard', function () {
    [$admin] = createDashboardReportsFixture();

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertOk();
    $response->assertSee('Projects', false);
    $response->assertSee('Available projects');
    $response->assertSee('Assigned projects');
    $response->assertSee('2', false);
    $response->assertSee('1', false);
});

it('shows request workflow counts on the dashboard', function () {
    [$admin] = createDashboardReportsFixture();

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertOk();
    $response->assertSee('Workflow', false);
    $response->assertSee('Requests', false);
    $response->assertSee('Pending', false);
    $response->assertSee('Accepted', false);
    $response->assertSee('Rejected', false);
});

it('shows idea workflow counts on the dashboard', function () {
    [$admin] = createDashboardReportsFixture();

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertOk();
    $response->assertSee('Ideas', false);
});

it('shows submission status counts on the dashboard', function () {
    [$admin] = createDashboardReportsFixture();

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertOk();
    $response->assertSee('Submissions', false);
    $response->assertSee('Pending Review');
    $response->assertSee('Approved');
    $response->assertSee('Revision Required');
});

it('shows projects per supervisor summary on the dashboard', function () {
    [$admin, $supervisor, $idleSupervisor] = createDashboardReportsFixture();

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertOk();
    $response->assertSee('Supervisor workload', false);
    $response->assertSee($supervisor->name);
    $response->assertSee($idleSupervisor->name);
    $response->assertSee('Total projects', false);
});

it('keeps existing dashboard overview metrics visible', function () {
    [$admin] = createDashboardReportsFixture();

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertOk();
    $response->assertSee('Overview', false);
    $response->assertSee('Total users');
    $response->assertSee('Total students');
    $response->assertSee('Total supervisors');
    $response->assertSee('Total projects');
    $response->assertSee('Total submissions');
    $response->assertSee('Pending requests');
    $response->assertSee('Pending ideas');
    $response->assertSee('Pending email');
    $response->assertSee('Latest registered users');
});

it('redirects students away from the dashboard reports page', function () {
    [$admin] = createDashboardReportsFixture();

    /** @var User $student */
    $student = User::factory()->create();
    $student->addRole('student');

    $this->actingAs($student)
        ->get(route('admin.dashboard'))
        ->assertRedirect('/StudentDashboard');
});
