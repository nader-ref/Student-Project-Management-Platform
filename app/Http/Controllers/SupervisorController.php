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
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
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
        Session::put('email', request('email'));
        Session::put('id', $sup->id);
        Session::put('name', $user->name);

        return redirect('/supervisorDashboard')->with('success', 'User registered successfully!');
    }

    public function showdash()
    {
        $supervisorId = Session::get('id');
        $supervisorName = Session::get('name');

        $projects = UniProject::where('supervisor_id', $supervisorId)->get();
        $myProjectIds = $projects->pluck('id');

        $requests = Projectrequest::whereIn('projectid', $myProjectIds)->get();
        $ideas = Idea::where('supname', $supervisorName)->get();
        $inboxMessages = Contact::where('supname', $supervisorName)->orderByDesc('created_at')->get();
        $announcements = Supcontact::where('supname', $supervisorName)->orderByDesc('created_at')->get();
        $submissions = ProjectSubmission::with('project')
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
            'supervisor' => Supervisor::find($supervisorId),
        ]);
    }

    public function logout()
    {
        Auth::logout();
        Session::flush();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect('/')->with('success', 'Logged out successfully.');
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
        $tak = $request->taken == 'Yes' ? 1 : 0;

        UniProject::create([
            'name' => $request->project_name,
            'description' => $request->description,
            'supervisor_id' => $sup->id,
            'department' => $request->department,
            'taken' => $tak,
            'student_count' => $request->students_number ?? null,
            'student_one_name' => $request->student_one_name ?? null,
            'student_one_id' => $request->student_one_id ?? null,
            'student_two_name' => $request->student_two_name ?? null,
            'student_two_id' => $request->student_two_id ?? null,
            'student_three_name' => $request->student_three_name ?? null,
            'student_three_id' => $request->student_three_id ?? null,
            'seminar_1' => $request->seminar1_date,
            'seminar_2' => $request->seminar2_date,
            'seminar_3' => $request->seminar3_date,
            'final' => $request->final_date,
        ]);

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

        $project = UniProject::where('id', $request->project_id)
            ->where('supervisor_id', Session::get('id'))
            ->first();

        if (! $project) {
            return back()->with('error', 'Project not found or access denied.');
        }

        $project->update([
            'name' => $request->project_name,
            'description' => $request->description,
            'department' => $request->department,
            'taken' => $request->taken == 'Yes' ? 1 : 0,
            'student_count' => $request->students_number ?? null,
            'student_one_name' => $request->student_one_name ?? null,
            'student_one_id' => $request->student_one_id ?? null,
            'student_two_name' => $request->student_two_name ?? null,
            'student_two_id' => $request->student_two_id ?? null,
            'student_three_name' => $request->student_three_name ?? null,
            'student_three_id' => $request->student_three_id ?? null,
            'seminar_1' => $request->seminar1_date,
            'seminar_2' => $request->seminar2_date,
            'seminar_3' => $request->seminar3_date,
            'final' => $request->final_date,
        ]);

        return redirect()->back()->with('success', 'Project updated successfully!')->with('active_tab', 'show_pro');
    }

    public function acceptrequest(Request $request)
    {
        $projectId = $request->input('project');
        $requestId = $request->input('request');

        $project = UniProject::where('id', $projectId)
            ->where('supervisor_id', Session::get('id'))
            ->first();
        $projectRequest = Projectrequest::where('id', $requestId)->first();

        if (! $project) {
            return back()->with('error', 'Project not found.');
        }

        if (! $projectRequest || $projectRequest->projectid != $project->id) {
            return back()->with('error', 'Project request not found.');
        }

        $project->taken = 1;
        $project->student_count = $projectRequest->count;
        $project->student_one_name = $projectRequest->nameone;
        $project->student_one_id = $projectRequest->oneid;
        $project->student_two_name = $projectRequest->nametwo;
        $project->student_two_id = $projectRequest->twoid;
        $project->student_three_name = $projectRequest->namethree;
        $project->student_three_id = $projectRequest->threeid;
        $project->save();

        $projectRequest->accepted = 1;
        $projectRequest->rejected = 0;
        $projectRequest->save();

        return redirect()->back()->with('success', 'Request accepted successfully!');
    }

    public function rejectrequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'request' => 'required|integer',
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->with('active_tab', 'Request');
        }

        $requestId = $request->input('request');
        $projectRequest = Projectrequest::find($requestId);
        if (! $projectRequest) {
            return back()->with('error', 'Request not found.');
        }

        $ownsProject = UniProject::where('id', $projectRequest->projectid)
            ->where('supervisor_id', Session::get('id'))
            ->exists();

        if (! $ownsProject) {
            return back()->with('error', 'You cannot reject this request.');
        }

        $projectRequest->rejected = 1;
        $projectRequest->accepted = 0;
        $projectRequest->reason = $request->reason;
        $projectRequest->save();

        return redirect()->back()->with('success', 'Request rejected.')->with('active_tab', 'Request');
    }

    public function acceptidea(Request $request)
    {
        $idea = Idea::where('id', $request->idea)
            ->where('supname', Session::get('name'))
            ->first();

        if (! $idea) {
            return back()->with('error', 'Idea not found.');
        }

        UniProject::create([
            'name' => $idea->projectname,
            'description' => null,
            'supervisor_id' => Session::get('id'),
            'department' => 'software',
            'taken' => 1,
            'student_count' => $idea->count,
            'student_one_name' => $idea->nameone ?? null,
            'student_one_id' => $idea->oneid ?? null,
            'student_two_name' => $idea->nametwo ?? null,
            'student_two_id' => $idea->twoid ?? null,
            'student_three_name' => $idea->namethree ?? null,
            'student_three_id' => $idea->threeid ?? null,
            'seminar_1' => null,
            'seminar_2' => null,
            'seminar_3' => null,
            'final' => null,
        ]);

        $idea->accepted = 1;
        $idea->rejected = 0;
        $idea->save();

        return redirect()->back()->with('success', 'Idea accepted and project created!');
    }

    public function rejectidea(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idea' => 'required|integer',
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->with('active_tab', 'Idea');
        }

        $idea = Idea::where('id', $request->idea)
            ->where('supname', Session::get('name'))
            ->first();

        if (! $idea) {
            return back()->with('error', 'Idea not found.');
        }

        $idea->rejected = 1;
        $idea->accepted = 0;
        $idea->reason = $request->reason;
        $idea->save();

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

        $contact = Contact::where('id', $request->contact_id)
            ->where('supname', Session::get('name'))
            ->first();

        if (! $contact) {
            return back()->with('error', 'Message not found.');
        }

        $contact->Replay = $request->replay;
        $contact->save();

        return redirect()->back()->with('success', 'Reply sent successfully!')->with('active_tab', 'Message');
    }

    public function sendannouncement(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'projectname' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('active_tab', 'Message');
        }

        Supcontact::create([
            'supname' => Session::get('name'),
            'projectname' => $request->projectname,
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

        $user = User::where('email', Session::get('email'))->first();

        if (! $user || ! Hash::check($request->old_password, $user->password)) {
            return back()->withErrors(['old_password' => 'Current password is incorrect.'])
                ->with('active_tab', 'settings');
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->back()->with('success', 'Password updated successfully!')->with('active_tab', 'settings');
    }
}
