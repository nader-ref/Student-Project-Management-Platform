<?php

namespace Database\Seeders;

use App\Models\contact;
use App\Models\idea;
use App\Models\ProjectSubmission;
use App\Models\projectrequest;
use App\Models\Supervisor;
use App\Models\UniProject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laratrust\Models\Role;

class DevelopmentTestSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->ensureRoles();

            $admin = $this->user(
                'System Administrator',
                'ADM-0001',
                'admin@test.local',
                'admin',
            );

            $supervisorUser = $this->user(
                'Dr. Lina Haddad',
                'SUP-2026-001',
                'lina.haddad@test.local',
                'supervisor',
            );

            $student = $this->user(
                'Ahmad Al Ali',
                'STU-2026-001',
                'ahmad@test.local',
                'student',
            );

            $supervisor = Supervisor::updateOrCreate(
                ['user_id' => $supervisorUser->id],
                [
                    'name' => $supervisorUser->name,
                    'email' => $supervisorUser->email,
                ],
            );

            $studentLegacyNumber = 2026001;

            $project = $supervisor->UniProjects()->updateOrCreate(
                ['name' => 'AI-Assisted Graduation Project Tracker'],
                [
                    'description' => 'A sample graduation project for tracking milestones, submissions, and supervisor feedback.',
                    'department' => 'software',
                    'taken' => true,
                    'student_count' => 1,
                    'seminar_1' => now()->addWeeks(2)->format('Ymd'),
                    'seminar_2' => now()->addWeeks(6)->format('Ymd'),
                    'seminar_3' => now()->addWeeks(10)->format('Ymd'),
                    'final' => now()->addWeeks(14)->format('Ymd'),
                    'status' => 'active',
                ],
            );

            $project->members()->updateOrCreate(
                ['user_id' => $student->id],
                ['position' => 1],
            );

            projectrequest::updateOrCreate(
                [
                    'projectid' => $project->id,
                    'oneid' => $studentLegacyNumber,
                ],
                [
                    'count' => 1,
                    'nameone' => $student->name,
                    'nametwo' => null,
                    'twoid' => null,
                    'namethree' => null,
                    'threeid' => null,
                    'accepted' => true,
                    'rejected' => false,
                    'reason' => null,
                ],
            );

            idea::updateOrCreate(
                [
                    'projectname' => 'Smart Research Progress Assistant',
                    'oneid' => $studentLegacyNumber,
                ],
                [
                    'count' => 1,
                    'supname' => $supervisor->name,
                    'nameone' => $student->name,
                    'nametwo' => null,
                    'twoid' => null,
                    'namethree' => null,
                    'threeid' => null,
                    'accepted' => false,
                    'rejected' => false,
                    'reason' => null,
                ],
            );

            contact::updateOrCreate(
                [
                    'email' => $student->email,
                    'supname' => $supervisor->name,
                    'subject' => 'Project kickoff question',
                ],
                [
                    'Message' => 'Hello Dr. Lina, could we schedule a short kickoff meeting for the project?',
                    'Replay' => 'Yes, please prepare your initial project outline before the meeting.',
                ],
            );

            $submissionPath = 'submissions/development-test/initial-proposal.txt';
            Storage::disk('public')->put(
                $submissionPath,
                "Development sample submission for {$project->name}.\n",
            );

            ProjectSubmission::updateOrCreate(
                [
                    'project_id' => $project->id,
                    'student_email' => $student->email,
                    'milestone' => 'seminar_1',
                    'title' => 'Initial Project Proposal',
                ],
                [
                    'student_name' => $student->name,
                    'file_path' => $submissionPath,
                    'original_filename' => 'initial-proposal.txt',
                    'notes' => 'Sample development submission created by DevelopmentTestSeeder.',
                    'status' => 'submitted',
                    'supervisor_feedback' => null,
                ],
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

    private function user(string $name, string $universityNumber, string $email, string $role): User
    {
        $user = User::updateOrCreate(
            ['university_number' => $universityNumber],
            [
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('password'),
            ],
        );

        if (! $user->hasRole($role)) {
            $user->addRole($role);
        }

        return $user->fresh();
    }
}
