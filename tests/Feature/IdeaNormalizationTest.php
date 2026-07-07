<?php

use App\Models\Idea;
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

it('creates ideas with relational supervisor, requester, and members', function () {
    [$student, $supervisor] = createIdeaFixture();

    $this->actingAs($student)
        ->post('/RequstIdea', [
            'projectname' => 'Smart Campus Navigator',
            'count' => 1,
            'supervisor_id' => $supervisor->id,
            'oneid' => $student->university_number,
        ])
        ->assertSessionHas('success');

    $idea = Idea::first();

    expect($idea->supervisor_id)->toBe($supervisor->id);
    expect($idea->requested_by_user_id)->toBe($student->id);

    $this->assertDatabaseHas('idea_members', [
        'idea_id' => $idea->id,
        'user_id' => $student->id,
        'position' => 1,
    ]);
});

it('shows only ideas where the student is a relational member', function () {
    [$student, $supervisor] = createIdeaFixture();
    $otherStudent = User::factory()->create(['university_number' => 'STU-2026-099']);
    $otherStudent->addRole('student');

    $visibleIdea = createIdeaForStudent($student, $supervisor, 'Visible Student Idea');
    $hiddenIdea = createIdeaForStudent($otherStudent, $supervisor, 'Hidden Student Idea');

    $response = $this->actingAs($student)->get('/StudentDashboard/acceptanceidea');

    $response->assertOk();
    $response->assertSee('IDEA-'.str_pad($visibleIdea->id, 4, '0', STR_PAD_LEFT));
    $response->assertDontSee('IDEA-'.str_pad($hiddenIdea->id, 4, '0', STR_PAD_LEFT));
});

it('prevents duplicate idea members for the same idea and user', function () {
    [$student, $supervisor] = createIdeaFixture();

    $idea = createIdeaForStudent($student, $supervisor, 'Duplicate Member Idea');

    expect(fn () => $idea->members()->create([
        'user_id' => $student->id,
        'position' => 2,
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});

it('rejects ideas through relational supervisor ownership', function () {
    [$student, $supervisor, $supervisorUser] = createIdeaFixture();
    $idea = createIdeaForStudent($student, $supervisor, 'Rejected Student Idea');

    $this->actingAs($supervisorUser)
        ->withSession(['id' => $supervisor->id])
        ->post('/rejectidea', [
            'idea' => $idea->id,
            'reason' => 'Needs a clearer scope.',
        ])
        ->assertSessionHas('success');

    $this->assertDatabaseHas('ideas', [
        'id' => $idea->id,
        'accepted' => false,
        'rejected' => true,
        'reason' => 'Needs a clearer scope.',
    ]);
});

it('accepts ideas and creates project members from idea members', function () {
    [$student, $supervisor, $supervisorUser] = createIdeaFixture();
    $idea = createIdeaForStudent($student, $supervisor, 'Accepted Student Idea');

    $this->actingAs($supervisorUser)
        ->withSession(['id' => $supervisor->id])
        ->post('/acceptidea', ['idea' => $idea->id])
        ->assertSessionHas('success');

    $project = UniProject::where('name', 'Accepted Student Idea')->first();

    expect($project)->not->toBeNull();
    expect($project->taken)->toBeTruthy();
    expect($project->student_count)->toBe(1);
    expect($project->isAssigned())->toBeTrue();
    expect($project->members()->where('user_id', $student->id)->exists())->toBeTrue();

    $this->assertDatabaseHas('ideas', [
        'id' => $idea->id,
        'accepted' => true,
        'rejected' => false,
    ]);
});

function createIdeaFixture(): array
{
    $student = User::factory()->create([
        'name' => 'Ahmad Al Ali',
        'university_number' => 'STU-2026-001',
    ]);
    $student->addRole('student');

    $supervisorUser = User::factory()->create([
        'name' => 'Dr. Lina Haddad',
        'university_number' => 'SUP-2026-001',
    ]);
    $supervisorUser->addRole('supervisor');

    $supervisor = Supervisor::create([
        'name' => $supervisorUser->name,
        'email' => $supervisorUser->email,
        'user_id' => $supervisorUser->id,
    ]);

    return [$student, $supervisor, $supervisorUser];
}

function createIdeaForStudent(User $student, Supervisor $supervisor, string $projectName): Idea
{
    $idea = Idea::create([
        'supervisor_id' => $supervisor->id,
        'requested_by_user_id' => $student->id,
        'projectname' => $projectName,
        'count' => 1,
    ]);

    $idea->members()->create([
        'user_id' => $student->id,
        'position' => 1,
    ]);

    return $idea;
}
