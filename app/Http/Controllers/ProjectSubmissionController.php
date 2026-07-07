<?php

namespace App\Http\Controllers;

use App\Models\ProjectMember;
use App\Models\ProjectSubmission;
use App\Notifications\WorkflowNotification;
use App\Services\StudentEnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProjectSubmissionController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::user();
        $enrollment = StudentEnrollmentService::resolve($user);

        if ($enrollment['mode'] !== StudentEnrollmentService::MODE_ENROLLED || ! $enrollment['project']) {
            return back()
                ->with('error', 'You must be enrolled in a project to submit files.')
                ->with('active_tab', 'submissions');
        }

        $project = $enrollment['project'];

        if (! ProjectMember::where('project_id', $project->id)->where('user_id', $user->id)->exists()) {
            return back()
                ->with('error', 'You must be a project member to submit files.')
                ->with('active_tab', 'submissions');
        }

        $validator = Validator::make($request->all(), [
            'milestone' => 'required|in:seminar_1,seminar_2,seminar_3,final,other',
            'title' => 'required|string|max:255',
            'file' => 'required|file|max:10240|mimes:pdf,doc,docx,ppt,pptx,zip,rar',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->except('file'))
                ->with('active_tab', 'submissions');
        }

        $validated = $validator->validated();

        $file = $request->file('file');
        $path = $file->store('submissions/'.$project->id, 'public');

        $submission = ProjectSubmission::create([
            'project_id' => $project->id,
            'submitted_by_user_id' => $user->id,
            'milestone' => $validated['milestone'],
            'title' => $validated['title'],
            'file_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'notes' => $validated['notes'] ?? null,
            'status' => 'submitted',
        ]);

        $project->loadMissing('supervisor.user');
        $supervisorUser = $project->supervisor?->user;

        if ($supervisorUser) {
            $supervisorUser->notify(new WorkflowNotification(
                type: 'submission_uploaded',
                title: 'New submission uploaded',
                body: 'A student has uploaded a project submission.',
                actionUrl: '/supervisorDashboard',
                relatedType: ProjectSubmission::class,
                relatedId: $submission->id,
            ));
        }

        return redirect()->back()
            ->with('success', 'File submitted successfully!')
            ->with('active_tab', 'submissions');
    }

    public function review(Request $request)
    {
        $validated = $request->validate([
            'submission_id' => 'required|integer|exists:project_submissions,id',
            'status' => 'required|in:submitted,approved,needs_revision',
            'supervisor_feedback' => 'nullable|string|max:1000',
        ]);

        $submission = ProjectSubmission::with('project')->findOrFail($validated['submission_id']);
        $supervisor = Auth::user()?->supervisor;

        if (! $supervisor || $submission->project?->supervisor_id !== $supervisor->id) {
            return back()->with('error', 'You cannot review this submission.');
        }

        $submission->update([
            'status' => $validated['status'],
            'supervisor_feedback' => $validated['supervisor_feedback'],
        ]);

        $submission->loadMissing('submittedBy');

        if ($submission->submittedBy) {
            $submission->submittedBy->notify(new WorkflowNotification(
                type: 'submission_reviewed',
                title: 'Submission reviewed',
                body: 'Your supervisor has reviewed your submission.',
                actionUrl: '/StudentDashboard',
                relatedType: ProjectSubmission::class,
                relatedId: $submission->id,
            ));
        }

        return redirect()->back()
            ->with('success', 'Submission updated.')
            ->with('active_tab', 'Submissions');
    }

    public function download(ProjectSubmission $submission): BinaryFileResponse
    {
        $submission->load('project');
        $user = Auth::user();

        if ($user->hasRole('student')) {
            $isMember = ProjectMember::where('project_id', $submission->project_id)
                ->where('user_id', $user->id)
                ->exists();

            if (! $isMember) {
                abort(403);
            }
        } elseif ($user->hasRole('supervisor')) {
            $supervisor = $user->supervisor;

            if (! $supervisor || $submission->project?->supervisor_id !== $supervisor->id) {
                abort(403);
            }
        } else {
            abort(403);
        }

        $absolutePath = storage_path('app/public/'.$submission->file_path);

        if (! is_file($absolutePath)) {
            abort(404);
        }

        return response()->download($absolutePath, $submission->original_filename);
    }
}
