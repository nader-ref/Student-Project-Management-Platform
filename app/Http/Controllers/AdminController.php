<?php

namespace App\Http\Controllers;

use App\Models\idea;
use App\Models\projectrequest;
use App\Models\ProjectSubmission;
use App\Models\Supervisor;
use App\Models\UniProject;
use App\Models\User;
use App\Services\StudentEnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
            ],
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
                'status' => $project->taken ? 'Taken' : 'Available',
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
            'email' => 'required|string|email|max:255|unique:users,email|unique:supervisors,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'university_number' => $validated['university_number'],
                'email' => $validated['email'],
                'password' => $validated['password'],
            ]);

            Role::firstOrCreate(
                ['name' => 'supervisor'],
                ['display_name' => 'Supervisor', 'description' => 'Supervisor role'],
            );

            $user->addRole('supervisor');

            Supervisor::create([
                'name' => $user->name,
                'email' => $user->email,
                'user_id' => $user->id,
            ]);
        });

        return redirect()
            ->route('admin.users')
            ->with('success', 'Supervisor account created successfully.');
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
            'needs_revision' => 'Needs revision',
            'submitted' => 'Submitted',
            default => ucfirst(str_replace('_', ' ', (string) $status)),
        };
    }

    private function userSummaries($users)
    {
        $userIds = $users->pluck('id');

        $rolesByUserId = DB::table('role_user')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->whereIn('role_user.user_id', $userIds)
            ->select('role_user.user_id', 'roles.name')
            ->get()
            ->groupBy('user_id')
            ->map(fn ($roles) => $roles->pluck('name')->implode(', '));

        return $users->map(fn (User $user) => [
            'name' => $user->name,
            'university_number' => $user->university_number,
            'email' => $user->email,
            'role' => $rolesByUserId->get($user->id) ?? 'No role',
            'status' => $this->accountStatus($user),
            'created_at' => $user->created_at,
        ]);
    }

    private function accountStatus(User $user): string
    {
        if (Schema::hasColumn('users', 'is_active')) {
            return $user->is_active ? 'Active' : 'Inactive';
        }

        return 'Active';
    }
}
