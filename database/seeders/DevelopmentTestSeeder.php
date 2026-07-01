<?php

namespace Database\Seeders;

use App\Models\Supervisor;
use App\Models\UniProject;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Laratrust\Models\Role;

class DevelopmentTestSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [];
        $availableProject = null;

        DB::transaction(function () use (&$accounts, &$availableProject) {
            $this->ensureRoles();

            $admin = $this->user(
                'System Admin',
                'ADM-0001',
                'admin@test.local',
                'admin',
            );

            $supervisorUser = $this->user(
                'Dr. Lina Haddad',
                'SUP-2026-001',
                'lina.haddad@university.test',
                'supervisor',
            );

            $students = new Collection([
                $this->user(
                    'Omar Ahmad',
                    'STU-2026-001',
                    'omar@test.local',
                    'student',
                ),
                $this->user(
                    'Ali Hasan',
                    'STU-2026-002',
                    'ali@test.local',
                    'student',
                ),
                $this->user(
                    'Sara Khaled',
                    'STU-2026-003',
                    'sara@test.local',
                    'student',
                ),
            ]);

            $supervisor = Supervisor::updateOrCreate(
                ['user_id' => $supervisorUser->id],
                [
                    'name' => $supervisorUser->name,
                    'email' => $supervisorUser->email,
                ],
            );

            $this->resetStudentWorkflowState($students);

            $availableProject = $supervisor->UniProjects()->updateOrCreate(
                ['name' => 'Phase 7 Idea Normalization Starter Project'],
                [
                    'description' => 'Available development project supervised by Dr. Lina Haddad for testing Phase 7 idea normalization workflows.',
                    'department' => 'software',
                    'taken' => false,
                    'student_count' => 0,
                    'student_one_name' => null,
                    'student_one_id' => null,
                    'student_two_name' => null,
                    'student_two_id' => null,
                    'student_three_name' => null,
                    'student_three_id' => null,
                    'seminar_1' => null,
                    'seminar_2' => null,
                    'seminar_3' => null,
                    'final' => null,
                    'status' => 'available',
                ],
            );
            $availableProject->members()->delete();

            $accounts = [
                ['role' => 'admin', 'user' => $admin],
                ['role' => 'supervisor', 'user' => $supervisorUser],
                ...$students->map(fn (User $student) => ['role' => 'student', 'user' => $student])->all(),
            ];
        });

        $this->printDevelopmentSummary($accounts, $availableProject);
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
        $this->releaseConflictingUser($email, $universityNumber);

        $user = User::where('university_number', $universityNumber)
            ->orWhere('email', $email)
            ->first() ?? new User();

        $user->fill([
            'name' => $name,
            'university_number' => $universityNumber,
            'email' => $email,
            'password' => Hash::make('password'),
        ]);
        $user->save();

        DB::table('role_user')
            ->where('user_id', $user->id)
            ->where('user_type', User::class)
            ->delete();

        $user->addRole($role);

        return $user->fresh();
    }

    private function releaseConflictingUser(string $email, string $universityNumber): void
    {
        $emailOwner = User::where('email', $email)->first();
        $numberOwner = User::where('university_number', $universityNumber)->first();

        if ($emailOwner && $numberOwner && $emailOwner->id !== $numberOwner->id) {
            $emailOwner->forceFill([
                'email' => "archived-dev-user-{$emailOwner->id}@test.local",
                'university_number' => null,
            ])->save();
        }
    }

    private function resetStudentWorkflowState(Collection $students): void
    {
        $studentIds = $students->pluck('id')->all();
        $studentNames = $students->pluck('name')->all();

        foreach (['project_members', 'project_request_members', 'idea_members'] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->whereIn('user_id', $studentIds)->delete();
            }
        }

        if (Schema::hasTable('projectrequests')) {
            DB::table('projectrequests')
                ->whereIn('requested_by_user_id', $studentIds)
                ->delete();
        }

        if (Schema::hasTable('ideas')) {
            DB::table('ideas')
                ->whereIn('requested_by_user_id', $studentIds)
                ->delete();
        }

        foreach ([
            ['student_one_name', 'student_one_id'],
            ['student_two_name', 'student_two_id'],
            ['student_three_name', 'student_three_id'],
        ] as [$nameColumn, $idColumn]) {
            DB::table('uni_projects')
                ->whereIn($nameColumn, $studentNames)
                ->update([
                    $nameColumn => null,
                    $idColumn => null,
                    'updated_at' => now(),
                ]);
        }
    }

    private function printDevelopmentSummary(array $accounts, ?UniProject $availableProject): void
    {
        if (! $this->command) {
            return;
        }

        $this->command->newLine();
        $this->command->info('Development test accounts');

        foreach ($accounts as $account) {
            /** @var User $user */
            $user = $account['user'];
            $this->command->line(sprintf(
                '- %s: %s | %s | %s | password',
                ucfirst($account['role']),
                $user->name,
                $user->university_number,
                $user->email,
            ));
        }

        if ($availableProject) {
            $this->command->newLine();
            $this->command->info('Available seeded project');
            $this->command->line(sprintf(
                '- %s | Supervisor: Dr. Lina Haddad | taken=false | project_members=0',
                $availableProject->name,
            ));
        }

        $this->command->newLine();
        $this->command->info('Expected Phase 7 manual workflow');
        $this->command->line('1. Log in as a student, for example omar@test.local / password.');
        $this->command->line('2. Submit a new project idea and add any desired team members.');
        $this->command->line('3. Confirm the idea appears in the student idea history.');
        $this->command->line('4. Log in as Dr. Lina Haddad: lina.haddad@university.test / password.');
        $this->command->line('5. Confirm the supervisor can see the submitted idea.');
        $this->command->line('6. Accept the idea from the supervisor dashboard.');
        $this->command->line('7. Confirm a new graduation project is created.');
        $this->command->line('8. Confirm project_members are created from idea_members.');
        $this->command->line('9. Log in as the student again and confirm the new project appears on the dashboard.');
    }
}
