<?php

namespace Database\Seeders;

use App\Models\idea;
use App\Models\projectrequest;
use App\Models\ProjectSubmission;
use App\Models\Supervisor;
use App\Models\UniProject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->call([
                DemoUsersSeeder::class,
                DemoProjectsSeeder::class,
                DemoWorkflowSeeder::class,
                DemoSubmissionsSeeder::class,
            ]);
        });

        $this->printSummary();
    }

    private function printSummary(): void
    {
        if (! $this->command) {
            return;
        }

        $students = User::whereHas('roles', fn ($q) => $q->where('name', 'student'))->count();
        $supervisors = Supervisor::count();
        $admins = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->count();
        $projects = UniProject::count();
        $available = UniProject::where('taken', false)->count();
        $taken = UniProject::where('taken', true)->count();
        $pendingRequests = projectrequest::where('accepted', false)
            ->where(fn ($q) => $q->where('rejected', false)->orWhereNull('rejected'))
            ->count();
        $acceptedIdeas = idea::where('accepted', true)->where('rejected', false)->count();
        $pendingIdeas = idea::where('accepted', false)->where('rejected', false)->count();
        $rejectedIdeas = idea::where('rejected', true)->count();
        $submissions = ProjectSubmission::count();

        $this->command->newLine();
        $this->command->info('Demo smoke-test accounts (password for all: password)');

        foreach ([
            ['Admin', '100000', 'Admin dashboard and provisioning'],
            ['Secondary admin', '100001', 'Additional admin account'],
            ['Supervisor', '200000', 'Canonical supervisor for demo request/submission flows'],
            ['Student (enrolled lead)', '300000', 'Mobile Attendance System workspace + seeded submission'],
            ['Team member student', '300001', 'Enrolled teammate on Mobile Attendance System'],
            ['Discovery student', '300002', 'Pending request ONLY on Smart Campus Portal'],
            ['AI demo student', '300003', 'Free discovery — use for Proposal Assistant + Similarity Check'],
        ] as [$label, $uni, $workflow]) {
            $user = User::where('university_number', $uni)->first();
            if (! $user) {
                continue;
            }
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
        $this->command->info('Seeded dataset counts');
        $this->command->line("- Admins: {$admins}");
        $this->command->line("- Supervisors: {$supervisors}");
        $this->command->line("- Students: {$students}");
        $this->command->line("- Projects: {$projects} (available {$available}, taken {$taken})");
        $this->command->line("- Pending requests: {$pendingRequests}");
        $this->command->line("- Ideas: accepted {$acceptedIdeas}, pending {$pendingIdeas}, rejected {$rejectedIdeas}");
        $this->command->line("- Submissions: {$submissions}");

        $this->command->newLine();
        $this->command->info('AI / similarity demo');
        $this->command->line('- Login as 300003 (AI Demo Student) for Proposal Assistant and Similarity Check.');
        $this->command->line('- Similarity corpus: all uni_projects + accepted ideas with proposal_description.');
        $this->command->line('- 300002 retains pending request workflow only (no pending idea).');
    }
}
