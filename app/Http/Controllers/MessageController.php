<?php

namespace App\Http\Controllers;

use App\Models\contact;
use App\Models\supcontact;
use App\Services\StudentEnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class MessageController extends Controller
{
    public function message(Request $request)
    {
        $validated = $request->validate([
            'supname' => 'required|string',
            'subject' => 'required|string|max:255',
            'Message' => 'nullable|string|max:255',
        ]);

        contact::create([
            'email' => Session::get('email'),
            'supname' => $validated['supname'],
            'subject' => $validated['subject'],
            'Message' => $validated['Message'],
        ]);

        return redirect()->back()->with('success', 'Your request has been submitted successfully.');
    }

    public function replay()
    {
        $studentEmail = Session::get('email');
        $studentName = Session::get('name');
        $enrollment = StudentEnrollmentService::resolve($studentName);
        $projectName = $enrollment['project']?->name;

        $messages = $studentEmail
            ? contact::where('email', $studentEmail)->orderByDesc('updated_at')->get()
            : collect();

        $supmessages = $projectName
            ? supcontact::where('projectname', $projectName)->orderByDesc('created_at')->get()
            : collect();

        return view('student.replay', [
            'Messages' => $messages,
            'supmessages' => $supmessages,
        ]);
    }
}
