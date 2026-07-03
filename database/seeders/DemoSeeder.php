<?php

namespace Database\Seeders;

use App\Models\contact;
use App\Models\idea;
use App\Models\projectrequest;
use App\Models\ProjectSubmission;
use App\Models\Supervisor;
use App\Models\supcontact;
use App\Models\UniProject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laratrust\Models\Role;

class DemoSeeder extends Seeder
{
    private const DEMO_SUBMISSION_PATH = 'submissions/demo/mobile-attendance-seminar-one.txt';

    public function run(): void
    {
        DB::transaction(function () {
            $this->ensureRoles();

            $admin = $this->seedUser(
                'Admin User',
                '100000',
                'admin@example.com',
                'admin',
            );

            $supervisorUser = $this->seedUser(
                'Supervisor User',
                '200000',
                'supervisor@example.com',
                'supervisor',
            );

            $student = $this->seedUser(
                'Student User',
                '300000',
                'student@example.com',
                'student',
            );

            $teamMember = $this->seedUser(
                'Team Member Student',
                '300001',
                'student2@example.com',
                'student',
            );

            $discoveryStudent = $this->seedUser(
                'Discovery Student',
                '300002',
                'student3@example.com',
                'student',
            );

            $supervisor = Supervisor::updateOrCreate(
                ['user_id' => $supervisorUser->id],
                [
                    'name' => $supervisorUser->name,
                    'email' => $supervisorUser->email,
                ],
            );

            $catalogMilestoneDates = $this->milestoneDates(weeksFromNow: 4);
            $catalogMilestoneDatesTwo = $this->milestoneDates(weeksFromNow: 8);
            $activeMilestoneDates = $this->milestoneDates(weeksFromNow: 2);

            $availableProjectOne = $this->seedAvailableProject(
                $supervisor,
                'Smart Campus Portal',
                'Browser-smoke-test project for pending join requests.',
                $catalogMilestoneDates,
            );

            $availableProjectTwo = $this->seedAvailableProject(
                $supervisor,
                'IoT Lab Monitor',
                'Second available supervisor project for manual dashboard checks.',
                $catalogMilestoneDatesTwo,
            );

            $activeProject = UniProject::updateOrCreate(
                ['name' => 'Mobile Attendance System'],
                [
                    'description' => 'Active graduation project with enrolled team members.',
                    'supervisor_id' => $supervisor->id,
                    'department' => 'software',
                    'taken' => true,
                    'student_count' => 2,
                    'status' => 'in_progress',
                    ...$activeMilestoneDates,
                ],
            );

            $activeProject->members()->delete();
            $activeProject->members()->create(['user_id' => $student->id, 'position' => 1]);
            $activeProject->members()->create(['user_id' => $teamMember->id, 'position' => 2]);

            projectrequest::query()
                ->where('project_id', $availableProjectOne->id)
                ->where('requested_by_user_id', $student->id)
                ->delete();

            idea::query()
                ->where('supervisor_id', $supervisor->id)
                ->where('requested_by_user_id', $student->id)
                ->where('projectname', 'AI-Powered Study Planner')
                ->delete();

            $pendingRequest = projectrequest::updateOrCreate(
                [
                    'project_id' => $availableProjectOne->id,
                    'requested_by_user_id' => $discoveryStudent->id,
                ],
                [
                    'count' => 1,
                    'accepted' => false,
                    'rejected' => false,
                    'reason' => null,
                ],
            );
            $pendingRequest->members()->delete();
            $pendingRequest->members()->create(['user_id' => $discoveryStudent->id, 'position' => 1]);

            $pendingIdea = idea::updateOrCreate(
                [
                    'supervisor_id' => $supervisor->id,
                    'requested_by_user_id' => $discoveryStudent->id,
                    'projectname' => 'AI-Powered Study Planner',
                ],
                [
                    'count' => 1,
                    'accepted' => false,
                    'rejected' => false,
                    'reason' => null,
                ],
            );
            $pendingIdea->members()->delete();
            $pendingIdea->members()->create(['user_id' => $discoveryStudent->id, 'position' => 1]);

            contact::updateOrCreate(
                [
                    'student_user_id' => $student->id,
                    'supervisor_id' => $supervisor->id,
                    'subject' => 'Seminar one requirements',
                ],
                [
                    'Message' => 'Could you confirm the deliverables for seminar one?',
                    'Replay' => 'Please submit a 5-page proposal covering the problem statement, objectives, methodology, and timeline for Seminar 1.',
                ],
            );

            supcontact::updateOrCreate(
                [
                    'supervisor_id' => $supervisor->id,
                    'project_id' => $activeProject->id,
                    'subject' => 'Seminar 1 deliverables reminder',
                ],
                [
                    'Message' => 'Seminar 1 is approaching. Upload your proposal PDF and a short presentation outline before the scheduled date.',
                ],
            );

            Storage::disk('public')->put(
                self::DEMO_SUBMISSION_PATH,
                implode("\n", [
                    'Mobile Attendance System',
                    'Seminar One Proposal (Demo Seed File)',
                    '',
                    'Problem Statement',
                    'Manual attendance tracking is slow and error-prone for large classes.',
                    '',
                    'Objectives',
                    '- Build a mobile attendance workflow for students and supervisors.',
                    '- Provide milestone tracking for seminar deliverables.',
                    '',
                    'Timeline',
                    '- Seminar 1: proposal and scope confirmation.',
                    '- Seminar 2: prototype demo.',
                    '- Seminar 3: evaluation and testing results.',
                    '- Final: complete system presentation.',
                ]),
            );

            ProjectSubmission::updateOrCreate(
                [
                    'project_id' => $activeProject->id,
                    'submitted_by_user_id' => $student->id,
                    'milestone' => 'seminar_1',
                    'title' => 'Seminar One Proposal',
                ],
                [
                    'file_path' => self::DEMO_SUBMISSION_PATH,
                    'original_filename' => 'seminar-one-proposal.txt',
                    'notes' => 'Demo submission for browser smoke testing.',
                    'status' => 'submitted',
                ],
            );

            $this->printSummary(
                $admin,
                $supervisorUser,
                $student,
                $teamMember,
                $discoveryStudent,
                $availableProjectOne,
                $availableProjectTwo,
                $activeProject,
                $pendingRequest,
                $pendingIdea,
                $catalogMilestoneDates,
                $catalogMilestoneDatesTwo,
                $activeMilestoneDates,
            );
        });
    }

    /**
     * @return array{seminar_1: string, seminar_2: string, seminar_3: string, final: string}
     */
    private function milestoneDates(int $weeksFromNow): array
    {
        $base = now()->addWeeks($weeksFromNow);

        return [
            'seminar_1' => $base->copy()->addWeeks(2)->toDateString(),
            'seminar_2' => $base->copy()->addWeeks(6)->toDateString(),
            'seminar_3' => $base->copy()->addWeeks(10)->toDateString(),
            'final' => $base->copy()->addWeeks(14)->toDateString(),
        ];
    }

    private function ensureRoles(): void
    {
        foreach (['admin', 'supervisor', 'student'] as $roleName) {
            Role::firstOrCreate(
                ['name' => $roleName],
                ['display_name' => ucfirst($roleName), 'description' => $roleName.' role'],
            );
        }
    }

    private function seedUser(string $name, string $universityNumber, string $email, string $role): User
    {
        $user = User::updateOrCreate(
            ['university_number' => $universityNumber],
            [
                'name' => $name,
                'email' => $email,
                'password' => 'password',
            ],
        );

        DB::table('role_user')
            ->where('user_id', $user->id)
            ->where('user_type', User::class)
            ->delete();

        $user->addRole($role);

        return $user->fresh();
    }

    /**
     * @param  array{seminar_1: string, seminar_2: string, seminar_3: string, final: string}  $milestoneDates
     */
    private function seedAvailableProject(
        Supervisor $supervisor,
        string $name,
        string $description,
        array $milestoneDates,
    ): UniProject {
        $project = UniProject::updateOrCreate(
            ['name' => $name],
            [
                'description' => $description,
                'supervisor_id' => $supervisor->id,
                'department' => 'software',
                'taken' => false,
                'student_count' => 0,
                'status' => 'available',
                ...$milestoneDates,
            ],
        );

        $project->members()->delete();

        return $project;
    }

    /**
     * @param  array{seminar_1: string, seminar_2: string, seminar_3: string, final: string}  $catalogMilestoneDates
     * @param  array{seminar_1: string, seminar_2: string, seminar_3: string, final: string}  $catalogMilestoneDatesTwo
     * @param  array{seminar_1: string, seminar_2: string, seminar_3: string, final: string}  $activeMilestoneDates
     */
    private function printSummary(
        User $admin,
        User $supervisorUser,
        User $student,
        User $teamMember,
        User $discoveryStudent,
        UniProject $availableProjectOne,
        UniProject $availableProjectTwo,
        UniProject $activeProject,
        projectrequest $pendingRequest,
        idea $pendingIdea,
        array $catalogMilestoneDates,
        array $catalogMilestoneDatesTwo,
        array $activeMilestoneDates,
    ): void {
        if (! $this->command) {
            return;
        }

        $this->command->newLine();
        $this->command->info('Demo smoke-test accounts (password for all: password)');

        foreach ([
            ['Admin', $admin, 'Admin dashboard metrics and read-only users page'],
            ['Supervisor', $supervisorUser, 'Requests, ideas, messages, submissions, announcements, project management'],
            ['Student (enrolled lead)', $student, 'Enrolled workspace on Mobile Attendance System with timeline, submissions, and replied message'],
            ['Team member student', $teamMember, 'Enrolled teammate view on Mobile Attendance System'],
            ['Discovery student', $discoveryStudent, 'Pending request + pending idea; browse available projects without enrollment'],
        ] as [$label, $user, $workflow]) {
            $this->command->line(sprintf(
                '- %s: %s | university_number=%s | %s',
                $label,
                $user->name,
                $user->university_number,
                $user->email,
            ));
            $this->command->line("  Workflow: {$workflow}");
        }

        $this->command->newLine();
        $this->command->info('Seeded projects and milestone dates');

        foreach ([
            [$availableProjectOne, $catalogMilestoneDates],
            [$availableProjectTwo, $catalogMilestoneDatesTwo],
            [$activeProject, $activeMilestoneDates],
        ] as [$project, $dates]) {
            $this->command->line(sprintf(
                '- %s (id %d): seminar_1=%s | seminar_2=%s | seminar_3=%s | final=%s',
                $project->name,
                $project->id,
                $dates['seminar_1'],
                $dates['seminar_2'],
                $dates['seminar_3'],
                $dates['final'],
            ));
        }

        $this->command->newLine();
        $this->command->info('Seeded workflow data');
        $this->command->line('- Pending project request REQ-'.str_pad((string) $pendingRequest->id, 4, '0', STR_PAD_LEFT)." on {$availableProjectOne->name} by {$discoveryStudent->email}");
        $this->command->line('- Pending idea IDEA-'.str_pad((string) $pendingIdea->id, 4, '0', STR_PAD_LEFT).": {$pendingIdea->projectname} by {$discoveryStudent->email}");
        $this->command->line('- Student message with supervisor reply: Seminar one requirements');
        $this->command->line('- Supervisor announcement: Seminar 1 deliverables reminder on Mobile Attendance System');
        $this->command->line('- Submission: Seminar One Proposal on Mobile Attendance System');
        $this->command->line('- Submission file: storage/app/public/'.self::DEMO_SUBMISSION_PATH);
    }
}
