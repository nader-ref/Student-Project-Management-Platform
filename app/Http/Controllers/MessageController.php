<?php

namespace App\Http\Controllers;

use App\Models\contact;
use App\Models\Supervisor;
use App\Models\supcontact;
use App\Services\StudentEnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function message(Request $request)
    {
        $validated = $request->validate([
            'supervisor_id' => 'required|integer|exists:supervisors,id',
            'subject' => 'required|string|max:255',
            'Message' => 'nullable|string|max:255',
        ]);

        $student = Auth::user();
        $supervisor = Supervisor::findOrFail($validated['supervisor_id']);

        contact::create([
            'student_user_id' => $student->id,
            'supervisor_id' => $supervisor->id,
            'subject' => $validated['subject'],
            'Message' => $validated['Message'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Your request has been submitted successfully.');
    }

    public function replay()
    {
        $student = Auth::user();
        $enrollment = StudentEnrollmentService::resolve($student);
        $project = $enrollment['project'];

        $messages = contact::with(['student', 'supervisor'])
            ->where('student_user_id', $student->id)
            ->orderByDesc('updated_at')
            ->get();

        $supmessages = $project
            ? supcontact::with(['supervisor', 'project'])
                ->where('project_id', $project->id)
                ->orderByDesc('created_at')
                ->get()
            : collect();

        return view('student.replay', [
            'Messages' => $messages,
            'supmessages' => $supmessages,
        ]);
    }
}
