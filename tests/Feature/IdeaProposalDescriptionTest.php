<?php

use App\Models\Idea;
use App\Models\Supervisor;
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

function createProposalDescriptionFixture(): array
{
    $student = User::factory()->create([
        'name' => 'Proposal Student',
        'university_number' => 'STU-PROP-001',
    ]);
    $student->addRole('student');

    $supervisorUser = User::factory()->create([
        'name' => 'Dr. Proposal Supervisor',
        'university_number' => 'SUP-PROP-001',
    ]);
    $supervisorUser->addRole('supervisor');

    $supervisor = Supervisor::create([
        'name' => $supervisorUser->name,
        'email' => $supervisorUser->email,
        'user_id' => $supervisorUser->id,
    ]);

    return [$student, $supervisor, $supervisorUser];
}

it('persists proposal_description when a student submits an idea', function () {
    [$student, $supervisor] = createProposalDescriptionFixture();

    $description = "Problem Statement\nStudents need better room discovery.\n\nObjectives\n• Build a finder app";

    $this->actingAs($student)
        ->post('/RequstIdea', [
            'projectname' => 'Campus Room Finder',
            'proposal_description' => $description,
            'count' => 1,
            'supervisor_id' => $supervisor->id,
            'oneid' => $student->university_number,
        ])
        ->assertSessionHas('success');

    $this->assertDatabaseHas('ideas', [
        'projectname' => 'Campus Room Finder',
        'proposal_description' => $description,
        'requested_by_user_id' => $student->id,
    ]);
});

it('allows idea submission without a proposal description', function () {
    [$student, $supervisor] = createProposalDescriptionFixture();

    $this->actingAs($student)
        ->post('/RequstIdea', [
            'projectname' => 'Title Only Idea',
            'count' => 1,
            'supervisor_id' => $supervisor->id,
            'oneid' => $student->university_number,
        ])
        ->assertSessionHas('success');

    $idea = Idea::first();

    expect($idea)->not->toBeNull();
    expect($idea->proposal_description)->toBeNull();
});

it('rejects proposal descriptions longer than 5000 characters', function () {
    [$student, $supervisor] = createProposalDescriptionFixture();

    $this->actingAs($student)
        ->from('/StudentDashboard')
        ->post('/RequstIdea', [
            'projectname' => 'Too Long Description',
            'proposal_description' => str_repeat('a', 5001),
            'count' => 1,
            'supervisor_id' => $supervisor->id,
            'oneid' => $student->university_number,
        ])
        ->assertSessionHasErrors('proposal_description');

    expect(Idea::count())->toBe(0);
});

it('shows view proposal details for supervisors when a description exists', function () {
    [$student, $supervisor, $supervisorUser] = createProposalDescriptionFixture();

    $idea = Idea::create([
        'projectname' => 'Detailed Proposal Idea',
        'proposal_description' => "Problem Statement\nNeed clearer lab booking.\n\nObjectives\n• Reduce wait time",
        'supervisor_id' => $supervisor->id,
        'requested_by_user_id' => $student->id,
        'count' => 1,
    ]);
    $idea->members()->create([
        'user_id' => $student->id,
        'position' => 1,
    ]);

    $this->actingAs($supervisorUser)
        ->get('/supervisorDashboard')
        ->assertOk()
        ->assertSee('View Proposal Details')
        ->assertSee('Detailed Proposal Idea')
        ->assertSee('Need clearer lab booking.', false)
        ->assertSee('proposal-details-modal', false);
});

it('renders safely when proposal_description is null', function () {
    [$student, $supervisor, $supervisorUser] = createProposalDescriptionFixture();

    $idea = Idea::create([
        'projectname' => 'No Details Idea',
        'proposal_description' => null,
        'supervisor_id' => $supervisor->id,
        'requested_by_user_id' => $student->id,
        'count' => 1,
    ]);
    $idea->members()->create([
        'user_id' => $student->id,
        'position' => 1,
    ]);

    $this->actingAs($supervisorUser)
        ->get('/supervisorDashboard')
        ->assertOk()
        ->assertSee('No Details Idea')
        ->assertSee('No proposal details provided.')
        ->assertSee('View Proposal Details')
        ->assertSee('Not analyzed');
});

it('escapes multiline proposal content for safe display', function () {
    [$student, $supervisor, $supervisorUser] = createProposalDescriptionFixture();

    $raw = "<script>alert('xss')</script>\nLine two";

    $idea = Idea::create([
        'projectname' => 'Escaped Proposal Idea',
        'proposal_description' => $raw,
        'supervisor_id' => $supervisor->id,
        'requested_by_user_id' => $student->id,
        'count' => 1,
    ]);
    $idea->members()->create([
        'user_id' => $student->id,
        'position' => 1,
    ]);

    $response = $this->actingAs($supervisorUser)->get('/supervisorDashboard');

    $response->assertOk();
    $response->assertSee('View Proposal Details');
    $response->assertSee('&lt;script&gt;alert(&#039;xss&#039;)&lt;/script&gt;', false);
    $response->assertDontSee("<script>alert('xss')</script>", false);
    $response->assertSee('Line two', false);
});

it('shows the proposal description field on the student new idea form', function () {
    [$student] = createProposalDescriptionFixture();

    $this->actingAs($student)
        ->get('/StudentDashboard')
        ->assertOk()
        ->assertSee('Proposal Description')
        ->assertSee('name="proposal_description"', false)
        ->assertSee('maxlength="5000"', false);
});
