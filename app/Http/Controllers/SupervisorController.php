<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use App\Models\UniProject;
use App\Models\Supervisor;
use App\Models\Projectrequest;
use App\Models\Idea;
use Illuminate\Support\Facades\Validator;

class SupervisorController extends Controller
{
    public function show()
    {
        return view('supervisor.Login');
    }

    public function login(Request $request)
    {
         $log =request()->validate([
                        'name' => 'required|string',
                        'email' => 'required|string|email',
                        'password' => 'required|string',
                    ]);
                   
                    if(!Auth::attempt($log)){
                        throw ValidationException::withMessages([
                        'email' =>'sorry you dont have ana account',
                        
                        ]);
                    };
                    $sup = Supervisor::where('email', request('email'))->first();
                    $user = User::where('email', request('email'))->first();
                    if (!$user->hasRole($request->role)) {
                                return response()->json(["message" => "user not found"], 404);
                    }

              
                    request()->session()->regenerate();
                    Session::put('email',request('email'));
                    Session::put('id',$sup->id);
                    Session::put('name',$user->name);
                    return redirect('/supervisorDashboard')->with('success', 'User registered successfully!');
      
    }

    public function showdash()
    {
        return view('supervisor.dashboard',['projects'=> UniProject::All(),
                                            'requests'=>Projectrequest::All(),
                                            'ideas'=>Idea::All()]);
    }

    public function logout()
    {
         Session::flush();
        return redirect('/')->with('success', 'User Unregistered successfully!');
    }

     public function addproject(Request $request)
    {
        // Validation rules
        $validator = Validator::make($request->all(), [
            'project_name'    => 'required|string|max:255',
            'description'     => 'required|string',
            'department'      => 'required|in:software,ai,network',
            'taken'           => 'required|in:Yes,No',
            'students_number' => 'nullable|integer|min:0|max:3',
            'student_one_name'=> 'nullable|string|max:255',
            'student_one_id'  => 'nullable|string|max:50',
            'student_two_name'=> 'nullable|string|max:255',
            'student_two_id'  => 'nullable|string|max:50',
            'student_three_name'=> 'nullable|string|max:255',
            'student_three_id'=> 'nullable|string|max:50',
            'seminar1_date'   => 'required|date',
            'seminar2_date'   => 'required|date',
            'seminar3_date'   => 'required|date',
            'final_date'      => 'required|date|after_or_equal:seminar3_date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                             ->withErrors($validator)
                             ->withInput();
        }
        $sup = supervisor::where('email',Session('email'))->first();
        if($request->taken =="Yes"){
            $tak = 1;
        }else{
            $tak= 0;
        }

        // Create record
        UniProject::create([
            'name'=>$request->project_name,
            'description'=>$request->description,
            'supervisor_id' =>$sup->id,
            'department'=>$request->department,
            'taken'=>$tak,
            'student_count'=> $request->students_number??null,
            'student_one_name'=> $request->student_one_name??null,
            'student_one_id'=> $request->student_one_id??null,
            'student_two_name'=> $request->student_two_name??null,
            'student_two_id'=> $request->student_two_id??null,
            'student_three_name'=> $request->student_three_name??null,
            'student_three_id'=> $request->student_three_id??null,
            'seminar_1'=>$request->seminar1_date,
            'seminar_2'=>$request->seminar2_date,
            'seminar_3'=>$request->seminar3_date,
            'final'=>$request->final_date,
        ]);

        
        return redirect()->back()->with('success', 'Project registered successfully!');
    }

    public function acceptrequest(Request $request)
    {
        $projectId = $request->input('project');
        $requestId = $request->input('request');

        $project = UniProject::where('id', $projectId)->first();
        $projectRequest = Projectrequest::where('id', $requestId)->first();
         // Validate existence
        if (!$project) {
            return back()->with('error', 'Project not found.');
        }

        if (!$projectRequest) {
            return back()->with('error', 'Project request not found.');
        }

     
        $project->taken = 1;
        $project->student_count =$projectRequest->count;
        $project->student_one_name =$projectRequest->nameone;
        $project->student_one_id =$projectRequest->oneid;
        $project->student_two_name=$projectRequest->nametwo;
        $project->student_two_id =$projectRequest->onetwo;
        $project->student_three_name=$projectRequest->namethree;
        $project->student_two_id =$projectRequest->onetwo;
        $project->supervisor_id ==Session::get('id');
        $project->save();

        $projectRequest->accepted = 1;
        $projectRequest->save();

        return redirect()->back()->with('success', 'Project registered successfully!');
    }

    
    public function acceptidea(Request $request)
    {
        
        

        $idea = idea::where('id', $request->idea)->first();
        if (!$idea ) {
            return back()->with('error', 'Project request not found.');
        }

       
        UniProject::create([
            'name'=>$idea->projectname,
            'description'=>Null,
            'supervisor_id' =>Session::get('id'),
            'department'=>'Software Engineering',
            'taken'=>1,
            'student_count'=> $idea->count,
            'student_one_name'=> $idea->nameone??null,
            'student_one_id'=> $idea->oneid??null,
            'student_two_name'=> $idea->nametwo??null,
            'student_two_id'=> $idea->twoid??null,
            'student_three_name'=> $idea->namethree??null,
            'student_three_id'=> $idea->threeid??null,
            'seminar_1'=>Null,
            'seminar_2'=>Null,
            'seminar_3'=>Null,
            'final'=>Null,
        ]);
        $idea->accepted =1;
        $idea->save();

        return redirect()->back()->with('success', 'Project registered successfully!');
    }

     public function rejectidea(Request $request)
    {
        
        

        $idea = idea::where('id', $request->idea)->first();
        if (!$idea ) {
            return back()->with('error', 'Project request not found.');
        }

       
        $idea->rejected=1;
        $idea->reason=$request->reason;
        $idea->save();

        return redirect()->back()->with('success', 'Project registered successfully!');
    }
}
