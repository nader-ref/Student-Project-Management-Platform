<?php

namespace App\Http\Controllers;

use App\Models\ProjectSubmission;
use App\Models\Supervisor;
use App\Models\UniProject;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

    private function countUsersWithRole(string $role): int
    {
        return DB::table('role_user')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('roles.name', $role)
            ->count();
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
