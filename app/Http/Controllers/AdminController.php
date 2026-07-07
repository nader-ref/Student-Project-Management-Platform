<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\idea;
use App\Models\projectrequest;
use App\Models\ProjectSubmission;
use App\Models\Supervisor;
use App\Models\UniProject;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\StudentEnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laratrust\Models\Role;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'stats' => [
                'totalUsers' => User::count(),
                'totalStudents' => $this->countUsersWithRole('student'),
                'totalSupervisors' => $this->countUsersWithRole('supervisor'),
                'totalProjects' => UniProject::count(),
                'totalSubmissions' => ProjectSubmission::count(),
                'pendingRequests' => $this->countPendingRequests(),
                'pendingIdeas' => $this->countPendingIdeas(),
                'pendingEmailUsers' => User::whereNull('email')->count(),
                'availableProjects' => UniProject::where('taken', false)->count(),
                'takenProjects' => UniProject::where('taken', true)->count(),
            ],
            'workflowSummary' => [
                'requests' => [
                    'pending' => $this->countPendingRequests(),
                    'accepted' => $this->countAcceptedRequests(),
                    'rejected' => $this->countRejectedRequests(),
                ],
                'ideas' => [
                    'pending' => $this->countPendingIdeas(),
                    'accepted' => $this->countAcceptedIdeas(),
                    'rejected' => $this->countRejectedIdeas(),
                ],
            ],
            'submissionSummary' => $this->submissionStatusCounts(),
            'supervisorWorkload' => $this->supervisorProjectSummary(),
            'latestUsers' => $this->userSummaries(
                User::query()->latest()->take(5)->get()
            ),
        ]);
    }

    public function users()
    {
        return view('admin.users', [
            'users' => $this->userSummaries(
                User::query()->orderBy('name')->get()
            ),
        ]);
    }

    public function deactivateUser(User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()
                ->route('admin.users')
                ->withErrors(['user' => 'You cannot deactivate your own account.']);
        }

        if ($this->isLastActiveAdmin($user)) {
            return redirect()
                ->route('admin.users')
                ->withErrors(['user' => 'You cannot deactivate the last active admin account.']);
        }

        $user->update(['is_active' => false]);

        ActivityLogger::log(
            ActivityLogger::USER_DEACTIVATED,
            "Deactivated account for {$user->name}",
            targetUser: $user,
            subject: $user,
        );

        return redirect()
            ->route('admin.users')
            ->with('success', 'User account deactivated successfully.');
    }

    public function activateUser(User $user)
    {
        $user->update(['is_active' => true]);

        ActivityLogger::log(
            ActivityLogger::USER_ACTIVATED,
            "Activated account for {$user->name}",
            targetUser: $user,
            subject: $user,
        );

        return redirect()
            ->route('admin.users')
            ->with('success', 'User account activated successfully.');
    }

    public function showResetPassword(User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()
                ->route('admin.users')
                ->withErrors(['user' => 'You cannot reset your own password through this page.']);
        }

        return view('admin.users.reset-password', [
            'user' => $this->userSummaries(collect([$user]))->first(),
        ]);
    }

    public function resetUserPassword(Request $request, User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()
                ->route('admin.users')
                ->withErrors(['user' => 'You cannot reset your own password through this page.']);
        }

        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->update(['password' => $validated['password']]);

        ActivityLogger::log(
            ActivityLogger::USER_PASSWORD_RESET,
            "Reset password for {$user->name}",
            targetUser: $user,
            subject: $user,
            metadata: [
                'university_number' => $user->university_number,
            ],
        );

        return redirect()
            ->route('admin.users')
            ->with('success', 'Password reset successfully. Share the temporary password securely with the user.');
    }

    public function projects()
    {
        $projects = UniProject::query()
            ->with('supervisor')
            ->withCount('members')
            ->latest()
            ->get()
            ->map(fn (UniProject $project) => [
                'name' => $project->name,
                'supervisor' => $project->supervisor?->name ?? '—',
                'department' => $project->department ?? '—',
                'status' => $project->lifecycleLabel(),
                'member_count' => $project->members_count,
                'seminar_1' => $project->seminar_1,
                'seminar_2' => $project->seminar_2,
                'seminar_3' => $project->seminar_3,
                'final' => $project->final,
                'created_at' => $project->created_at,
            ]);

        return view('admin.projects', [
            'projects' => $projects,
        ]);
    }

    public function requests()
    {
        $requests = projectrequest::query()
            ->with(['project', 'requester', 'students'])
            ->latest()
            ->get()
            ->map(fn (projectrequest $request) => [
                'code' => 'REQ-'.str_pad((string) $request->id, 4, '0', STR_PAD_LEFT),
                'project' => $request->project?->name ?? '—',
                'requester' => $request->requester?->name ?? '—',
                'members' => $request->students->pluck('name')->filter()->implode(', ') ?: '—',
                'status' => $this->workflowStatus($request->accepted, $request->rejected),
                'created_at' => $request->created_at,
            ]);

        return view('admin.requests', [
            'requests' => $requests,
        ]);
    }

    public function ideas()
    {
        $ideas = idea::query()
            ->with(['supervisor', 'requester', 'students'])
            ->latest()
            ->get()
            ->map(fn (idea $idea) => [
                'title' => $idea->projectname,
                'supervisor' => $idea->supervisor?->name ?? '—',
                'requester' => $idea->requester?->name ?? '—',
                'members' => $idea->students->pluck('name')->filter()->implode(', ') ?: '—',
                'status' => $this->workflowStatus($idea->accepted, $idea->rejected),
                'created_at' => $idea->created_at,
            ]);

        return view('admin.ideas', [
            'ideas' => $ideas,
        ]);
    }

    public function submissions()
    {
        $milestoneLabels = StudentEnrollmentService::milestoneLabels();

        $submissions = ProjectSubmission::query()
            ->with(['project', 'submittedBy'])
            ->latest()
            ->get()
            ->map(fn (ProjectSubmission $submission) => [
                'title' => $submission->title,
                'project' => $submission->project?->name ?? '—',
                'submitter' => $submission->submittedBy?->name ?? '—',
                'milestone' => $milestoneLabels[$submission->milestone] ?? $submission->milestone,
                'status' => $this->submissionStatusLabel($submission->status),
                'submitted_at' => $submission->created_at,
            ]);

        return view('admin.submissions', [
            'submissions' => $submissions,
        ]);
    }

    public function createSupervisor()
    {
        return view('admin.supervisors.create');
    }

    public function storeSupervisor(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'university_number' => 'required|string|max:255|unique:users,university_number',
            'email' => 'nullable|string|email|max:255|unique:users,email|unique:supervisors,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'university_number' => $validated['university_number'],
                'email' => $validated['email'] ?? null,
                'password' => $validated['password'],
            ]);

            Role::firstOrCreate(
                ['name' => 'supervisor'],
                ['display_name' => 'Supervisor', 'description' => 'Supervisor role'],
            );

            $user->addRole('supervisor');

            Supervisor::create([
                'name' => $user->name,
                'email' => $user->email ?? null,
                'user_id' => $user->id,
            ]);

            return $user;
        });

        ActivityLogger::log(
            ActivityLogger::USER_SUPERVISOR_CREATED,
            "Created supervisor account for {$user->name}",
            targetUser: $user,
            subject: $user,
            metadata: array_filter([
                'university_number' => $user->university_number,
                'email' => $user->email,
                'role' => 'supervisor',
            ]),
        );

        return redirect()
            ->route('admin.users')
            ->with('success', 'Supervisor account created successfully.');
    }

    public function createStudent()
    {
        return view('admin.students.create');
    }

    public function storeStudent(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'university_number' => 'required|string|max:255|unique:users,university_number',
            'email' => 'nullable|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'university_number' => $validated['university_number'],
                'email' => $validated['email'] ?? null,
                'password' => $validated['password'],
            ]);

            Role::firstOrCreate(
                ['name' => 'student'],
                ['display_name' => 'Student', 'description' => 'Student role'],
            );

            $user->addRole('student');

            return $user;
        });

        ActivityLogger::log(
            ActivityLogger::USER_STUDENT_CREATED,
            "Created student account for {$user->name}",
            targetUser: $user,
            subject: $user,
            metadata: array_filter([
                'university_number' => $user->university_number,
                'email' => $user->email,
                'role' => 'student',
            ]),
        );

        return redirect()
            ->route('admin.users')
            ->with('success', 'Student account created successfully.');
    }

    public function activity()
    {
        $logs = ActivityLog::query()
            ->with(['actor', 'targetUser'])
            ->latest('created_at')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.activity', [
            'logs' => $logs,
        ]);
    }

    private function countUsersWithRole(string $role): int
    {
        return DB::table('role_user')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('roles.name', $role)
            ->count();
    }

    private function countPendingRequests(): int
    {
        return projectrequest::query()
            ->where('accepted', false)
            ->where('rejected', false)
            ->count();
    }

    private function countPendingIdeas(): int
    {
        return idea::query()
            ->where('accepted', false)
            ->where('rejected', false)
            ->count();
    }

    private function countAcceptedRequests(): int
    {
        return projectrequest::query()
            ->where('accepted', true)
            ->count();
    }

    private function countRejectedRequests(): int
    {
        return projectrequest::query()
            ->where('rejected', true)
            ->count();
    }

    private function countAcceptedIdeas(): int
    {
        return idea::query()
            ->where('accepted', true)
            ->count();
    }

    private function countRejectedIdeas(): int
    {
        return idea::query()
            ->where('rejected', true)
            ->count();
    }

    private function submissionStatusCounts(): array
    {
        $counts = ProjectSubmission::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return collect(['submitted', 'approved', 'needs_revision'])
            ->map(fn (string $status) => [
                'label' => $this->submissionStatusLabel($status),
                'count' => (int) ($counts[$status] ?? 0),
            ])
            ->all();
    }

    private function supervisorProjectSummary(): array
    {
        $projectCounts = UniProject::query()
            ->selectRaw('supervisor_id, COUNT(*) as total, SUM(CASE WHEN taken = 1 THEN 1 ELSE 0 END) as taken')
            ->groupBy('supervisor_id')
            ->get()
            ->keyBy('supervisor_id');

        $summary = Supervisor::query()
            ->orderBy('name')
            ->get()
            ->map(function (Supervisor $supervisor) use ($projectCounts) {
                $counts = $projectCounts->get($supervisor->id);
                $total = (int) ($counts->total ?? 0);
                $taken = (int) ($counts->taken ?? 0);

                return [
                    'name' => $supervisor->name,
                    'total' => $total,
                    'taken' => $taken,
                    'available' => $total - $taken,
                ];
            })
            ->sortByDesc('total')
            ->values();

        if ($summary->count() > 5) {
            return $summary->take(5)->all();
        }

        return $summary->all();
    }

    private function workflowStatus(mixed $accepted, mixed $rejected): string
    {
        if ($accepted == 1) {
            return 'Accepted';
        }

        if ($rejected == 1) {
            return 'Rejected';
        }

        return 'Pending';
    }

    private function submissionStatusLabel(?string $status): string
    {
        return match ($status) {
            'approved' => 'Approved',
            'needs_revision' => 'Revision Required',
            'submitted' => 'Pending Review',
            default => ucfirst(str_replace('_', ' ', (string) $status)),
        };
    }

    private function userSummaries($users)
    {
        $userIds = $users->pluck('id');
        $activeAdminCount = $this->countActiveAdmins();

        $rolesByUserId = DB::table('role_user')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->whereIn('role_user.user_id', $userIds)
            ->select('role_user.user_id', 'roles.name')
            ->get()
            ->groupBy('user_id')
            ->map(fn ($roles) => $roles->pluck('name')->implode(', '));

        return $users->map(function (User $user) use ($rolesByUserId, $activeAdminCount) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'university_number' => $user->university_number,
                'email' => $user->email,
                'email_status' => $this->emailStatus($user),
                'role' => $rolesByUserId->get($user->id) ?? 'No role',
                'status' => $this->accountStatus($user),
                'is_active' => $user->isActive(),
                'can_deactivate' => $user->isActive()
                    && $user->id !== Auth::id()
                    && ! ($user->hasRole('admin') && $activeAdminCount <= 1),
                'created_at' => $user->created_at,
            ];
        });
    }

    private function emailStatus(User $user): string
    {
        return $user->email ? 'Complete' : 'Pending';
    }

    private function accountStatus(User $user): string
    {
        return $user->isActive() ? 'Active' : 'Inactive';
    }

    private function countActiveAdmins(): int
    {
        return DB::table('role_user')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->join('users', 'users.id', '=', 'role_user.user_id')
            ->where('roles.name', 'admin')
            ->where('users.is_active', true)
            ->count();
    }

    private function isLastActiveAdmin(User $user): bool
    {
        return $user->hasRole('admin') && $this->countActiveAdmins() <= 1;
    }
}
