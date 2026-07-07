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

it('redirects guests away from the admin dashboard', function () {
    $this->get('/admin')->assertRedirect(route('login'));
});

it('redirects guests away from admin oversight routes', function (string $path) {
    $this->get($path)->assertRedirect(route('login'));
})->with([
    'projects' => '/admin/projects',
    'requests' => '/admin/requests',
    'ideas' => '/admin/ideas',
    'submissions' => '/admin/submissions',
    'activity' => '/admin/activity',
]);

it('redirects students away from the admin dashboard', function () {
    /** @var \App\Models\User $student */
    $student = User::factory()->create();
    $student->addRole('student');

    $this->actingAs($student)
        ->get('/admin')
        ->assertRedirect('/StudentDashboard');
});

it('redirects students away from admin oversight routes', function (string $path) {
    /** @var \App\Models\User $student */
    $student = User::factory()->create();
    $student->addRole('student');

    $this->actingAs($student)
        ->get($path)
        ->assertRedirect('/StudentDashboard');
})->with([
    'projects' => '/admin/projects',
    'requests' => '/admin/requests',
    'ideas' => '/admin/ideas',
    'submissions' => '/admin/submissions',
    'activity' => '/admin/activity',
]);

it('redirects supervisors away from the admin dashboard', function () {
    /** @var \App\Models\User $supervisorUser */
    $supervisorUser = User::factory()->create();
    $supervisorUser->addRole('supervisor');

    Supervisor::create([
        'name' => $supervisorUser->name,
        'email' => $supervisorUser->email,
        'user_id' => $supervisorUser->id,
    ]);

    $this->actingAs($supervisorUser)
        ->get('/admin')
        ->assertRedirect('/supervisorDashboard');
});

it('redirects supervisors away from admin oversight routes', function (string $path) {
    /** @var \App\Models\User $supervisorUser */
    $supervisorUser = User::factory()->create();
    $supervisorUser->addRole('supervisor');

    Supervisor::create([
        'name' => $supervisorUser->name,
        'email' => $supervisorUser->email,
        'user_id' => $supervisorUser->id,
    ]);

    $this->actingAs($supervisorUser)
        ->get($path)
        ->assertRedirect('/supervisorDashboard');
})->with([
    'projects' => '/admin/projects',
    'requests' => '/admin/requests',
    'ideas' => '/admin/ideas',
    'submissions' => '/admin/submissions',
    'activity' => '/admin/activity',
]);

it('shows the admin dashboard metrics to admins', function () {
    [$admin, $student, $supervisor] = createAdminFixture();

    $response = $this->actingAs($admin)->get('/admin');

    $response->assertOk();
    $response->assertSee('Admin Dashboard');
    $response->assertSee('Total users');
    $response->assertSee('Total students');
    $response->assertSee('Total supervisors');
    $response->assertSee('Total projects');
    $response->assertSee('Total submissions');
    $response->assertSee('Pending requests');
    $response->assertSee('Pending ideas');
    $response->assertSee('Pending email');
    $response->assertSee('Overview', false);
    $response->assertSee('Projects', false);
    $response->assertSee('Workflow', false);
    $response->assertSee('Submissions', false);
    $response->assertSee('Supervisor workload', false);
    $response->assertSee($student->name);
    $response->assertSee($supervisor->name);
});

it('shows a read-only users page to admins', function () {
    [$admin, $student, $supervisor] = createAdminFixture();

    $response = $this->actingAs($admin)->get('/admin/users');

    $response->assertOk();
    $response->assertSee('Account overview with lifecycle actions.');
    $response->assertSee($admin->name);
    $response->assertSee($student->university_number);
    $response->assertSee($supervisor->university_number);
    $response->assertSee('Email', false);
    $response->assertSee('Email status', false);
    $response->assertSee('Account status');
    $response->assertSee('Active');
    $response->assertSee($student->email);
    $response->assertSee('Complete');
});

it('shows a read-only projects page to admins', function () {
    [$admin, $student] = createAdminFixture();

    $response = $this->actingAs($admin)->get('/admin/projects');

    $response->assertOk();
    $response->assertSee('Read-only project overview.');
    $response->assertSee('AI-Assisted Graduation Project Tracker');
    $response->assertSee('Dr. Lina Haddad');
    $response->assertSee('Assigned');
    $response->assertSee('1');
});

it('shows a read-only requests page to admins', function () {
    [$admin] = createAdminFixture();

    $response = $this->actingAs($admin)->get('/admin/requests');

    $response->assertOk();
    $response->assertSee('Read-only request overview.');
    $response->assertSee('REQ-0001');
    $response->assertSee('Pending');
});

it('shows a read-only ideas page to admins', function () {
    [$admin] = createAdminFixture();

    $response = $this->actingAs($admin)->get('/admin/ideas');

    $response->assertOk();
    $response->assertSee('Read-only idea overview.');
    $response->assertSee('Smart Campus Navigator');
    $response->assertSee('Pending');
});

it('shows a read-only submissions page to admins', function () {
    [$admin, $student] = createAdminFixture();

    $response = $this->actingAs($admin)->get('/admin/submissions');

    $response->assertOk();
    $response->assertSee('Read-only submission overview.');
    $response->assertSee('Initial Project Proposal');
    $response->assertSee('Seminar 1');
    $response->assertSee('Pending Review');
    $response->assertSee($student->name);
});

function createAdminFixture(): array
{
    $admin = User::factory()->create([
        'name' => 'System Administrator',
        'university_number' => 'ADM-0001',
        'email' => 'admin@test.local',
    ]);
    $admin->addRole('admin');

    $student = User::factory()->create([
        'name' => 'Ahmad Al Ali',
        'university_number' => 'STU-2026-001',
        'email' => 'ahmad@test.local',
    ]);
    $student->addRole('student');

    $supervisorUser = User::factory()->create([
        'name' => 'Dr. Lina Haddad',
        'university_number' => 'SUP-2026-001',
        'email' => 'lina.haddad@test.local',
    ]);
    $supervisorUser->addRole('supervisor');

    $supervisor = Supervisor::create([
        'name' => $supervisorUser->name,
        'email' => $supervisorUser->email,
        'user_id' => $supervisorUser->id,
    ]);

    $project = UniProject::create([
        'name' => 'AI-Assisted Graduation Project Tracker',
        'description' => 'Sample admin dashboard project.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => true,
        'student_count' => 1,
    ]);

    $project->members()->create([
        'user_id' => $student->id,
        'position' => 1,
    ]);

    $pendingRequest = projectrequest::create([
        'project_id' => $project->id,
        'requested_by_user_id' => $student->id,
        'count' => 1,
        'accepted' => false,
        'rejected' => false,
    ]);
    $pendingRequest->members()->create([
        'user_id' => $student->id,
        'position' => 1,
    ]);

    idea::create([
        'supervisor_id' => $supervisor->id,
        'requested_by_user_id' => $student->id,
        'projectname' => 'Smart Campus Navigator',
        'count' => 1,
        'accepted' => false,
        'rejected' => false,
    ])->members()->create([
        'user_id' => $student->id,
        'position' => 1,
    ]);

    ProjectSubmission::create([
        'project_id' => $project->id,
        'submitted_by_user_id' => $student->id,
        'milestone' => 'seminar_1',
        'title' => 'Initial Project Proposal',
        'file_path' => 'submissions/development-test/initial-proposal.txt',
        'original_filename' => 'initial-proposal.txt',
        'status' => 'submitted',
    ]);

    return [$admin, $student, $supervisorUser];
}
