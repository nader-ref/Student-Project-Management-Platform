<?php

namespace Database\Seeders;

use App\Models\contact;
use App\Models\idea;
use App\Models\projectrequest;
use App\Models\ProjectSubmission;
use App\Models\Supervisor;
use App\Models\UniProject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Laratrust\Models\Role;

class DemoSeeder extends Seeder
{
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

            $supervisor = Supervisor::updateOrCreate(
                ['user_id' => $supervisorUser->id],
                [
                    'name' => $supervisorUser->name,
                    'email' => $supervisorUser->email,
                ],
            );

            $availableProjectOne = $this->seedAvailableProject(
                $supervisor,
                'Smart Campus Portal',
                'Browser-smoke-test project for pending join requests.',
            );

            $availableProjectTwo = $this->seedAvailableProject(
                $supervisor,
                'IoT Lab Monitor',
                'Second available supervisor project for manual dashboard checks.',
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
                ],
            );

            $activeProject->members()->delete();
            $activeProject->members()->create(['user_id' => $student->id, 'position' => 1]);
            $activeProject->members()->create(['user_id' => $teamMember->id, 'position' => 2]);

            $pendingRequest = projectrequest::updateOrCreate(
                [
                    'project_id' => $availableProjectOne->id,
                    'requested_by_user_id' => $student->id,
                ],
                [
                    'count' => 1,
                    'accepted' => false,
                    'rejected' => false,
                    'reason' => null,
                ],
            );
            $pendingRequest->members()->delete();
            $pendingRequest->members()->create(['user_id' => $student->id, 'position' => 1]);

            $pendingIdea = idea::updateOrCreate(
                [
                    'supervisor_id' => $supervisor->id,
                    'requested_by_user_id' => $student->id,
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
            $pendingIdea->members()->create(['user_id' => $student->id, 'position' => 1]);

            contact::updateOrCreate(
                [
                    'student_user_id' => $student->id,
                    'supervisor_id' => $supervisor->id,
                    'subject' => 'Seminar one requirements',
                ],
                [
                    'Message' => 'Could you confirm the deliverables for seminar one?',
                    'Replay' => null,
                ],
            );

            ProjectSubmission::updateOrCreate(
                [
                    'project_id' => $activeProject->id,
                    'submitted_by_user_id' => $student->id,
                    'milestone' => 'seminar_1',
                    'title' => 'Seminar One Proposal',
                ],
                [
                    'file_path' => 'submissions/demo/mobile-attendance-seminar-one.txt',
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
                $availableProjectOne,
                $availableProjectTwo,
                $activeProject,
                $pendingRequest,
                $pendingIdea,
            );
        });
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

    private function seedAvailableProject(Supervisor $supervisor, string $name, string $description): UniProject
    {
        $project = UniProject::updateOrCreate(
            ['name' => $name],
            [
                'description' => $description,
                'supervisor_id' => $supervisor->id,
                'department' => 'software',
                'taken' => false,
                'student_count' => 0,
                'status' => 'available',
            ],
        );

        $project->members()->delete();

        return $project;
    }

    private function printSummary(
        User $admin,
        User $supervisorUser,
        User $student,
        User $teamMember,
        UniProject $availableProjectOne,
        UniProject $availableProjectTwo,
        UniProject $activeProject,
        projectrequest $pendingRequest,
        idea $pendingIdea,
    ): void {
        if (! $this->command) {
            return;
        }

        $this->command->newLine();
        $this->command->info('Demo smoke-test accounts (password for all: password)');

        foreach ([
            ['Admin', $admin],
            ['Supervisor', $supervisorUser],
            ['Student', $student],
            ['Team member student', $teamMember],
        ] as [$label, $user]) {
            $this->command->line(sprintf(
                '- %s: %s | university_number=%s | %s',
                $label,
                $user->name,
                $user->university_number,
                $user->email,
            ));
        }

        $this->command->newLine();
        $this->command->info('Seeded projects');
        $this->command->line("- Available: {$availableProjectOne->name} (id {$availableProjectOne->id})");
        $this->command->line("- Available: {$availableProjectTwo->name} (id {$availableProjectTwo->id})");
        $this->command->line("- Active with members: {$activeProject->name} (id {$activeProject->id})");

        $this->command->newLine();
        $this->command->info('Seeded workflow data');
        $this->command->line("- Pending project request REQ-".str_pad((string) $pendingRequest->id, 4, '0', STR_PAD_LEFT)." on {$availableProjectOne->name}");
        $this->command->line("- Pending idea IDEA-".str_pad((string) $pendingIdea->id, 4, '0', STR_PAD_LEFT).": {$pendingIdea->projectname}");
        $this->command->line('- Student message: Seminar one requirements');
        $this->command->line('- Submission: Seminar One Proposal on Mobile Attendance System');
    }
}
