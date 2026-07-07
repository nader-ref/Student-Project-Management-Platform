<?php

use App\Models\ProjectMember;
use App\Models\ProjectSubmission;
use App\Models\Supervisor;
use App\Models\UniProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laratrust\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    foreach (['student', 'supervisor'] as $roleName) {
        Role::firstOrCreate(
            ['name' => $roleName],
            ['display_name' => ucfirst($roleName), 'description' => $roleName.' role'],
        );
    }

    Storage::fake('local');
});

it('stores uploaded submissions on the private local disk', function () {
    [$student, $project] = createSubmissionSecurityFixture();

    $this->actingAs($student)
        ->post('/student/submission', [
            'milestone' => 'seminar_1',
            'title' => 'Private Storage Report',
            'file' => UploadedFile::fake()->create('private.pdf', 100, 'application/pdf'),
        ])
        ->assertSessionHas('success');

    $submission = ProjectSubmission::where('title', 'Private Storage Report')->firstOrFail();

    expect(Storage::disk('local')->exists($submission->file_path))->toBeTrue();
    expect(Storage::disk('public')->exists($submission->file_path))->toBeFalse();
});

it('allows authorized project members to download through the controller', function () {
    [$student, $project, , $supervisorUser] = createSubmissionSecurityFixture();
    $submission = createSubmissionSecurityRecord($project, $student);

    $this->actingAs($student)
        ->get(route('student.submission.download', $submission))
        ->assertOk();
});

it('prevents non-enrolled students from downloading submissions', function () {
    [, $project] = createSubmissionSecurityFixture();
    /** @var \App\Models\User $otherStudent */
    $otherStudent = User::factory()->create(['university_number' => 'FILE-STU-OTHER']);
    $otherStudent->addRole('student');

    $submission = createSubmissionSecurityRecord($project, User::factory()->create());

    $this->actingAs($otherStudent)
        ->get(route('student.submission.download', $submission))
        ->assertForbidden();
});

it('prevents supervisors from downloading submissions outside their projects', function () {
    [$student, $project, , $supervisorUser] = createSubmissionSecurityFixture();
    [, , , $otherSupervisorUser] = createSubmissionSecurityFixture('other');
    $submission = createSubmissionSecurityRecord($project, $student);

    $this->actingAs($otherSupervisorUser)
        ->get(route('supervisor.submission.download', $submission))
        ->assertForbidden();

    $this->actingAs($supervisorUser)
        ->get(route('supervisor.submission.download', $submission))
        ->assertOk();
});

it('does not expose direct public storage submission urls in student submission views', function () {
    [$student, $project] = createSubmissionSecurityFixture();
    createSubmissionSecurityRecord($project, $student);

    $response = $this->actingAs($student)->get('/StudentDashboard');

    $response->assertOk();
    $response->assertSee(route('student.submission.download', ProjectSubmission::first()));
    $response->assertDontSee('/storage/submissions/');
});

function createSubmissionSecurityFixture(string $suffix = 'main'): array
{
    $student = User::factory()->create([
        'name' => "File Student {$suffix}",
        'university_number' => "FILE-STU-{$suffix}",
    ]);
    $student->addRole('student');

    $supervisorUser = User::factory()->create([
        'name' => "File Supervisor {$suffix}",
        'university_number' => "FILE-SUP-{$suffix}",
    ]);
    $supervisorUser->addRole('supervisor');

    $supervisor = Supervisor::create([
        'name' => $supervisorUser->name,
        'email' => $supervisorUser->email,
        'user_id' => $supervisorUser->id,
    ]);

    $project = UniProject::create([
        'name' => "File Security Project {$suffix}",
        'description' => 'Project used for submission file security tests.',
        'supervisor_id' => $supervisor->id,
        'department' => 'software',
        'taken' => true,
        'student_count' => 1,
        'seminar_1' => now()->addWeeks(4)->toDateString(),
        'seminar_2' => now()->addWeeks(8)->toDateString(),
        'seminar_3' => now()->addWeeks(12)->toDateString(),
        'final' => now()->addWeeks(16)->toDateString(),
    ]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $student->id,
        'position' => 1,
    ]);

    return [$student, $project, $supervisor, $supervisorUser];
}

function createSubmissionSecurityRecord(UniProject $project, User $student): ProjectSubmission
{
    $path = 'submissions/'.$project->id.'/secured-report.pdf';
    Storage::disk('local')->put($path, 'secured file contents');

    return ProjectSubmission::create([
        'project_id' => $project->id,
        'submitted_by_user_id' => $student->id,
        'milestone' => 'seminar_1',
        'title' => 'Secured Report',
        'file_path' => $path,
        'original_filename' => 'secured-report.pdf',
        'status' => 'submitted',
    ]);
}
