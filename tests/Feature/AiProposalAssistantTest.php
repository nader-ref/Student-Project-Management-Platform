<?php

use App\Models\Idea;
use App\Models\Supervisor;
use App\Models\User;
use App\Notifications\WorkflowNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Laratrust\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

beforeEach(function () {
    foreach (['student', 'supervisor', 'admin'] as $roleName) {
        Role::firstOrCreate(
            ['name' => $roleName],
            ['display_name' => ucfirst($roleName), 'description' => $roleName.' role'],
        );
    }

    Config::set('ai.enabled', false);
    Config::set('ai.provider', 'ollama');
    Config::set('ai.base_url', 'http://ollama.test');
    Config::set('ai.model', 'llama3.2:3b');
    Config::set('ai.timeout', 45);
    Config::set('ai.max_input_chars', 2000);
});

function createAiProposalStudent(): User
{
    $student = User::factory()->create([
        'university_number' => 'STU-AI-001',
    ]);
    $student->addRole('student');

    return $student;
}

function validRawIdea(): string
{
    return 'A campus system that helps students discover empty study rooms using live occupancy sensors and booking history.';
}

function ollamaSuggestionPayload(): array
{
    return [
        'title' => 'Campus Study Room Finder',
        'problem_statement' => 'Students waste time searching for quiet study spaces.',
        'objectives' => [
            'Show live room availability',
            'Allow short-term room reservations',
        ],
        'scope' => 'In scope: campus buildings with sensors. Out of scope: off-campus venues.',
        'functional_requirements' => [
            'Display available rooms on a map',
            'Reserve a room for up to two hours',
        ],
    ];
}

it('blocks guests from the AI proposal assistant', function () {
    postJson(route('student.ai.proposal'), [
        'raw_idea' => validRawIdea(),
    ])->assertUnauthorized();

    assertDatabaseCount('ideas', 0);
});

it('blocks non-students from the AI proposal assistant', function () {
    /** @var User $supervisorUser */
    $supervisorUser = User::factory()->create(['university_number' => 'SUP-AI-001']);
    $supervisorUser->addRole('supervisor');
    Supervisor::create([
        'name' => $supervisorUser->name,
        'email' => $supervisorUser->email,
        'user_id' => $supervisorUser->id,
    ]);

    actingAs($supervisorUser)
        ->post(route('student.ai.proposal'), [
            'raw_idea' => validRawIdea(),
        ])
        ->assertRedirect('/Login');

    assertDatabaseCount('ideas', 0);
});

it('validates raw idea length for the AI proposal assistant', function () {
    $student = createAiProposalStudent();

    actingAs($student)
        ->postJson(route('student.ai.proposal'), [
            'raw_idea' => 'too short',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['raw_idea']);

    actingAs($student)
        ->postJson(route('student.ai.proposal'), [
            'raw_idea' => str_repeat('a', 2001),
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['raw_idea']);

    assertDatabaseCount('ideas', 0);
});

it('returns AI mode when Ollama succeeds', function () {
    Config::set('ai.enabled', true);
    Config::set('ai.provider', 'ollama');
    Notification::fake();

    $suggestion = ollamaSuggestionPayload();

    Http::fake([
        'ollama.test/api/chat' => Http::response([
            'message' => [
                'role' => 'assistant',
                'content' => json_encode($suggestion),
            ],
        ], 200),
    ]);

    $student = createAiProposalStudent();

    $response = actingAs($student)
        ->postJson(route('student.ai.proposal'), [
            'raw_idea' => validRawIdea(),
        ]);

    $response->assertOk()
        ->assertJsonPath('ok', true)
        ->assertJsonPath('mode', 'ai')
        ->assertJsonPath('suggestion.title', $suggestion['title'])
        ->assertJsonPath('suggestion.problem_statement', $suggestion['problem_statement'])
        ->assertJsonPath('suggestion.scope', $suggestion['scope']);

    expect($response->json('suggestion.objectives'))->toBe($suggestion['objectives']);
    expect($response->json('suggestion.functional_requirements'))->toBe($suggestion['functional_requirements']);
    expect($response->json('disclaimer'))->not->toBeEmpty();

    Http::assertSentCount(1);
    assertDatabaseCount('ideas', 0);
    Notification::assertNothingSent();
});

it('falls back when Ollama fails', function () {
    Config::set('ai.enabled', true);
    Config::set('ai.provider', 'ollama');
    Notification::fake();

    Http::fake([
        'ollama.test/api/chat' => Http::response('unavailable', 503),
    ]);

    $student = createAiProposalStudent();

    $response = actingAs($student)
        ->postJson(route('student.ai.proposal'), [
            'raw_idea' => validRawIdea(),
        ]);

    $response->assertOk()
        ->assertJsonPath('ok', true)
        ->assertJsonPath('mode', 'fallback')
        ->assertJsonStructure([
            'disclaimer',
            'suggestion' => [
                'title',
                'problem_statement',
                'objectives',
                'scope',
                'functional_requirements',
            ],
        ]);

    expect($response->json('suggestion.objectives'))->toBeArray()->not->toBeEmpty();
    expect($response->json('suggestion.functional_requirements'))->toBeArray()->not->toBeEmpty();

    assertDatabaseCount('ideas', 0);
    Notification::assertNothingSent();
});

it('falls back when Ollama returns malformed JSON', function () {
    Config::set('ai.enabled', true);
    Config::set('ai.provider', 'ollama');
    Notification::fake();

    Http::fake([
        'ollama.test/api/chat' => Http::response([
            'message' => [
                'role' => 'assistant',
                'content' => 'this is not valid json {{{',
            ],
        ], 200),
    ]);

    $student = createAiProposalStudent();

    actingAs($student)
        ->postJson(route('student.ai.proposal'), [
            'raw_idea' => validRawIdea(),
        ])
        ->assertOk()
        ->assertJsonPath('ok', true)
        ->assertJsonPath('mode', 'fallback');

    assertDatabaseCount('ideas', 0);
    Notification::assertNothingSent();
});

it('uses fallback mode when AI is disabled and does not create an idea', function () {
    Config::set('ai.enabled', false);
    Notification::fake();
    Http::fake();

    $student = createAiProposalStudent();

    actingAs($student)
        ->postJson(route('student.ai.proposal'), [
            'raw_idea' => validRawIdea(),
        ])
        ->assertOk()
        ->assertJsonPath('ok', true)
        ->assertJsonPath('mode', 'fallback');

    Http::assertNothingSent();
    assertDatabaseCount('ideas', 0);
    Notification::assertNothingSent();
});

it('uses fallback when provider is not ollama', function () {
    Config::set('ai.enabled', true);
    Config::set('ai.provider', 'openai');
    Http::fake();

    $student = createAiProposalStudent();

    actingAs($student)
        ->postJson(route('student.ai.proposal'), [
            'raw_idea' => validRawIdea(),
        ])
        ->assertOk()
        ->assertJsonPath('mode', 'fallback');

    Http::assertNothingSent();
});

it('parses Ollama message.content when wrapped in markdown fences', function () {
    Config::set('ai.enabled', true);
    Config::set('ai.provider', 'ollama');

    $suggestion = ollamaSuggestionPayload();
    $fenced = "```json\n".json_encode($suggestion)."\n```";

    Http::fake([
        'ollama.test/api/chat' => Http::response([
            'message' => [
                'role' => 'assistant',
                'content' => $fenced,
            ],
        ], 200),
    ]);

    $student = createAiProposalStudent();

    actingAs($student)
        ->postJson(route('student.ai.proposal'), [
            'raw_idea' => validRawIdea(),
        ])
        ->assertOk()
        ->assertJsonPath('mode', 'ai')
        ->assertJsonPath('suggestion.title', $suggestion['title']);
});

it('accepts scope as an array from Ollama JSON', function () {
    Config::set('ai.enabled', true);
    Config::set('ai.provider', 'ollama');

    $payload = ollamaSuggestionPayload();
    $payload['scope'] = [
        'Sensor installation in classrooms',
        'Data analysis algorithm development',
    ];

    Http::fake([
        'ollama.test/api/chat' => Http::response([
            'message' => [
                'role' => 'assistant',
                'content' => json_encode($payload),
            ],
        ], 200),
    ]);

    $student = createAiProposalStudent();

    actingAs($student)
        ->postJson(route('student.ai.proposal'), [
            'raw_idea' => validRawIdea(),
        ])
        ->assertOk()
        ->assertJsonPath('mode', 'ai')
        ->assertJsonPath('diagnostic', 'ok')
        ->assertJsonPath(
            'suggestion.scope',
            'Sensor installation in classrooms Data analysis algorithm development'
        );
});

it('recovers functional_requirements nested under an object-shaped scope', function () {
    Config::set('ai.enabled', true);
    Config::set('ai.provider', 'ollama');

    $payload = [
        'title' => 'University Parking Reservation System',
        'problem_statement' => 'Campus parking is hard to find during peak hours.',
        'objectives' => [
            'Let students reserve parking spots',
            'Show live availability',
        ],
        'scope' => [
            'functional_requirements' => [
                'User registration and login',
                'Parking spot reservation',
                'Payment confirmation',
            ],
        ],
    ];

    Http::fake([
        'ollama.test/api/chat' => Http::response([
            'message' => [
                'role' => 'assistant',
                'content' => json_encode($payload),
            ],
        ], 200),
    ]);

    $student = createAiProposalStudent();

    actingAs($student)
        ->postJson(route('student.ai.proposal'), [
            'raw_idea' => validRawIdea(),
        ])
        ->assertOk()
        ->assertJsonPath('mode', 'ai')
        ->assertJsonPath('diagnostic', 'ok')
        ->assertJsonPath('suggestion.title', $payload['title'])
        ->assertJsonPath('suggestion.functional_requirements.0', 'User registration and login');
});

it('sends keep_alive on Ollama chat requests', function () {
    Config::set('ai.enabled', true);
    Config::set('ai.provider', 'ollama');
    Config::set('ai.keep_alive', '10m');

    Http::fake([
        'ollama.test/api/chat' => Http::response([
            'message' => [
                'role' => 'assistant',
                'content' => json_encode(ollamaSuggestionPayload()),
            ],
        ], 200),
    ]);

    $student = createAiProposalStudent();

    actingAs($student)
        ->postJson(route('student.ai.proposal'), [
            'raw_idea' => validRawIdea(),
        ])
        ->assertOk()
        ->assertJsonPath('mode', 'ai');

    Http::assertSent(function ($request) {
        return $request->url() === 'http://ollama.test/api/chat'
            && ($request['keep_alive'] ?? null) === '10m';
    });
});

it('includes a local diagnostic when falling back after malformed JSON', function () {
    Config::set('ai.enabled', true);
    Config::set('ai.provider', 'ollama');

    Http::fake([
        'ollama.test/api/chat' => Http::response([
            'message' => [
                'role' => 'assistant',
                'content' => 'not-json',
            ],
        ], 200),
    ]);

    $student = createAiProposalStudent();

    actingAs($student)
        ->postJson(route('student.ai.proposal'), [
            'raw_idea' => validRawIdea(),
        ])
        ->assertOk()
        ->assertJsonPath('mode', 'fallback')
        ->assertJsonPath('diagnostic', 'json_error')
        ->assertJsonPath('notice', \App\Services\ProjectProposalAssistantService::FALLBACK_NOTICE);
});

it('does not send notifications when generating an AI proposal suggestion', function () {
    Config::set('ai.enabled', true);
    Config::set('ai.provider', 'ollama');
    Notification::fake();

    Http::fake([
        'ollama.test/api/chat' => Http::response([
            'message' => [
                'role' => 'assistant',
                'content' => json_encode(ollamaSuggestionPayload()),
            ],
        ], 200),
    ]);

    $student = createAiProposalStudent();

    actingAs($student)
        ->postJson(route('student.ai.proposal'), [
            'raw_idea' => validRawIdea(),
        ])
        ->assertOk();

    Notification::assertNothingSent();
    Notification::assertNotSentTo($student, WorkflowNotification::class);
});

it('leaves the existing idea submission workflow unchanged', function () {
    $student = createAiProposalStudent();
    $supervisorUser = User::factory()->create(['university_number' => 'SUP-AI-002']);
    $supervisorUser->addRole('supervisor');
    $supervisor = Supervisor::create([
        'name' => $supervisorUser->name,
        'email' => $supervisorUser->email,
        'user_id' => $supervisorUser->id,
    ]);

    actingAs($student)
        ->post('/RequstIdea', [
            'projectname' => 'Smart Campus Navigator',
            'count' => 1,
            'supervisor_id' => $supervisor->id,
            'oneid' => $student->university_number,
        ])
        ->assertSessionHas('success');

    expect(Idea::count())->toBe(1);
    assertDatabaseHas('ideas', [
        'projectname' => 'Smart Campus Navigator',
        'requested_by_user_id' => $student->id,
        'supervisor_id' => $supervisor->id,
    ]);
});

it('shows the AI proposal assistant on the discovery New Idea tab', function () {
    $student = createAiProposalStudent();

    actingAs($student)
        ->get('/StudentDashboard')
        ->assertOk()
        ->assertSee('Project Proposal Assistant')
        ->assertSee('Generate suggestion')
        ->assertSee('Apply AI Suggestion')
        ->assertSee(route('student.ai.proposal'), false);
});

it('raises max_execution_time on the AI endpoint for Herd web PHP vs CLI', function () {
    // Note: Herd/web PHP commonly uses max_execution_time=30; CLI is often 0 (unlimited).
    // AiProposalController::suggest calls set_time_limit(120) so Ollama (25–50s) can finish.
    $previousLimit = (int) ini_get('max_execution_time');
    if ($previousLimit === 0 || $previousLimit > 30) {
        set_time_limit(30);
    }

    Config::set('ai.enabled', true);
    Config::set('ai.provider', 'ollama');

    Http::fake([
        'ollama.test/api/chat' => Http::response([
            'message' => [
                'role' => 'assistant',
                'content' => json_encode(ollamaSuggestionPayload()),
            ],
        ], 200),
    ]);

    $student = createAiProposalStudent();

    actingAs($student)
        ->postJson(route('student.ai.proposal'), [
            'raw_idea' => validRawIdea(),
        ])
        ->assertOk()
        ->assertJsonPath('mode', 'ai');

    expect((int) ini_get('max_execution_time'))->toBeGreaterThanOrEqual(120);

    if ($previousLimit === 0) {
        set_time_limit(0);
    } else {
        set_time_limit($previousLimit);
    }
});
