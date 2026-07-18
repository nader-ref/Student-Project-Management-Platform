<?php

use App\Models\Idea;
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

/**
 * @return array{0: User, 1: Supervisor}
 */
function createSimilarityFixture(): array
{
    $student = User::factory()->create([
        'name' => 'Similarity Student',
        'university_number' => 'STU-SIM-001',
        'email' => 'similarity-student@example.com',
    ]);
    $student->addRole('student');

    $supervisorUser = User::factory()->create([
        'name' => 'Dr. Similarity Supervisor',
        'university_number' => 'SUP-SIM-001',
        'email' => 'similarity-supervisor@example.com',
    ]);
    $supervisorUser->addRole('supervisor');

    $supervisor = Supervisor::create([
        'name' => $supervisorUser->name,
        'email' => $supervisorUser->email,
        'user_id' => $supervisorUser->id,
    ]);

    return [$student, $supervisor];
}

/**
 * @param  list<array<int, float>>  $vectors
 */
function fakeEmbeddingsInOrder(array $vectors): void
{
    Http::fake([
        'http://ollama.test/api/embed' => function () use (&$vectors) {
            // Support multiple batch calls by consuming vectors FIFO is not needed
            // when a single batch covers query + corpus.
            return Http::response([
                'embeddings' => $vectors,
            ], 200);
        },
    ]);
}

it('includes uni_projects and accepted ideas in the corpus', function () {
    [$student, $supervisor] = createSimilarityFixture();

    $project = UniProject::create([
        'name' => 'Smart Parking Management System',
        'description' => 'Manage campus parking availability.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => false,
    ]);

    $accepted = Idea::create([
        'projectname' => 'Accepted Study Planner',
        'proposal_description' => 'Help students plan study sessions.',
        'supervisor_id' => $supervisor->id,
        'requested_by_user_id' => $student->id,
        'count' => 1,
        'accepted' => true,
        'rejected' => false,
    ]);

    // query, project, accepted idea
    fakeEmbeddingsInOrder([
        [1.0, 0.0],
        [1.0, 0.0],
        [0.8, 0.6],
    ]);

    $result = app(ProjectSimilarityService::class)->compare(
        'Smart Parking App',
        'A system for campus parking slots.',
    );

    expect($result['ok'])->toBeTrue()
        ->and($result['mode'])->toBe('semantic')
        ->and($result['disclaimer'])->toBe(ProjectSimilarityService::DISCLAIMER)
        ->and($result['matches'])->toHaveCount(2);

    $types = collect($result['matches'])->pluck('source_type')->all();
    $ids = collect($result['matches'])->pluck('source_id')->all();

    expect($types)->toContain('existing_project')
        ->and($types)->toContain('accepted_idea')
        ->and($ids)->toContain($project->id)
        ->and($ids)->toContain($accepted->id);
});

it('excludes pending and rejected ideas from the corpus', function () {
    [$student, $supervisor] = createSimilarityFixture();

    UniProject::create([
        'name' => 'Baseline Catalog Project',
        'description' => 'A normal catalog project.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => false,
    ]);

    Idea::create([
        'projectname' => 'Pending Secret Idea',
        'proposal_description' => 'Should never appear in similarity results.',
        'supervisor_id' => $supervisor->id,
        'requested_by_user_id' => $student->id,
        'count' => 1,
        'accepted' => false,
        'rejected' => false,
    ]);

    Idea::create([
        'projectname' => 'Rejected Secret Idea',
        'proposal_description' => 'Should also never appear in similarity results.',
        'supervisor_id' => $supervisor->id,
        'requested_by_user_id' => $student->id,
        'count' => 1,
        'accepted' => false,
        'rejected' => true,
    ]);

    // query + one project only
    fakeEmbeddingsInOrder([
        [1.0, 0.0],
        [0.0, 1.0],
    ]);

    $result = app(ProjectSimilarityService::class)->compare(
        'Anything',
        'Draft description for exclusion test.',
    );

    expect($result['ok'])->toBeTrue();

    $titles = collect($result['matches'])->pluck('title')->all();
    expect($titles)->not->toContain('Pending Secret Idea')
        ->and($titles)->not->toContain('Rejected Secret Idea');

    $payload = json_encode($result);
    expect($payload)->not->toContain('Pending Secret Idea')
        ->and($payload)->not->toContain('Rejected Secret Idea')
        ->and($payload)->not->toContain('Should never appear')
        ->and($payload)->not->toContain('Should also never appear');
});

it('sorts matches descending and respects the minimum score filter', function () {
    [$student, $supervisor] = createSimilarityFixture();

    UniProject::create([
        'name' => 'High Match Project',
        'description' => 'Closely related system.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => false,
    ]);
    UniProject::create([
        'name' => 'Moderate Match Project',
        'description' => 'Somewhat related system.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => false,
    ]);
    UniProject::create([
        'name' => 'Low Match Project',
        'description' => 'Barely related system.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => false,
    ]);

    // cosine([1,0], [1,0]) = 1.0 high (>= 0.78)
    // cosine([1,0], [0.7071, 0.7071]) ≈ 0.707 moderate (>= 0.66, < 0.78)
    // cosine([1,0], [0.2, 0.98]) ≈ 0.2 filtered
    fakeEmbeddingsInOrder([
        [1.0, 0.0],
        [1.0, 0.0],
        [0.7071, 0.7071],
        [0.2, 0.979795897],
    ]);

    $result = app(ProjectSimilarityService::class)->compare('Query Title', 'Query draft');

    expect($result['matches'])->toHaveCount(2)
        ->and($result['matches'][0]['title'])->toBe('High Match Project')
        ->and($result['matches'][0]['level'])->toBe('high')
        ->and($result['matches'][0]['score'])->toBeGreaterThan($result['matches'][1]['score'])
        ->and($result['matches'][1]['title'])->toBe('Moderate Match Project')
        ->and($result['matches'][1]['level'])->toBe('moderate');

    $titles = collect($result['matches'])->pluck('title')->all();
    expect($titles)->not->toContain('Low Match Project');
});

it('limits results to the configured Top N', function () {
    [, $supervisor] = createSimilarityFixture();

    Config::set('ai.similarity_top_n', 2);

    for ($i = 1; $i <= 4; $i++) {
        UniProject::create([
            'name' => "TopN Project {$i}",
            'description' => "Description {$i}",
            'supervisor_id' => $supervisor->id,
            'department' => 'software',
            'taken' => false,
        ]);
    }

    fakeEmbeddingsInOrder([
        [1.0, 0.0],
        [1.0, 0.0],
        [0.99, 0.141067],
        [0.95, 0.31225],
        [0.90, 0.43589],
    ]);

    $result = app(ProjectSimilarityService::class)->compare('TopN Query', 'TopN draft text');

    expect($result['matches'])->toHaveCount(2);
});

it('returns a privacy-safe result shape without identity or foreign proposals', function () {
    [$student, $supervisor] = createSimilarityFixture();

    UniProject::create([
        'name' => 'Privacy Safe Project',
        'description' => 'Short public project blurb.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => false,
    ]);

    Idea::create([
        'projectname' => 'Accepted Privacy Idea',
        'proposal_description' => "FULL FOREIGN PROPOSAL TEXT THAT MUST NOT LEAK\nSecret details.",
        'supervisor_id' => $supervisor->id,
        'requested_by_user_id' => $student->id,
        'count' => 1,
        'accepted' => true,
        'rejected' => false,
    ]);

    fakeEmbeddingsInOrder([
        [1.0, 0.0],
        [1.0, 0.0],
        [0.9, 0.43589],
    ]);

    $result = app(ProjectSimilarityService::class)->compare('Privacy Query', 'Privacy draft');

    expect($result['matches'])->not->toBeEmpty();

    foreach ($result['matches'] as $match) {
        expect($match)->toHaveKeys(['source_type', 'source_id', 'title', 'score', 'percentage', 'level'])
            ->and($match)->not->toHaveKey('proposal_description')
            ->and($match)->not->toHaveKey('requested_by_user_id')
            ->and($match)->not->toHaveKey('supervisor_id')
            ->and($match)->not->toHaveKey('email')
            ->and($match)->not->toHaveKey('name');
    }

    $payload = json_encode($result);
    expect($payload)->not->toContain('FULL FOREIGN PROPOSAL TEXT THAT MUST NOT LEAK')
        ->and($payload)->not->toContain('similarity-student@example.com')
        ->and($payload)->not->toContain('similarity-supervisor@example.com')
        ->and($payload)->not->toContain('Dr. Similarity Supervisor')
        ->and($payload)->not->toContain('STU-SIM-001');
});

it('does not create idea rows or send notifications', function () {
    [, $supervisor] = createSimilarityFixture();

    UniProject::create([
        'name' => 'Side Effect Project',
        'description' => 'Used to verify no writes occur.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => false,
    ]);

    fakeEmbeddingsInOrder([
        [1.0, 0.0],
        [1.0, 0.0],
    ]);

    $ideasBefore = Idea::count();

    app(ProjectSimilarityService::class)->compare('No Side Effects', 'Draft that must not persist.');

    expect(Idea::count())->toBe($ideasBefore);
    Notification::assertNothingSent();
});

it('returns unavailable when Ollama embedding fails', function () {
    [, $supervisor] = createSimilarityFixture();

    UniProject::create([
        'name' => 'Unavailable Path Project',
        'description' => 'Exists so a corpus is loaded.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => false,
    ]);

    Http::fake([
        'http://ollama.test/api/embed' => Http::response(['error' => 'down'], 500),
    ]);

    $result = app(ProjectSimilarityService::class)->compare('Unavailable Query', 'Unavailable draft');

    expect($result)->toMatchArray([
        'ok' => false,
        'mode' => 'unavailable',
        'matches' => [],
        'message' => ProjectSimilarityService::MESSAGE_UNAVAILABLE,
    ]);
});

it('returns no significant matches when all scores are below the minimum', function () {
    [, $supervisor] = createSimilarityFixture();

    UniProject::create([
        'name' => 'Unrelated Project',
        'description' => 'Completely different topic.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => false,
    ]);

    fakeEmbeddingsInOrder([
        [1.0, 0.0],
        [0.0, 1.0],
    ]);

    $result = app(ProjectSimilarityService::class)->compare('Unrelated Query', 'Unrelated draft');

    expect($result)->toMatchArray([
        'ok' => true,
        'mode' => 'semantic',
        'matches' => [],
        'message' => ProjectSimilarityService::MESSAGE_NO_MATCHES,
        'disclaimer' => ProjectSimilarityService::DISCLAIMER,
    ]);
});

it('prefixes the query with search_query and corpus with search_document', function () {
    [, $supervisor] = createSimilarityFixture();

    UniProject::create([
        'name' => 'Smart Parking Management System',
        'description' => 'Problem Statement Students waste time finding empty campus parking spots during peak hours.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => false,
    ]);

    Http::fake([
        'http://ollama.test/api/embed' => function ($request) {
            $input = $request['input'] ?? null;
            expect($input)->toBeArray()
                ->and($input[0])->toStartWith(ProjectSimilarityService::QUERY_PREFIX)
                ->and($input[1])->toStartWith(ProjectSimilarityService::DOCUMENT_PREFIX)
                ->and($input[0])->toContain('Title:')
                ->and($input[1])->toContain('Title: Smart Parking Management System');

            $count = is_array($input) ? count($input) : 1;

            return Http::response([
                'embeddings' => array_fill(0, $count, [1.0, 0.0]),
            ], 200);
        },
    ]);

    app(ProjectSimilarityService::class)->compare(
        'AI Campus Parking Spot Finder',
        "Problem Statement\nFinding empty campus parking spaces is difficult during peak lecture hours.\n\nObjectives\n• Detect free spots",
    );

    Http::assertSentCount(1);
});

it('ranks a clearly related sample above an unrelated sample', function () {
    [, $supervisor] = createSimilarityFixture();

    UniProject::create([
        'name' => 'Smart Parking Management System',
        'description' => 'Problem Statement Campus parking spots are hard to find in peak hours.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => false,
    ]);
    UniProject::create([
        'name' => 'Hospital Appointment Management System',
        'description' => 'Problem Statement Patients wait too long to book clinic appointments.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => false,
    ]);

    // Query near parking; hospital nearly orthogonal.
    fakeEmbeddingsInOrder([
        [1.0, 0.0],
        [0.95, 0.3122],
        [0.1, 0.995],
    ]);

    $result = app(ProjectSimilarityService::class)->compare(
        'AI Campus Parking Spot Finder',
        'Problem Statement Finding empty campus parking spaces is difficult during peak hours.',
    );

    expect($result['matches'])->not->toBeEmpty()
        ->and($result['matches'][0]['title'])->toBe('Smart Parking Management System');

    $titles = collect($result['matches'])->pluck('title')->all();
    if (count($result['matches']) > 1) {
        expect($result['matches'][0]['score'])->toBeGreaterThan($result['matches'][1]['score']);
    }
    expect($titles[0])->toBe('Smart Parking Management System');
});

it('uses concise problem-statement text rather than full requirement boilerplate', function () {
    [, $supervisor] = createSimilarityFixture();

    UniProject::create([
        'name' => 'Boilerplate Probe Project',
        'description' => "Problem Statement Unique zebra telemetry for labs.\n\nObjectives\n• Track sensors\n\nInitial Functional Requirements\n• The system shall validate users\n• The system shall manage records",
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => false,
    ]);

    Http::fake([
        'http://ollama.test/api/embed' => function ($request) {
            $input = $request['input'];
            $doc = $input[1] ?? '';
            expect($doc)->toContain('Unique zebra telemetry')
                ->and($doc)->not->toContain('The system shall validate users')
                ->and($doc)->not->toContain('Initial Functional Requirements');

            return Http::response([
                'embeddings' => [
                    [1.0, 0.0],
                    [1.0, 0.0],
                ],
            ], 200);
        },
    ]);

    app(ProjectSimilarityService::class)->compare(
        'Zebra Lab Monitor',
        "Problem Statement Unique zebra telemetry for labs.\n\nInitial Functional Requirements\n• The system shall validate users",
    );
});
