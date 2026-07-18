<?php

use App\Models\Idea;
use App\Models\Supervisor;
use App\Models\UniProject;
use App\Models\User;
use App\Notifications\WorkflowNotification;
use App\Services\Ai\ProjectSimilarityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Laratrust\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    foreach (['student', 'supervisor'] as $roleName) {
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

/**
 * @return array{0: User, 1: Supervisor, 2: User}
 */
function createSnapshotFixture(string $suffix = '001'): array
{
    $student = User::factory()->create([
        'name' => "Snapshot Student {$suffix}",
        'university_number' => "STU-SNAP-{$suffix}",
        'email' => "snap-student-{$suffix}@example.com",
    ]);
    $student->addRole('student');

    $supervisorUser = User::factory()->create([
        'name' => "Dr. Snapshot Supervisor {$suffix}",
        'university_number' => "SUP-SNAP-{$suffix}",
        'email' => "snap-supervisor-{$suffix}@example.com",
    ]);
    $supervisorUser->addRole('supervisor');

    $supervisor = Supervisor::create([
        'name' => $supervisorUser->name,
        'email' => $supervisorUser->email,
        'user_id' => $supervisorUser->id,
    ]);

    return [$student, $supervisor, $supervisorUser];
}

/**
 * @param  list<array<int, float>>  $vectors
 */
function fakeSnapshotEmbeddings(array $vectors): void
{
    Http::fake([
        'http://ollama.test/api/embed' => Http::response([
            'embeddings' => $vectors,
        ], 200),
    ]);
}

it('stores a high match similarity snapshot after idea submission', function () {
    [$student, $supervisor] = createSnapshotFixture('HIGH');

    $project = UniProject::create([
        'name' => 'Smart Parking Management System',
        'description' => 'Manage campus parking availability and occupancy.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => false,
    ]);

    fakeSnapshotEmbeddings([
        [1.0, 0.0],
        [1.0, 0.0],
    ]);

    $this->actingAs($student)
        ->from('/student/ideas/create')
        ->post('/RequstIdea', [
            'projectname' => 'Campus Parking Slot Finder',
            'proposal_description' => "Problem Statement\nStudents struggle to find open parking spaces on campus.",
            'count' => 1,
            'supervisor_id' => $supervisor->id,
            'oneid' => $student->university_number,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $idea = Idea::query()->where('projectname', 'Campus Parking Slot Finder')->first();

    expect($idea)->not->toBeNull()
        ->and($idea->similarity_status)->toBe('matched')
        ->and($idea->similarity_percentage)->toBe(100.0)
        ->and($idea->similarity_level)->toBe('high')
        ->and($idea->similarity_match_source_type)->toBe('existing_project')
        ->and($idea->similarity_match_source_id)->toBe($project->id)
        ->and($idea->similarity_match_title)->toBe('Smart Parking Management System')
        ->and($idea->similarity_checked_at)->not->toBeNull()
        ->and($idea->similarity_model)->toBe('nomic-embed-text');
});

it('stores a moderate match similarity snapshot after idea submission', function () {
    [$student, $supervisor] = createSnapshotFixture('MOD');

    $project = UniProject::create([
        'name' => 'Library Seat Reservation',
        'description' => 'Reserve study seats in the campus library.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => false,
    ]);

    // Cosine([1,0], [0.7, 0.71414284285]) ≈ 0.70 → moderate
    fakeSnapshotEmbeddings([
        [1.0, 0.0],
        [0.7, 0.71414284285],
    ]);

    $this->actingAs($student)
        ->post('/RequstIdea', [
            'projectname' => 'Study Space Booking Helper',
            'proposal_description' => "Problem Statement\nStudents need an easier way to book quiet study seats.",
            'count' => 1,
            'supervisor_id' => $supervisor->id,
            'oneid' => $student->university_number,
        ])
        ->assertSessionHas('success');

    $idea = Idea::first();

    expect($idea->similarity_status)->toBe('matched')
        ->and($idea->similarity_level)->toBe('moderate')
        ->and($idea->similarity_percentage)->toBeGreaterThanOrEqual(66.0)
        ->and($idea->similarity_percentage)->toBeLessThan(78.0)
        ->and($idea->similarity_match_source_type)->toBe('existing_project')
        ->and($idea->similarity_match_source_id)->toBe($project->id)
        ->and($idea->similarity_match_title)->toBe('Library Seat Reservation')
        ->and($idea->similarity_model)->toBe('nomic-embed-text');
});

it('stores no_match when no significant similarity is found', function () {
    [$student, $supervisor] = createSnapshotFixture('NONE');

    UniProject::create([
        'name' => 'Unrelated Catalog Project',
        'description' => 'Completely different domain.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => false,
    ]);

    fakeSnapshotEmbeddings([
        [1.0, 0.0],
        [0.0, 1.0],
    ]);

    $this->actingAs($student)
        ->post('/RequstIdea', [
            'projectname' => 'Fresh Unique Idea',
            'proposal_description' => "Problem Statement\nA novel proposal with no close peers.",
            'count' => 1,
            'supervisor_id' => $supervisor->id,
            'oneid' => $student->university_number,
        ])
        ->assertSessionHas('success');

    $idea = Idea::first();

    expect($idea->similarity_status)->toBe('no_match')
        ->and($idea->similarity_percentage)->toBeNull()
        ->and($idea->similarity_level)->toBeNull()
        ->and($idea->similarity_match_source_type)->toBeNull()
        ->and($idea->similarity_match_source_id)->toBeNull()
        ->and($idea->similarity_match_title)->toBeNull()
        ->and($idea->similarity_checked_at)->not->toBeNull()
        ->and($idea->similarity_model)->toBe('nomic-embed-text');
});

it('still creates the idea and notifies the supervisor when Ollama is unavailable', function () {
    [$student, $supervisor, $supervisorUser] = createSnapshotFixture('UNAV');

    UniProject::create([
        'name' => 'Catalog Project For Embedding Attempt',
        'description' => 'Exists so similarity must call Ollama.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => false,
    ]);

    Http::fake([
        'http://ollama.test/api/embed' => Http::response('service down', 503),
    ]);

    $this->actingAs($student)
        ->from('/student/ideas/create')
        ->post('/RequstIdea', [
            'projectname' => 'Offline Embedding Idea',
            'proposal_description' => "Problem Statement\nShould still submit when embeddings fail.",
            'count' => 1,
            'supervisor_id' => $supervisor->id,
            'oneid' => $student->university_number,
        ])
        ->assertRedirect('/student/ideas/create')
        ->assertSessionHas('success');

    $idea = Idea::first();

    expect($idea)->not->toBeNull()
        ->and($idea->similarity_status)->toBe('unavailable')
        ->and($idea->similarity_percentage)->toBeNull()
        ->and($idea->similarity_match_title)->toBeNull()
        ->and($idea->similarity_checked_at)->not->toBeNull()
        ->and($idea->similarity_model)->toBe('nomic-embed-text');

    Notification::assertSentTo($supervisorUser, WorkflowNotification::class, function (WorkflowNotification $notification) use ($idea) {
        return $notification->type === 'idea_submitted'
            && $notification->relatedId === $idea->id;
    });
});

it('never prevents idea creation when the similarity service throws', function () {
    [$student, $supervisor] = createSnapshotFixture('EXC');

    $this->mock(ProjectSimilarityService::class, function ($mock) {
        $mock->shouldReceive('buildSnapshot')
            ->once()
            ->andThrow(new RuntimeException('Simulated similarity failure'));
    });

    $this->actingAs($student)
        ->post('/RequstIdea', [
            'projectname' => 'Exception Safe Idea',
            'proposal_description' => "Problem Statement\nMust persist even if snapshot throws.",
            'count' => 1,
            'supervisor_id' => $supervisor->id,
            'oneid' => $student->university_number,
        ])
        ->assertSessionHas('success');

    $idea = Idea::first();

    expect($idea)->not->toBeNull()
        ->and($idea->projectname)->toBe('Exception Safe Idea')
        ->and($idea->similarity_status)->toBe('unavailable')
        ->and($idea->similarity_percentage)->toBeNull()
        ->and($idea->similarity_match_title)->toBeNull();
});

it('ignores browser-posted similarity fields and uses server calculation only', function () {
    [$student, $supervisor] = createSnapshotFixture('TAMP');

    $project = UniProject::create([
        'name' => 'Trusted Catalog Project',
        'description' => 'Trusted description for server-side match.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => false,
    ]);

    fakeSnapshotEmbeddings([
        [1.0, 0.0],
        [1.0, 0.0],
    ]);

    $this->actingAs($student)
        ->post('/RequstIdea', [
            'projectname' => 'Tampered Client Idea',
            'proposal_description' => "Problem Statement\nServer must ignore client similarity payloads.",
            'count' => 1,
            'supervisor_id' => $supervisor->id,
            'oneid' => $student->university_number,
            'similarity_status' => 'no_match',
            'similarity_percentage' => 12.5,
            'similarity_level' => 'low',
            'similarity_match_title' => 'Client Fake Title',
            'similarity_match_source_type' => 'accepted_idea',
            'similarity_match_source_id' => 999999,
        ])
        ->assertSessionHas('success');

    $idea = Idea::first();

    expect($idea->similarity_status)->toBe('matched')
        ->and($idea->similarity_percentage)->toBe(100.0)
        ->and($idea->similarity_level)->toBe('high')
        ->and($idea->similarity_match_title)->toBe('Trusted Catalog Project')
        ->and($idea->similarity_match_source_type)->toBe('existing_project')
        ->and($idea->similarity_match_source_id)->toBe($project->id)
        ->and($idea->similarity_match_title)->not->toBe('Client Fake Title');
});

it('renders legacy null snapshots safely on the supervisor dashboard', function () {
    [$student, $supervisor, $supervisorUser] = createSnapshotFixture('LEG');

    $idea = Idea::create([
        'projectname' => 'Legacy Idea Without Snapshot',
        'proposal_description' => 'Submitted before similarity snapshots existed.',
        'supervisor_id' => $supervisor->id,
        'requested_by_user_id' => $student->id,
        'count' => 1,
        'accepted' => false,
        'rejected' => false,
    ]);
    $idea->members()->create([
        'user_id' => $student->id,
        'position' => 1,
    ]);

    expect($idea->similarity_status)->toBeNull()
        ->and($idea->similarityDisplayLabel())->toBe('Not analyzed');

    $response = $this->actingAs($supervisorUser)->get('/supervisorDashboard');

    $response->assertOk()
        ->assertSee('Legacy Idea Without Snapshot', false)
        ->assertSee('Not analyzed', false)
        ->assertSee('Advisory semantic similarity; not plagiarism detection.', false)
        ->assertSee('data-similarity-status=""', false);
});

it('shows matched badge and safe modal snapshot details for supervisors', function () {
    [$student, $supervisor, $supervisorUser] = createSnapshotFixture('UI');

    $foreignStudent = User::factory()->create([
        'name' => 'Foreign Matched Student',
        'university_number' => 'STU-FOREIGN-999',
    ]);

    $longProposal = "Problem Statement\n".str_repeat('Parking congestion details. ', 40)
        ."\n\nObjectives\n• Monitor spaces\n• Guide drivers\n\nScope\nCampus-wide lots.";

    $idea = Idea::create([
        'projectname' => 'Pending Parking Idea',
        'proposal_description' => $longProposal,
        'supervisor_id' => $supervisor->id,
        'requested_by_user_id' => $student->id,
        'count' => 1,
        'accepted' => false,
        'rejected' => false,
        'similarity_status' => 'matched',
        'similarity_percentage' => 82.3,
        'similarity_level' => 'high',
        'similarity_match_source_type' => 'existing_project',
        'similarity_match_source_id' => 42,
        'similarity_match_title' => 'Smart Parking Management System',
        'similarity_checked_at' => now(),
        'similarity_model' => 'nomic-embed-text',
    ]);
    $idea->members()->create([
        'user_id' => $student->id,
        'position' => 1,
    ]);

    $response = $this->actingAs($supervisorUser)->get('/supervisorDashboard');
    $html = $response->getContent();

    $response->assertOk()
        ->assertSee('82.3% · High', false)
        ->assertSee('Semantic Similarity Analysis', false)
        ->assertSee('proposal-modal__scroll', false)
        ->assertSee('This result is advisory only and does not determine acceptance or rejection.', false)
        ->assertSee('data-similarity-status="matched"', false)
        ->assertSee('data-similarity-percentage="82.3"', false)
        ->assertSee('data-similarity-level="high"', false)
        ->assertSee('data-similarity-match-title="Smart Parking Management System"', false)
        ->assertSee('data-similarity-match-source="Existing Project"', false)
        ->assertSee('data-similarity-model="nomic-embed-text"', false)
        ->assertSee('Closest match: ', false)
        ->assertSee("percentage + '% · '", false)
        ->assertDontSee('Foreign Matched Student', false)
        ->assertDontSee('STU-FOREIGN-999', false)
        ->assertDontSee('Manage campus parking availability and occupancy.', false);

    expect($html)
        ->toContain('function renderSimilarity')
        ->toContain("status === 'matched'")
        ->toContain("status === 'no_match'")
        ->toContain("status === 'unavailable'")
        ->toContain('This idea was submitted before similarity snapshots were enabled.')
        ->toContain('No significant similarity was found in the current project records.')
        ->toContain('Similarity analysis was unavailable when this idea was submitted.');
});

it('shows no-match and unavailable badges for supervisors', function () {
    [$student, $supervisor, $supervisorUser] = createSnapshotFixture('BADGE');

    $noMatch = Idea::create([
        'projectname' => 'No Match Idea',
        'proposal_description' => 'Unique enough.',
        'supervisor_id' => $supervisor->id,
        'requested_by_user_id' => $student->id,
        'count' => 1,
        'similarity_status' => 'no_match',
        'similarity_checked_at' => now(),
        'similarity_model' => 'nomic-embed-text',
    ]);
    $noMatch->members()->create(['user_id' => $student->id, 'position' => 1]);

    $otherStudent = User::factory()->create([
        'name' => 'Other Snapshot Student',
        'university_number' => 'STU-SNAP-OTHER',
        'email' => 'snap-other@example.com',
    ]);
    $otherStudent->addRole('student');

    $unavailable = Idea::create([
        'projectname' => 'Unavailable Idea',
        'proposal_description' => 'Checked while offline.',
        'supervisor_id' => $supervisor->id,
        'requested_by_user_id' => $otherStudent->id,
        'count' => 1,
        'similarity_status' => 'unavailable',
        'similarity_checked_at' => now(),
        'similarity_model' => 'nomic-embed-text',
    ]);
    $unavailable->members()->create(['user_id' => $otherStudent->id, 'position' => 1]);

    $response = $this->actingAs($supervisorUser)->get('/supervisorDashboard');

    $response->assertOk()
        ->assertSee('No significant similarity', false)
        ->assertSee('Similarity unavailable', false)
        ->assertSee('data-similarity-status="no_match"', false)
        ->assertSee('data-similarity-status="unavailable"', false);
});

it('escapes malicious similarity title snapshot in supervisor output', function () {
    [$student, $supervisor, $supervisorUser] = createSnapshotFixture('XSS');

    $idea = Idea::create([
        'projectname' => 'XSS Guard Idea',
        'proposal_description' => 'Safe proposal body.',
        'supervisor_id' => $supervisor->id,
        'requested_by_user_id' => $student->id,
        'count' => 1,
        'similarity_status' => 'matched',
        'similarity_percentage' => 80.0,
        'similarity_level' => 'high',
        'similarity_match_source_type' => 'accepted_idea',
        'similarity_match_source_id' => 7,
        'similarity_match_title' => '<script>alert("xss")</script>',
        'similarity_checked_at' => now(),
        'similarity_model' => 'nomic-embed-text',
    ]);
    $idea->members()->create(['user_id' => $student->id, 'position' => 1]);

    $html = $this->actingAs($supervisorUser)->get('/supervisorDashboard')->getContent();

    expect($html)->toContain('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;')
        ->and($html)->not->toContain('<script>alert("xss")</script>');
});
