<?php

use App\Models\Idea;
use App\Models\IdeaMember;
use App\Models\Supervisor;
use App\Models\UniProject;
use App\Models\User;
use App\Notifications\WorkflowNotification;
use App\Services\Ai\ProjectSimilarityService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Laratrust\Models\Role;

beforeEach(function () {
    foreach (['student', 'supervisor', 'admin'] as $roleName) {
        Role::firstOrCreate(
            ['name' => $roleName],
            ['display_name' => ucfirst($roleName), 'description' => $roleName.' role'],
        );
    }

    Config::set('ai.enabled', true);
    Config::set('ai.provider', 'ollama');
    Config::set('ai.base_url', 'http://ollama.test');
    Config::set('ai.embedding_model', 'nomic-embed-text');
    Config::set('ai.embedding_timeout', 45);
    Config::set('ai.similarity_min_score', 0.66);
    Config::set('ai.similarity_high_score', 0.78);
    Config::set('ai.similarity_top_n', 5);

    Notification::fake();
});

function createSimilarityRouteStudent(string $suffix = '001'): User
{
    $student = User::factory()->create([
        'name' => "Similarity UI Student {$suffix}",
        'university_number' => "STU-SIM-UI-{$suffix}",
        'email' => "sim-ui-{$suffix}@example.com",
    ]);
    $student->addRole('student');

    return $student;
}

/**
 * @return array{0: User, 1: Supervisor, 2: User}
 */
function createSimilarityRouteFixture(string $suffix = '001'): array
{
    $student = createSimilarityRouteStudent($suffix);

    $supervisorUser = User::factory()->create([
        'name' => "Dr. Similarity UI {$suffix}",
        'university_number' => "SUP-SIM-UI-{$suffix}",
        'email' => "sim-sup-ui-{$suffix}@example.com",
    ]);
    $supervisorUser->addRole('supervisor');

    $supervisor = Supervisor::create([
        'name' => $supervisorUser->name,
        'email' => $supervisorUser->email,
        'user_id' => $supervisorUser->id,
    ]);

    return [$student, $supervisor, $supervisorUser];
}

it('blocks guests from the similarity endpoint', function () {
    $this->postJson(route('student.ai.similarity'), [
        'title' => 'Campus Finder',
        'proposal_description' => 'A draft proposal description.',
    ])->assertUnauthorized();

    $this->assertDatabaseCount('ideas', 0);
});

it('blocks supervisors from the similarity endpoint', function () {
    [, , $supervisorUser] = createSimilarityRouteFixture('sup');

    $this->actingAs($supervisorUser)
        ->post(route('student.ai.similarity'), [
            'title' => 'Campus Finder',
            'proposal_description' => 'A draft proposal description.',
        ])
        ->assertRedirect('/Login');

    $this->assertDatabaseCount('ideas', 0);
});

it('blocks admins from the similarity endpoint', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['university_number' => 'ADM-SIM-UI-001']);
    $admin->addRole('admin');

    $this->actingAs($admin)
        ->post(route('student.ai.similarity'), [
            'title' => 'Campus Finder',
            'proposal_description' => 'A draft proposal description.',
        ])
        ->assertRedirect('/Login');

    $this->assertDatabaseCount('ideas', 0);
});

it('validates similarity request input for students', function () {
    $student = createSimilarityRouteStudent('val');

    $this->actingAs($student)
        ->postJson(route('student.ai.similarity'), [
            'title' => 'ab',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['title']);

    $this->actingAs($student)
        ->postJson(route('student.ai.similarity'), [
            'title' => 'Valid Title',
            'proposal_description' => str_repeat('a', 5001),
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['proposal_description']);
});

it('returns privacy-safe JSON for a successful student similarity check', function () {
    [$student, $supervisor] = createSimilarityRouteFixture('ok');

    UniProject::create([
        'name' => 'Smart Parking Management System',
        'description' => 'Manage campus parking availability.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => false,
    ]);

    Idea::create([
        'projectname' => 'Accepted Study Planner',
        'proposal_description' => 'FOREIGN PROPOSAL BODY MUST NOT APPEAR IN JSON RESPONSE',
        'supervisor_id' => $supervisor->id,
        'requested_by_user_id' => $student->id,
        'count' => 1,
        'accepted' => true,
        'rejected' => false,
    ]);

    Http::fake([
        'http://ollama.test/api/embed' => Http::response([
            'embeddings' => [
                [1.0, 0.0],
                [1.0, 0.0],
                [0.8, 0.6],
            ],
        ], 200),
    ]);

    $ideasBefore = Idea::count();

    $response = $this->actingAs($student)
        ->postJson(route('student.ai.similarity'), [
            'title' => 'Smart Parking App',
            'proposal_description' => 'A system for campus parking slots.',
        ])
        ->assertOk()
        ->assertJsonPath('ok', true)
        ->assertJsonPath('mode', 'semantic')
        ->assertJsonPath('disclaimer', ProjectSimilarityService::DISCLAIMER);

    $payload = $response->json();

    expect($payload['matches'])->not->toBeEmpty()
        ->and(Idea::count())->toBe($ideasBefore);

    foreach ($payload['matches'] as $match) {
        expect($match)->toHaveKeys(['source_type', 'source_id', 'title', 'score', 'percentage', 'level'])
            ->and($match)->not->toHaveKey('proposal_description')
            ->and($match)->not->toHaveKey('requested_by_user_id')
            ->and($match)->not->toHaveKey('supervisor_id')
            ->and($match)->not->toHaveKey('email');
    }

    $encoded = json_encode($payload);
    expect($encoded)->not->toContain('FOREIGN PROPOSAL BODY MUST NOT APPEAR IN JSON RESPONSE')
        ->and($encoded)->not->toContain('sim-ui-ok@example.com')
        ->and($encoded)->not->toContain('Dr. Similarity UI ok')
        ->and($encoded)->not->toContain('STU-SIM-UI-ok');

    Notification::assertNothingSent();
});

it('returns the no significant match path for students', function () {
    [$student, $supervisor] = createSimilarityRouteFixture('none');

    UniProject::create([
        'name' => 'Unrelated Hardware Monitor',
        'description' => 'Completely different topic.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => false,
    ]);

    Http::fake([
        'http://ollama.test/api/embed' => Http::response([
            'embeddings' => [
                [1.0, 0.0],
                [0.0, 1.0],
            ],
        ], 200),
    ]);

    $this->actingAs($student)
        ->postJson(route('student.ai.similarity'), [
            'title' => 'Unrelated Query',
            'proposal_description' => 'Draft with no overlap.',
        ])
        ->assertOk()
        ->assertJsonPath('ok', true)
        ->assertJsonPath('mode', 'semantic')
        ->assertJsonPath('matches', [])
        ->assertJsonPath('message', ProjectSimilarityService::MESSAGE_NO_MATCHES);
});

it('returns a soft unavailable response when Ollama fails', function () {
    [$student, $supervisor] = createSimilarityRouteFixture('down');

    UniProject::create([
        'name' => 'Catalog Project For Soft Fail',
        'description' => 'Exists so corpus is non-empty.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => false,
    ]);

    Http::fake([
        'http://ollama.test/api/embed' => Http::response(['error' => 'down'], 500),
    ]);

    $this->actingAs($student)
        ->postJson(route('student.ai.similarity'), [
            'title' => 'Soft Fail Query',
            'proposal_description' => 'Still submittable draft.',
        ])
        ->assertOk()
        ->assertJsonPath('ok', false)
        ->assertJsonPath('mode', 'unavailable')
        ->assertJsonPath('matches', [])
        ->assertJsonPath('message', ProjectSimilarityService::MESSAGE_UNAVAILABLE);

    Notification::assertNothingSent();
    $this->assertDatabaseCount('ideas', 0);
});

it('does not create ideas or send notifications from the similarity endpoint', function () {
    $student = createSimilarityRouteStudent('side');

    Http::fake([
        'http://ollama.test/api/embed' => Http::response([
            'embeddings' => [[1.0, 0.0]],
        ], 200),
    ]);

    $this->actingAs($student)
        ->postJson(route('student.ai.similarity'), [
            'title' => 'No Side Effects Title',
            'proposal_description' => 'Should not persist.',
        ])
        ->assertOk();

    $this->assertDatabaseCount('ideas', 0);
    Notification::assertNothingSent();
    Notification::assertNotSentTo($student, WorkflowNotification::class);
});

it('shows the similarity UI for discovery students on New Idea', function () {
    $student = createSimilarityRouteStudent('disc');

    $this->actingAs($student)
        ->get('/StudentDashboard')
        ->assertOk()
        ->assertSee('Similar Projects Check')
        ->assertSee('Check Similar Projects')
        ->assertSee('Similarity results are advisory only and are not plagiarism detection.')
        ->assertSee(route('student.ai.similarity'), false)
        ->assertSee('Project Proposal Assistant');
});

it('does not show New Idea similarity UI for enrolled students', function () {
    [$student, $supervisor] = createSimilarityRouteFixture('enr');

    $project = UniProject::create([
        'name' => 'Enrolled Student Project',
        'description' => 'Assigned project for enrollment mode.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => true,
        'student_count' => 1,
    ]);
    $project->members()->create([
        'user_id' => $student->id,
        'position' => 1,
    ]);

    $this->actingAs($student)
        ->get('/StudentDashboard')
        ->assertOk()
        ->assertDontSee('Similar Projects Check')
        ->assertDontSee('Check Similar Projects')
        ->assertDontSee(route('student.ai.similarity'), false);
});

it('does not show New Idea similarity UI for pending students', function () {
    [$student, $supervisor] = createSimilarityRouteFixture('pend');

    $idea = Idea::create([
        'projectname' => 'Pending Idea Blocks Discovery',
        'proposal_description' => null,
        'supervisor_id' => $supervisor->id,
        'requested_by_user_id' => $student->id,
        'count' => 1,
        'accepted' => false,
        'rejected' => false,
    ]);

    IdeaMember::create([
        'idea_id' => $idea->id,
        'user_id' => $student->id,
        'position' => 1,
    ]);

    $this->actingAs($student)
        ->get('/StudentDashboard')
        ->assertOk()
        ->assertDontSee('Similar Projects Check')
        ->assertDontSee('Check Similar Projects');
});
