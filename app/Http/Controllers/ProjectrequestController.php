<?php

namespace App\Http\Controllers;

use App\Models\Idea;
use App\Models\Projectrequest;
use App\Models\Supervisor;
use App\Models\UniProject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ProjectrequestController extends Controller
{
    public function request(Request $request)
    {
        $validated = $request->validate([
            'projectid' => 'required|integer|exists:uni_projects,id',
            'count' => 'required|integer|min:1|max:3',
            'nameone' => 'nullable|string|max:255',
            'oneid' => 'required|string',
            'nametwo' => 'nullable|string|max:255',
            'twoid' => 'nullable|string',
            'namethree' => 'nullable|string|max:255',
            'threeid' => 'nullable|string',
        ], [
            'projectid.exists' => 'The selected project ID does not exist in our records.',
        ]);

        $project = UniProject::findOrFail($validated['projectid']);

        if ($project->taken == 1) {
            return redirect()->back()->with('faild', ' project already taken');
        }

        $memberResult = $this->membersFromUniversityNumbers([
            1 => $validated['oneid'],
            2 => $validated['twoid'] ?? null,
            3 => $validated['threeid'] ?? null,
        ]);

        if (isset($memberResult['error'])) {
            return redirect()->back()->with('faild', $memberResult['error'])->withInput();
        }

        if ($this->teamAlreadyHasAcceptedProject($memberResult['users'])) {
            return redirect()->back()->with('faild2', 'Your already have a project');
        }

        DB::transaction(function () use ($validated, $memberResult) {
            $leader = $memberResult['members'][0]['user'];
            $legacyLeaderId = (int) preg_replace('/\D/', '', $leader->university_number) ?: $leader->id;
            $projectRequest = Projectrequest::create([
                // Temporary compatibility for non-null legacy columns; relational columns are the source of truth.
                'projectid' => $validated['projectid'],
                'nameone' => $leader->name,
                'oneid' => $legacyLeaderId,
                'project_id' => $validated['projectid'],
                'requested_by_user_id' => Auth::id(),
                'count' => count($memberResult['members']),
            ]);

            foreach ($memberResult['members'] as $member) {
                $projectRequest->members()->create([
                    'user_id' => $member['user']->id,
                    'position' => $member['position'],
                ]);
            }
        });

        return redirect()->back()->with('success', 'Your request has been submitted successfully.');
    }

    public function accept()
    {
        $student = Auth::user();
        $requests = Projectrequest::with(['project', 'members.user'])
            ->when($student, fn ($query) => $query->whereHas(
                'members',
                fn ($memberQuery) => $memberQuery->where('user_id', $student->id),
            ), fn ($query) => $query->whereRaw('1 = 0'))
            ->orderByDesc('created_at')
            ->get();

        return view('student.acceptance', ['requests' => $requests]);
    }

    public function idea(Request $request)
    {
        $validated = $request->validate([
            'projectname' => 'required|string',
            'count' => 'required|integer|min:1|max:3',
            'supname' => 'required|string',
            'nameone' => 'nullable|string|max:255',
            'oneid' => 'required|string',
            'nametwo' => 'nullable|string|max:255',
            'twoid' => 'nullable|string',
            'namethree' => 'nullable|string|max:255',
            'threeid' => 'nullable|string',
        ]);

        $supervisor = Supervisor::where('name', $validated['supname'])->first();

        if (! $supervisor) {
            return redirect()->back()->with('faild', 'Selected supervisor was not found.')->withInput();
        }

        $memberResult = $this->membersFromUniversityNumbers([
            1 => $validated['oneid'],
            2 => $validated['twoid'] ?? null,
            3 => $validated['threeid'] ?? null,
        ]);

        if (isset($memberResult['error'])) {
            return redirect()->back()->with('faild', $memberResult['error'])->withInput();
        }

        if ($this->teamAlreadyHasAcceptedProject($memberResult['users'])) {
            return redirect()->back()->with('faild2', 'Your already have a project');
        }

        DB::transaction(function () use ($validated, $supervisor, $memberResult) {
            $leader = $memberResult['members'][0]['user'];
            $legacyLeaderId = (int) preg_replace('/\D/', '', $leader->university_number) ?: $leader->id;
            $idea = Idea::create([
                // Temporary compatibility for non-null legacy columns; relational columns are the source of truth.
                'supname' => $supervisor->name,
                'nameone' => $leader->name,
                'oneid' => $legacyLeaderId,
                'projectname' => $validated['projectname'],
                'supervisor_id' => $supervisor->id,
                'requested_by_user_id' => Auth::id(),
                'count' => count($memberResult['members']),
            ]);

            foreach ($memberResult['members'] as $member) {
                $idea->members()->create([
                    'user_id' => $member['user']->id,
                    'position' => $member['position'],
                ]);
            }
        });

        return redirect()->back()->with('success', 'Your request has been submitted successfully.');
    }

    public function acceptidea()
    {
        $student = Auth::user();
        $requests = Idea::with(['supervisor', 'members.user'])
            ->when($student, fn ($query) => $query->whereHas(
                'members',
                fn ($memberQuery) => $memberQuery->where('user_id', $student->id),
            ), fn ($query) => $query->whereRaw('1 = 0'))
            ->orderByDesc('created_at')
            ->get();

        return view('student.acceptanceidea', ['requests' => $requests]);
    }

    /**
     * @param  array<int, User>  $students
     */
    private function teamAlreadyHasAcceptedProject(array $students): bool
    {
        foreach ($students as $student) {
            $hasAcceptedRequest = Projectrequest::query()
                ->where('accepted', 1)
                ->whereHas('members', fn ($query) => $query->where('user_id', $student->id))
                ->exists();

            if ($hasAcceptedRequest) {
                return true;
            }

            $enrolledOnProject = $student
                ? $student->projectMemberships()->exists()
                : false;

            if ($enrolledOnProject) {
                return true;
            }
        }

        return false;
    }

    /**
     * Temporary compatibility for idea requests until idea membership is normalized.
     *
     * @param  array<int, int|string|null>  $studentIds
     */
    private function legacyTeamAlreadyHasAcceptedProject(array $studentIds): bool
    {
        foreach (array_filter($studentIds) as $studentId) {
            $hasAcceptedLegacyRequest = Projectrequest::query()
                ->where('accepted', 1)
                ->where(function ($query) use ($studentId) {
                    $query->where('oneid', $studentId)
                        ->orWhere('twoid', $studentId)
                        ->orWhere('threeid', $studentId);
                })
                ->exists();

            if ($hasAcceptedLegacyRequest) {
                return true;
            }

            $student = User::where('university_number', (string) $studentId)->first();

            if ($student && $student->projectMemberships()->exists()) {
                return true;
            }
        }

        return false;
    }

    private function membersFromUniversityNumbers(array $slots): array
    {
        $members = [];
        $users = [];
        $seenUserIds = [];

        foreach ($slots as $position => $universityNumber) {
            if (blank($universityNumber)) {
                continue;
            }

            $user = User::where('university_number', (string) $universityNumber)->first();

            if (! $user) {
                return ['error' => "No student account found for university number {$universityNumber}."];
            }

            if (in_array($user->id, $seenUserIds, true)) {
                return ['error' => "Student {$universityNumber} was selected more than once."];
            }

            $seenUserIds[] = $user->id;
            $users[] = $user;
            $members[] = [
                'user' => $user,
                'position' => $position,
            ];
        }

        return [
            'members' => $members,
            'users' => $users,
        ];
    }
}
