<?php

namespace App\Http\Controllers;

use App\Models\ProjectMember;
use App\Models\ProjectSubmission;
use App\Notifications\WorkflowNotification;
use App\Services\ActivityLogger;
use App\Services\StudentEnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
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

        $blockingExists = ProjectSubmission::query()
            ->where('project_id', $project->id)
            ->where('milestone', $validated['milestone'])
            ->whereIn('status', ['submitted', 'approved'])
            ->exists();

        if ($blockingExists) {
            return redirect()->back()
                ->withErrors([
                    'milestone' => 'A submission for this milestone is already pending or approved.',
                ])
                ->withInput($request->except('file'))
                ->with('active_tab', 'submissions');
        }

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

        ActivityLogger::log(
            ActivityLogger::SUBMISSION_UPLOADED,
            "Uploaded submission: {$submission->title}",
            subject: $submission,
            metadata: [
                'project_id' => $project->id,
                'project_name' => $project->name,
                'milestone' => $submission->milestone,
                'title' => $submission->title,
                'original_filename' => $submission->original_filename,
            ],
        );

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
            'supervisor_feedback' => [
                Rule::requiredIf($request->input('status') === 'needs_revision'),
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $submission = ProjectSubmission::with('project')->findOrFail($validated['submission_id']);
        $supervisor = Auth::user()?->supervisor;

        if (! $supervisor || $submission->project?->supervisor_id !== $supervisor->id) {
            return back()->with('error', 'You cannot review this submission.');
        }

        $oldStatus = $submission->status;
        $oldFeedback = $submission->supervisor_feedback;

        $submission->update([
            'status' => $validated['status'],
            'supervisor_feedback' => $validated['supervisor_feedback'] ?? null,
            'reviewed_at' => now(),
            'reviewed_by_user_id' => Auth::id(),
        ]);

        $statusChanged = $oldStatus !== $submission->status;
        $feedbackChanged = $oldFeedback !== $submission->supervisor_feedback;

        $submission->loadMissing('submittedBy');

        if (($statusChanged || $feedbackChanged) && $submission->submittedBy) {
            ActivityLogger::log(
                ActivityLogger::SUBMISSION_REVIEWED,
                "Reviewed submission: {$submission->title}",
                targetUser: $submission->submittedBy,
                subject: $submission,
                metadata: [
                    'milestone' => $submission->milestone,
                    'old_status' => $oldStatus,
                    'new_status' => $submission->status,
                    'feedback_changed' => $feedbackChanged,
                ],
            );

            $milestoneLabels = StudentEnrollmentService::milestoneLabels();
            $milestoneLabel = $milestoneLabels[$submission->milestone] ?? $submission->milestone;

            $body = match ($submission->status) {
                'approved' => "Your {$milestoneLabel} submission was approved.",
                'needs_revision' => "Revision required for your {$milestoneLabel} submission.",
                default => "Your {$milestoneLabel} submission was updated.",
            };

            $submission->submittedBy->notify(new WorkflowNotification(
                type: 'submission_reviewed',
                title: 'Submission reviewed',
                body: $body,
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
