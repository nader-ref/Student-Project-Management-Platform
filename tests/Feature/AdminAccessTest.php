<?php

use App\Models\ProjectSubmission;
use App\Models\Supervisor;
use App\Models\UniProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laratrust\Models\Role;

uses(RefreshDatabase::class);

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

it('redirects students away from the admin dashboard', function () {
    $student = User::factory()->create();
    $student->addRole('student');

    $this->actingAs($student)
        ->get('/admin')
        ->assertRedirect('/StudentDashboard');
});

it('redirects supervisors away from the admin dashboard', function () {
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
    $response->assertSee($student->name);
    $response->assertSee($supervisor->name);
});

it('shows a read-only users page to admins', function () {
    [$admin, $student, $supervisor] = createAdminFixture();

    $response = $this->actingAs($admin)->get('/admin/users');

    $response->assertOk();
    $response->assertSee('Read-only account overview.');
    $response->assertSee($admin->name);
    $response->assertSee($student->university_number);
    $response->assertSee($supervisor->university_number);
    $response->assertSee('Account status');
    $response->assertSee('Active');
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

    ProjectSubmission::create([
        'project_id' => $project->id,
        'submitted_by_user_id' => $student->id,
        'student_email' => $student->email,
        'student_name' => $student->name,
        'milestone' => 'seminar_1',
        'title' => 'Initial Project Proposal',
        'file_path' => 'submissions/development-test/initial-proposal.txt',
        'original_filename' => 'initial-proposal.txt',
        'status' => 'submitted',
    ]);

    return [$admin, $student, $supervisorUser];
}
