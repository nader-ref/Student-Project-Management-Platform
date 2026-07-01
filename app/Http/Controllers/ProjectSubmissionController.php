<?php

namespace App\Http\Controllers;

use App\Models\ProjectSubmission;
use App\Models\UniProject;
use App\Services\StudentEnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProjectSubmissionController extends Controller
{
    public function store(Request $request)
    {
        $enrollment = StudentEnrollmentService::resolve(Session::get('name'), Auth::user());

        if ($enrollment['mode'] !== StudentEnrollmentService::MODE_ENROLLED || ! $enrollment['project']) {
            return back()->with('error', 'You must be enrolled in a project to submit files.');
        }

        $validated = $request->validate([
            'milestone' => 'required|in:seminar_1,seminar_2,seminar_3,final,other',
            'title' => 'required|string|max:255',
            'file' => 'required|file|max:10240|mimes:pdf,doc,docx,ppt,pptx,zip,rar',
            'notes' => 'nullable|string|max:1000',
        ]);

        $project = $enrollment['project'];
        $file = $request->file('file');
        $path = $file->store('submissions/'.$project->id, 'public');

        ProjectSubmission::create([
            'project_id' => $project->id,
            'student_email' => Session::get('email'),
            'student_name' => Session::get('name'),
            'milestone' => $validated['milestone'],
            'title' => $validated['title'],
            'file_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'notes' => $validated['notes'] ?? null,
            'status' => 'submitted',
        ]);

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

        $ownsProject = UniProject::where('id', $submission->project_id)
            ->where('supervisor_id', Session::get('id'))
            ->exists();

        if (! $ownsProject) {
            return back()->with('error', 'You cannot review this submission.');
        }

        $submission->update([
            'status' => $validated['status'],
            'supervisor_feedback' => $validated['supervisor_feedback'],
        ]);

        return redirect()->back()
            ->with('success', 'Submission updated.')
            ->with('active_tab', 'Submissions');
    }

    public function download(ProjectSubmission $submission): BinaryFileResponse
    {
        $submission->load('project');
        $user = Auth::user();

        if ($user->hasRole('student')) {
            $enrollment = StudentEnrollmentService::resolve(Session::get('name'), $user);
            if ($enrollment['project']?->id !== $submission->project_id) {
                abort(403);
            }
        } elseif ($user->hasRole('supervisor')) {
            $ownsProject = UniProject::where('id', $submission->project_id)
                ->where('supervisor_id', Session::get('id'))
                ->exists();

            if (! $ownsProject) {
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
