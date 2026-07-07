<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Idea;
use App\Models\ProjectSubmission;
use App\Models\Projectrequest;
use App\Models\Supervisor;
use App\Models\Supcontact;
use App\Models\UniProject;
use App\Models\User;
use App\Notifications\WorkflowNotification;
use App\Services\WorkflowGuard;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SupervisorController extends Controller
{
    public function show()
    {
        $supervisorNames = Supervisor::query()
            ->orderBy('name')
            ->pluck('name');

        return view('supervisor.Login', compact('supervisorNames'));
    }

    public function login(Request $request)
    {
        $log = request()->validate([
            'name' => 'required|string',
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (! Auth::attempt([
            'email' => $request->email,
            'password' => $request->password,
        ])) {
            throw ValidationException::withMessages([
                'email' => 'sorry you dont have ana account',
            ]);
        }

        $user = Auth::user();

        if ($user->name !== $request->name) {
            Auth::logout();

            throw ValidationException::withMessages([
                'name' => 'The provided name does not match our records.',
            ]);
        }

        if (! $user->hasRole('supervisor')) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'This account does not have supervisor access.',
            ]);
        }

        $sup = $user->supervisor;

        if (! $sup) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'Supervisor profile not found.',
            ]);
        }

        request()->session()->regenerate();

        return redirect('/supervisorDashboard')->with('success', 'User registered successfully!');
    }

    public function showdash()
    {
        $supervisor = Auth::user()->supervisor;

        if (! $supervisor) {
            abort(403);
        }

        $supervisorId = $supervisor->id;

        $projects = UniProject::with('members.user')->where('supervisor_id', $supervisorId)->get();
        $myProjectIds = $projects->pluck('id');

        $requests = Projectrequest::with(['project', 'members.user'])
            ->whereIn('project_id', $myProjectIds)
            ->get();
        $ideas = Idea::with(['supervisor', 'members.user'])
            ->where('supervisor_id', $supervisorId)
            ->get();
        $inboxMessages = Contact::with(['student', 'supervisor'])
            ->where('supervisor_id', $supervisorId)
            ->orderByDesc('created_at')
            ->get();
        $announcements = Supcontact::with(['supervisor', 'project'])
            ->where('supervisor_id', $supervisorId)
            ->orderByDesc('created_at')
            ->get();
        $submissions = ProjectSubmission::with(['project', 'submittedBy'])
            ->whereIn('project_id', $myProjectIds)
            ->orderByDesc('created_at')
            ->get();

        return view('supervisor.dashboard', [
            'projects' => $projects,
            'requests' => $requests,
            'ideas' => $ideas,
            'inboxMessages' => $inboxMessages,
            'announcements' => $announcements,
            'submissions' => $submissions,
            'milestoneLabels' => \App\Services\StudentEnrollmentService::milestoneLabels(),
            'supervisor' => $supervisor,
        ]);
    }

    public function addproject(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_name' => 'required|string|max:255',
            'description' => 'required|string',
            'department' => 'required|in:software,ai,network',
            'taken' => 'required|in:Yes,No',
            'students_number' => 'nullable|integer|min:0|max:3',
            'student_one_name' => 'nullable|string|max:255',
            'student_one_id' => 'nullable|string|max:50',
            'student_two_name' => 'nullable|string|max:255',
            'student_two_id' => 'nullable|string|max:50',
            'student_three_name' => 'nullable|string|max:255',
            'student_three_id' => 'nullable|string|max:50',
            'seminar1_date' => 'required|date',
            'seminar2_date' => 'required|date',
            'seminar3_date' => 'required|date',
            'final_date' => 'required|date|after_or_equal:seminar3_date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $sup = Auth::user()->supervisor;

        if (! $sup) {
            return back()->with('error', 'Supervisor profile not found.');
        }

        $tak = $request->taken == 'Yes' ? 1 : 0;

        $memberResult = $this->membersFromRequest($request);
        if (isset($memberResult['error'])) {
            return back()->with('error', $memberResult['error'])->withInput();
        }

        $memberUserIds = WorkflowGuard::userIdsFromMembers($memberResult['members']);

        if (WorkflowGuard::anyUserEnrolledInOtherProject($memberUserIds)) {
            return back()->with('error', 'One or more selected students are already enrolled in another project.')->withInput();
        }

        DB::transaction(function () use ($request, $sup, $tak, $memberResult) {
            $project = UniProject::create([
                'name' => $request->project_name,
                'description' => $request->description,
                'supervisor_id' => $sup->id,
                'department' => $request->department,
                'taken' => $tak,
                'student_count' => count($memberResult['members']) ?: ($request->students_number ?? null),
                'seminar_1' => $request->seminar1_date,
                'seminar_2' => $request->seminar2_date,
                'seminar_3' => $request->seminar3_date,
                'final' => $request->final_date,
            ]);

            $this->syncProjectMembers($project, $memberResult['members']);
        });

        return redirect()->back()->with('success', 'Project registered successfully!');
    }

    public function updateproject(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|integer|exists:uni_projects,id',
            'project_name' => 'required|string|max:255',
            'description' => 'required|string',
            'department' => 'required|in:software,ai,network',
            'taken' => 'required|in:Yes,No',
            'students_number' => 'nullable|integer|min:0|max:3',
            'student_one_name' => 'nullable|string|max:255',
            'student_one_id' => 'nullable|string|max:50',
            'student_two_name' => 'nullable|string|max:255',
            'student_two_id' => 'nullable|string|max:50',
            'student_three_name' => 'nullable|string|max:255',
            'student_three_id' => 'nullable|string|max:50',
            'seminar1_date' => 'required|date',
            'seminar2_date' => 'required|date',
            'seminar3_date' => 'required|date',
            'final_date' => 'required|date|after_or_equal:seminar3_date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('active_tab', 'show_pro');
        }

        $supervisor = Auth::user()->supervisor;

        if (! $supervisor) {
            return back()->with('error', 'Supervisor profile not found.')->with('active_tab', 'show_pro');
        }

        $project = UniProject::where('id', $request->project_id)
            ->where('supervisor_id', $supervisor->id)
            ->first();

        if (! $project) {
            return back()->with('error', 'Project not found or access denied.');
        }

        $memberResult = $this->membersFromRequest($request);
        if (isset($memberResult['error'])) {
            return back()->with('error', $memberResult['error'])->withInput()->with('active_tab', 'show_pro');
        }

        $memberUserIds = WorkflowGuard::userIdsFromMembers($memberResult['members']);

        if (WorkflowGuard::anyUserEnrolledInOtherProject($memberUserIds, $project->id)) {
            return back()->with('error', 'One or more selected students are already enrolled in another project.')
                ->withInput()
                ->with('active_tab', 'show_pro');
        }

        DB::transaction(function () use ($request, $project, $memberResult) {
            $project->update([
                'name' => $request->project_name,
                'description' => $request->description,
                'department' => $request->department,
                'taken' => $request->taken == 'Yes' ? 1 : 0,
                'student_count' => count($memberResult['members']) ?: ($request->students_number ?? null),
                'seminar_1' => $request->seminar1_date,
                'seminar_2' => $request->seminar2_date,
                'seminar_3' => $request->seminar3_date,
                'final' => $request->final_date,
            ]);

            $this->syncProjectMembers($project, $memberResult['members']);
        });

        return redirect()->back()->with('success', 'Project updated successfully!')->with('active_tab', 'show_pro');
    }

    public function acceptrequest(Request $request)
    {
        $supervisor = Auth::user()->supervisor;

        if (! $supervisor) {
            return back()->with('error', 'Supervisor profile not found.');
        }

        $requestId = $request->input('request');

        $projectRequest = Projectrequest::with(['project', 'members.user'])->find($requestId);
        $project = $projectRequest?->project;

        if (! $project) {
            return back()->with('error', 'Project not found.');
        }

        if ((int) $project->supervisor_id !== (int) $supervisor->id) {
            return back()->with('error', 'Project request not found.');
        }

        if (WorkflowGuard::isProjectRequestProcessed($projectRequest)) {
            return back()->with('error', 'This request has already been processed.');
        }

        if ($project->taken) {
            return back()->with('error', 'This project is already taken.');
        }

        if ($project->members()->exists()) {
            return back()->with('error', 'This project already has enrolled team members.');
        }

        $memberResult = $this->membersFromRequestMembers($projectRequest);

        if (isset($memberResult['error'])) {
            return back()->with('error', $memberResult['error']);
        }

        $memberUserIds = WorkflowGuard::userIdsFromMembers($memberResult['members']);

        if (WorkflowGuard::teamSizeExceedsMax(count($memberResult['members']))) {
            return back()->with('error', 'A project team cannot exceed '.WorkflowGuard::MAX_TEAM_SIZE.' members.');
        }

        if (WorkflowGuard::anyUserEnrolledInProject($memberUserIds)) {
            return back()->with('error', 'One or more students in this request are already enrolled in another project.');
        }

        DB::transaction(function () use ($project, $projectRequest, $memberResult) {
            $project->taken = 1;
            $project->student_count = count($memberResult['members']);
            $project->save();

            $this->syncProjectMembers($project, $memberResult['members']);

            $projectRequest->accepted = 1;
            $projectRequest->rejected = 0;
            $projectRequest->save();
        });

        $projectRequest->loadMissing('members.user');

        foreach ($projectRequest->members as $member) {
            if ($member->user) {
                $member->user->notify(new WorkflowNotification(
                    type: 'request_accepted',
                    title: 'Project request accepted',
                    body: 'Your project request has been accepted.',
                    actionUrl: '/StudentDashboard/acceptance',
                    relatedType: Projectrequest::class,
                    relatedId: $projectRequest->id,
                ));
            }
        }

        return redirect()->back()->with('success', 'Request accepted successfully!');
    }

    public function rejectrequest(Request $request)
    {
        $supervisor = Auth::user()->supervisor;

        if (! $supervisor) {
            return back()->with('error', 'Supervisor profile not found.')->with('active_tab', 'Request');
        }

        $validator = Validator::make($request->all(), [
            'request' => 'required|integer',
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->with('active_tab', 'Request');
        }

        $requestId = $request->input('request');
        $projectRequest = Projectrequest::with('project')->find($requestId);
        if (! $projectRequest) {
            return back()->with('error', 'Request not found.');
        }

        $ownsProject = (int) $projectRequest->project?->supervisor_id === (int) $supervisor->id;

        if (! $ownsProject) {
            return back()->with('error', 'You cannot reject this request.');
        }

        if (WorkflowGuard::isProjectRequestProcessed($projectRequest)) {
            return back()->with('error', 'This request has already been processed.')->with('active_tab', 'Request');
        }

        DB::transaction(function () use ($projectRequest, $request) {
            $projectRequest->rejected = 1;
            $projectRequest->accepted = 0;
            $projectRequest->reason = $request->reason;
            $projectRequest->save();
        });

        $projectRequest->loadMissing('members.user');

        foreach ($projectRequest->members as $member) {
            if ($member->user) {
                $member->user->notify(new WorkflowNotification(
                    type: 'request_rejected',
                    title: 'Project request rejected',
                    body: 'Your project request has been rejected.',
                    actionUrl: '/StudentDashboard/acceptance',
                    relatedType: Projectrequest::class,
                    relatedId: $projectRequest->id,
                ));
            }
        }

        return redirect()->back()->with('success', 'Request rejected.')->with('active_tab', 'Request');
    }

    public function acceptidea(Request $request)
    {
        $supervisor = Auth::user()->supervisor;

        if (! $supervisor) {
            return back()->with('error', 'Supervisor profile not found.');
        }

        $idea = Idea::with(['members.user'])->find($request->idea);

        if (! $idea || (int) $idea->supervisor_id !== (int) $supervisor->id) {
            return back()->with('error', 'Idea not found.');
        }

        if (WorkflowGuard::isIdeaProcessed($idea)) {
            return back()->with('error', 'This idea has already been processed.');
        }

        $memberResult = $this->membersFromIdeaMembers($idea);

        if (isset($memberResult['error'])) {
            return back()->with('error', $memberResult['error']);
        }

        if (WorkflowGuard::teamSizeExceedsMax(count($memberResult['members']))) {
            return back()->with('error', 'A project team cannot exceed '.WorkflowGuard::MAX_TEAM_SIZE.' members.');
        }

        $memberUserIds = WorkflowGuard::userIdsFromMembers($memberResult['members']);

        if (WorkflowGuard::anyUserEnrolledInProject($memberUserIds)) {
            return back()->with('error', 'One or more students in this idea are already enrolled in another project.');
        }

        DB::transaction(function () use ($idea, $memberResult, $supervisor) {
            $project = UniProject::create([
                'name' => $idea->projectname,
                'description' => null,
                'supervisor_id' => $supervisor->id,
                'department' => 'software',
                'taken' => 1,
                'student_count' => count($memberResult['members']),
                'seminar_1' => null,
                'seminar_2' => null,
                'seminar_3' => null,
                'final' => null,
            ]);

            $this->syncProjectMembers($project, $memberResult['members']);

            $idea->accepted = 1;
            $idea->rejected = 0;
            $idea->save();
        });

        $idea->loadMissing('members.user');

        foreach ($idea->members as $member) {
            if ($member->user) {
                $member->user->notify(new WorkflowNotification(
                    type: 'idea_accepted',
                    title: 'Project idea accepted',
                    body: 'Your project idea has been accepted.',
                    actionUrl: '/StudentDashboard/acceptanceidea',
                    relatedType: Idea::class,
                    relatedId: $idea->id,
                ));
            }
        }

        return redirect()->back()->with('success', 'Idea accepted and project created!');
    }

    public function rejectidea(Request $request)
    {
        $supervisor = Auth::user()->supervisor;

        if (! $supervisor) {
            return back()->with('error', 'Supervisor profile not found.')->with('active_tab', 'Idea');
        }

        $validator = Validator::make($request->all(), [
            'idea' => 'required|integer',
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->with('active_tab', 'Idea');
        }

        $idea = Idea::find($request->idea);

        if (! $idea || (int) $idea->supervisor_id !== (int) $supervisor->id) {
            return back()->with('error', 'Idea not found.');
        }

        if (WorkflowGuard::isIdeaProcessed($idea)) {
            return back()->with('error', 'This idea has already been processed.')->with('active_tab', 'Idea');
        }

        DB::transaction(function () use ($idea, $request) {
            $idea->rejected = 1;
            $idea->accepted = 0;
            $idea->reason = $request->reason;
            $idea->save();
        });

        $idea->loadMissing('members.user');

        foreach ($idea->members as $member) {
            if ($member->user) {
                $member->user->notify(new WorkflowNotification(
                    type: 'idea_rejected',
                    title: 'Project idea rejected',
                    body: 'Your project idea has been rejected.',
                    actionUrl: '/StudentDashboard/acceptanceidea',
                    relatedType: Idea::class,
                    relatedId: $idea->id,
                ));
            }
        }

        return redirect()->back()->with('success', 'Idea rejected.')->with('active_tab', 'Idea');
    }

    public function replycontact(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contact_id' => 'required|integer',
            'replay' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->with('active_tab', 'Message');
        }

        $supervisor = Auth::user()->supervisor;
        $contact = $supervisor
            ? Contact::where('id', $request->contact_id)
                ->where('supervisor_id', $supervisor->id)
                ->first()
            : null;

        if (! $contact) {
            return back()->with('error', 'Message not found.');
        }

        $contact->Replay = $request->replay;
        $contact->save();

        $contact->loadMissing('student');

        if ($contact->student) {
            $contact->student->notify(new WorkflowNotification(
                type: 'message_reply',
                title: 'Supervisor replied',
                body: 'Your supervisor has replied to your message.',
                actionUrl: '/StudentDashboard/replay',
                relatedType: Contact::class,
                relatedId: $contact->id,
            ));
        }

        return redirect()->back()->with('success', 'Reply sent successfully!')->with('active_tab', 'Message');
    }

    public function sendannouncement(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|integer|exists:uni_projects,id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('active_tab', 'Message');
        }

        $supervisor = Auth::user()->supervisor;
        $project = $supervisor
            ? UniProject::where('id', $request->project_id)
                ->where('supervisor_id', $supervisor->id)
                ->first()
            : null;

        if (! $project) {
            return back()->with('error', 'Project not found or access denied.')->with('active_tab', 'Message');
        }

        Supcontact::create([
            'supervisor_id' => $supervisor->id,
            'project_id' => $project->id,
            'subject' => $request->subject,
            'Message' => $request->message,
        ]);

        return redirect()->back()->with('success', 'Announcement published!')->with('active_tab', 'Message');
    }

    public function changepassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (! Hash::check($request->old_password, $user->password)) {
            return back()->withErrors(['old_password' => 'Current password is incorrect.'])
                ->with('active_tab', 'settings');
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->back()->with('success', 'Password updated successfully!')->with('active_tab', 'settings');
    }

    private function membersFromRequest(Request $request): array
    {
        return $this->membersFromLegacySlots([
            1 => $request->student_one_id,
            2 => $request->student_two_id,
            3 => $request->student_three_id,
        ]);
    }

    private function membersFromLegacySlots(array $slots): array
    {
        $members = [];
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
            $members[] = [
                'user' => $user,
                'position' => $position,
            ];
        }

        return ['members' => $members];
    }

    private function membersFromRequestMembers(Projectrequest $projectRequest): array
    {
        $projectRequest->loadMissing('members.user');

        $members = $projectRequest->members
            ->sortBy('position')
            ->map(fn ($member) => [
                'user' => $member->user,
                'position' => $member->position,
            ])
            ->values()
            ->all();

        if ($members === []) {
            return ['error' => 'This request has no linked student members.'];
        }

        return ['members' => $members];
    }

    private function membersFromIdeaMembers(Idea $idea): array
    {
        $idea->loadMissing('members.user');

        $members = $idea->members
            ->sortBy('position')
            ->map(fn ($member) => [
                'user' => $member->user,
                'position' => $member->position,
            ])
            ->values()
            ->all();

        if ($members === []) {
            return ['error' => 'This idea has no linked student members.'];
        }

        return ['members' => $members];
    }

    private function syncProjectMembers(UniProject $project, array $members): void
    {
        $project->members()->delete();

        foreach ($members as $member) {
            $project->members()->create([
                'user_id' => $member['user']->id,
                'position' => $member['position'],
            ]);
        }
    }
}
