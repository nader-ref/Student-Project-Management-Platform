<?php

namespace App\Http\Controllers;
use App\Models\Contact;
use App\Models\Supcontact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
class MessageController extends Controller
{
    public function message(Request $request)
    {
        $validated = $request->validate([
            'supname'   => 'required|string',
            'subject'   => 'required|string|max:255',
            'Message'   => 'nullable|string|max:255',
        ]);
        contact::create([
            'email'              =>Session::get('email'),
            'supname'            => $validated['supname'],
            'subject'           => $validated['subject'],
            'Message'             => $validated['Message'],
        ]);
        return redirect()->back()->with('success', 'Your request has been submitted successfully.');
    }

      public function replay()
    {
        return view('student.replay',['Messages'=> Contact::All(),'supmessages'=>Supcontact::All()]);
    }
}
