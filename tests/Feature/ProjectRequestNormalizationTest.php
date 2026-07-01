<?php

use App\Models\Projectrequest;
use App\Models\Supervisor;
use App\Models\UniProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

it('creates project requests with relational members', function () {
    [$student, $project] = createRequestFixture();

    $this->actingAs($student)
        ->post('/RequstAdd', [
            'project_id' => $project->id,
            'count' => 1,
            'oneid' => $student->university_number,
        ])
        ->assertSessionHas('success');

    $request = Projectrequest::first();

    expect($request->project_id)->toBe($project->id);
    expect($request->requested_by_user_id)->toBe($student->id);

    $this->assertDatabaseHas('project_request_members', [
        'project_request_id' => $request->id,
        'user_id' => $student->id,
        'position' => 1,
    ]);
});

it('shows only requests where the student is a relational member', function () {
    [$student, $project] = createRequestFixture();
    $otherStudent = User::factory()->create(['university_number' => '2026002']);
    $otherStudent->addRole('student');

    $visibleRequest = Projectrequest::create([
        'project_id' => $project->id,
        'requested_by_user_id' => $student->id,
        'count' => 1,
    ]);
    $visibleRequest->members()->create(['user_id' => $student->id, 'position' => 1]);

    $hiddenRequest = Projectrequest::create([
        'project_id' => $project->id,
        'requested_by_user_id' => $otherStudent->id,
        'count' => 1,
    ]);
    $hiddenRequest->members()->create(['user_id' => $otherStudent->id, 'position' => 1]);

    $response = $this->actingAs($student)->get('/StudentDashboard/acceptance');

    $response->assertOk();
    $response->assertSee('REQ-'.str_pad($visibleRequest->id, 4, '0', STR_PAD_LEFT));
    $response->assertDontSee('REQ-'.str_pad($hiddenRequest->id, 4, '0', STR_PAD_LEFT));
});

it('prevents duplicate request members for the same request and user', function () {
    [$student, $project] = createRequestFixture();

    $request = Projectrequest::create([
        'project_id' => $project->id,
        'requested_by_user_id' => $student->id,
        'count' => 1,
    ]);
    $request->members()->create(['user_id' => $student->id, 'position' => 1]);

    expect(fn () => $request->members()->create([
        'user_id' => $student->id,
        'position' => 2,
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});

it('rejects project requests in a transaction', function () {
    [$student, $project, $supervisorUser] = createRequestFixture();

    $request = Projectrequest::create([
        'project_id' => $project->id,
        'requested_by_user_id' => $student->id,
        'count' => 1,
    ]);
    $request->members()->create(['user_id' => $student->id, 'position' => 1]);

    $this->actingAs($supervisorUser)
        ->withSession(['id' => $project->supervisor_id])
        ->post('/rejectrequest', [
            'request' => $request->id,
            'reason' => 'Team capacity is full.',
        ])
        ->assertSessionHas('success');

    $this->assertDatabaseHas('projectrequests', [
        'id' => $request->id,
        'accepted' => false,
        'rejected' => true,
        'reason' => 'Team capacity is full.',
    ]);
});

function createRequestFixture(): array
{
    $student = User::factory()->create([
        'name' => 'Ahmad Al Ali',
        'university_number' => '2026001',
    ]);
    $student->addRole('student');

    $supervisorUser = User::factory()->create();
    $supervisorUser->addRole('supervisor');

    $supervisor = Supervisor::create([
        'name' => $supervisorUser->name,
        'email' => $supervisorUser->email,
        'user_id' => $supervisorUser->id,
    ]);

    $project = UniProject::create([
        'name' => 'Available Project',
        'description' => 'Project for request normalization.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => false,
    ]);

    return [$student, $project, $supervisorUser];
}
