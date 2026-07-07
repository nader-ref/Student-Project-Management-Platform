<?php

namespace App\Http\Controllers;

use App\Models\Idea;
use App\Models\Projectrequest;
use App\Models\Supervisor;
use App\Models\UniProject;
use App\Models\User;
use App\Notifications\WorkflowNotification;
use App\Services\ActivityLogger;
use App\Services\WorkflowGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ProjectrequestController extends Controller
{
    public function request(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|integer|exists:uni_projects,id',
            'count' => 'required|integer|min:1|max:3',
            'oneid' => 'required|string',
            'twoid' => 'nullable|string',
            'threeid' => 'nullable|string',
        ], [
            'project_id.exists' => 'The selected project does not exist in our records.',
        ]);

        $project = UniProject::findOrFail($validated['project_id']);

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

        if (WorkflowGuard::anyUserHasPendingApplication(WorkflowGuard::userIdsFromUsers($memberResult['users']))) {
            return redirect()->back()->with('faild2', 'One or more team members already has a pending request or idea.');
        }

        $projectRequest = DB::transaction(function () use ($validated, $memberResult) {
            $projectRequest = Projectrequest::create([
                'project_id' => $validated['project_id'],
                'requested_by_user_id' => Auth::id(),
                'count' => count($memberResult['members']),
            ]);

            foreach ($memberResult['members'] as $member) {
                $projectRequest->members()->create([
                    'user_id' => $member['user']->id,
                    'position' => $member['position'],
                ]);
            }

            return $projectRequest;
        });

        ActivityLogger::log(
            ActivityLogger::PROJECT_REQUEST_SUBMITTED,
            "Submitted project request for {$project->name}",
            subject: $projectRequest,
            metadata: [
                'request_id' => $projectRequest->id,
                'project_id' => $project->id,
                'project_name' => $project->name,
                'member_university_numbers' => collect($memberResult['members'])
                    ->map(fn (array $member) => $member['user']->university_number)
                    ->values()
                    ->all(),
            ],
        );

        $project->loadMissing('supervisor.user');
        $supervisorUser = $project->supervisor?->user;

        if ($supervisorUser) {
            $supervisorUser->notify(new WorkflowNotification(
                type: 'request_submitted',
                title: 'New project request',
                body: 'A student has submitted a project request.',
                actionUrl: '/supervisorDashboard',
                relatedType: UniProject::class,
                relatedId: $project->id,
            ));
        }

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
            'supervisor_id' => 'required|integer|exists:supervisors,id',
            'oneid' => 'required|string',
            'twoid' => 'nullable|string',
            'threeid' => 'nullable|string',
        ]);

        $supervisor = Supervisor::findOrFail($validated['supervisor_id']);

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

        if (WorkflowGuard::anyUserHasPendingApplication(WorkflowGuard::userIdsFromUsers($memberResult['users']))) {
            return redirect()->back()->with('faild2', 'One or more team members already has a pending request or idea.');
        }

        $idea = DB::transaction(function () use ($validated, $supervisor, $memberResult) {
            $idea = Idea::create([
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

            return $idea;
        });

        ActivityLogger::log(
            ActivityLogger::IDEA_SUBMITTED,
            "Submitted project idea: {$idea->projectname}",
            subject: $idea,
            metadata: [
                'idea_id' => $idea->id,
                'title' => $idea->projectname,
                'supervisor_id' => $supervisor->id,
                'member_university_numbers' => collect($memberResult['members'])
                    ->map(fn (array $member) => $member['user']->university_number)
                    ->values()
                    ->all(),
            ],
        );

        $supervisor->loadMissing('user');

        if ($supervisor->user && $idea) {
            $supervisor->user->notify(new WorkflowNotification(
                type: 'idea_submitted',
                title: 'New project idea',
                body: 'A student has submitted a project idea.',
                actionUrl: '/supervisorDashboard',
                relatedType: Idea::class,
                relatedId: $idea->id,
            ));
        }

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
