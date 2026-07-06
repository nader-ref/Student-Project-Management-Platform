<?php

namespace App\Http\Controllers;

use App\Models\contact;
use App\Models\ProjectSubmission;
use App\Models\Supervisor;
use App\Models\supcontact;
use App\Models\UniProject;
use App\Models\User;
use App\Services\StudentEnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laratrust\Models\Role;

class UserController extends Controller
{
    public function show()
    {
        return view('register.signup');
    }

    public function Create()
    {
        
            request()->validate([
            'name' => 'required|string|max:255',
            'university_number' => 'required|string|max:255|unique:users,university_number',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            ]);

            // Create a new user instance
            $user = User::create([
                'name'=> request('name'),
                'university_number' => request('university_number'),
                'email'=>request('email'),
                'password'=>Hash::make(request('password'))
            ]);

            if (request()->has('remember')) {
                cookie()->queue('remember_user', $user->id, 60 * 24 * 7); // 7 days
            }

            Role::firstOrCreate(
                ['name' => 'student'],
                ['display_name' => 'Student', 'description' => 'Student role']
            );
            $user->addRole('student');
            //how to see the cookie
            //Right-click your page → Inspect → Application tab
            //Go to Storage → Cookies → your site
            //Look for a cookie named remember_user
             Auth::login($user);
            return redirect('/StudentDashboard')->with('success', 'User registered successfully!');
    }

      public function Out()
    {
        Auth::logout();
        Session::flush();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect('/')->with('success', 'Logged out successfully.');
    }

    public function Show2()
    {
         return view('register.Login');
    }

    public function Enter(Request $request)
    {
                    $log =request()->validate([
                        'university_number' => 'required|string',
                        'password' => 'required|string',
                    ]);
                   
                    if(!Auth::attempt($log, $request->boolean('remember'))){
                    //    $admin = Admin::where('email',request('email'))->first();
                    //    if($admin){
                    //      if(Hash::check(request('password'), $admin->password)&& $admin->name == request('name')){
                    //         Session::put('email',request('email'));
                    //         Session::put('name',request('name'));
                    //         return view('admin.dashboard',['users'=>MyUser::All(),'finishs'=>finish::All(),'rates'=>rate::All(), 'contacts'=>AdminRate::All(),'images'=>Adminprofile::all()]);
                    //      }
                    //    }

                        throw ValidationException::withMessages([
                        'university_number' =>'sorry you dont have an account',
                        
                        ]);
                    };
                    $user = Auth::user();
                    $dashboardRoute = $user->resolveDashboardRoute();

                    if (! $dashboardRoute) {
                        Auth::logout();

                        throw ValidationException::withMessages([
                            'university_number' => 'This account does not have access to a portal.',
                        ]);
                    }

              

                    //how to see the cookie
                    //Right-click your page → Inspect → Application tab
                    //Go to Storage → Cookies → your site
                    //Look for a cookie named remember_user
         
                    
                    request()->session()->regenerate();

                    if (blank($user->email)) {
                        return redirect()->route('profile.complete-email')->with('success', 'Signed in successfully!');
                    }

                    return redirect($dashboardRoute)->with('success', 'Signed in successfully!');
      

    }

    public function showCompleteEmail()
    {
        $user = Auth::user();

        if (filled($user->email)) {
            return redirect($user->resolveDashboardRoute() ?? '/');
        }

        return view('complete-email');
    }

    public function storeCompleteEmail(Request $request)
    {
        $user = Auth::user();

        if (filled($user->email)) {
            return redirect($user->resolveDashboardRoute() ?? '/');
        }

        $validated = $request->validate([
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
                Rule::unique('supervisors', 'email')->ignore($user->supervisor?->id),
            ],
        ]);

        DB::transaction(function () use ($user, $validated) {
            $user->update(['email' => $validated['email']]);

            if ($user->supervisor) {
                $user->supervisor->update(['email' => $validated['email']]);
            }
        });

        $dashboardRoute = $user->resolveDashboardRoute();

        if (! $dashboardRoute) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'This account does not have access to a portal.',
            ]);
        }

        return redirect($dashboardRoute)->with('success', 'Email saved successfully.');
    }

    public function Change()
    {
         return view('register.ChangePassword');
    }

    public function changepost(Request $request)
    {
        
        request()->validate( [
            'email' => 'required|email|exists:users,email',
            'old' => 'required|string',
            'new' => 'required|string|min:8', 
        ], [
            'email.exists' => 'The provided email does not exist in our records.',
        ]);

        
        $user = User::where('email', $request->email)->first();

        
        if (!Hash::check($request->old, $user->password)) {
            return redirect()->back()->withErrors([
                'old' => 'The old password is incorrect.'
            ])->withInput();
        }

        if($request->new == $request->password_confirmation){
        $user->password = Hash::make($request->new);
        $user->save();
        }else{
            return redirect()->back()->with('failed', 'Password not match');
        }
        
        return redirect()->back()->with('success', 'Password changed successfully!');
    }

    public function showForm()
    {
         return view('register.ForgetPassword');
    }
    

    // Handle form submission: generate token, store it, log the reset link
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        // Check the status and respond accordingly
        return $status === Password::RESET_LINK_SENT
                    ? back()->with(['status' => __($status)])
                    : back()->withErrors(['email' => __($status)]);
    }

    public function showForms($token)
    {
        return view('register.reset-password', ['token' => $token]);
    }

    // Handle the password reset
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', 'Password reset successful!')
            : back()->withErrors(['email' => [__($status)]]);
    }
    
    public function showDash()
    {
        $projects = UniProject::with('supervisor')->get();
        $enrollment = StudentEnrollmentService::resolve(Auth::user());
        $submissions = collect();
        $progress = null;
        $nextSteps = collect();
        $recentActivity = collect();

        if ($enrollment['project']) {
            $submissions = ProjectSubmission::with('submittedBy')
                ->where('project_id', $enrollment['project']->id)
                ->orderByDesc('created_at')
                ->get();
            $progress = StudentEnrollmentService::computeProgress(
                $enrollment['project'],
                $enrollment['milestones'],
                $submissions,
            );

            $student = Auth::user();
            $contacts = $student
                ? contact::with(['student', 'supervisor'])
                    ->where('student_user_id', $student->id)
                    ->orderByDesc('updated_at')
                    ->get()
                : collect();
            $announcements = supcontact::with(['supervisor', 'project'])
                ->where('project_id', $enrollment['project']->id)
                ->orderByDesc('created_at')
                ->get();

            $nextSteps = StudentEnrollmentService::buildNextSteps(
                $enrollment['project'],
                $enrollment['milestones'],
                $submissions,
                $enrollment['nextMilestone'],
                $contacts,
            );
            $recentActivity = StudentEnrollmentService::buildRecentActivity(
                $submissions,
                $contacts,
                $announcements,
            );
        }

        $previousEnrollmentMode = Session::get('student_dashboard_enrollment_mode');
        $previousEnrolledProjectId = Session::get('student_dashboard_enrolled_project_id');
        $currentEnrolledProjectId = $enrollment['project']?->id;
        $showEnrolledBanner = $enrollment['mode'] === StudentEnrollmentService::MODE_ENROLLED
            && $currentEnrolledProjectId
            && $previousEnrollmentMode !== null
            && (
                $previousEnrollmentMode !== StudentEnrollmentService::MODE_ENROLLED
                || $previousEnrolledProjectId !== $currentEnrolledProjectId
            );

        Session::put('student_dashboard_enrollment_mode', $enrollment['mode']);
        Session::put('student_dashboard_enrolled_project_id', $currentEnrolledProjectId);

        return view('student.dashboard', [
            'projects' => $projects,
            'supervisors' => Supervisor::orderBy('name')->get(),
            'stats' => [
                'totalProjects' => $projects->count(),
                'availableProjects' => $projects->where('taken', 0)->count(),
                'takenProjects' => $projects->where('taken', 1)->count(),
                'supervisors' => Supervisor::count(),
            ],
            'enrollmentMode' => $enrollment['mode'],
            'enrolledProject' => $enrollment['project'],
            'pendingRequest' => $enrollment['pendingRequest'],
            'pendingIdea' => $enrollment['pendingIdea'],
            'teamMembers' => collect($enrollment['teamMembers']),
            'milestones' => collect($enrollment['milestones']),
            'nextMilestone' => $enrollment['nextMilestone'],
            'submissions' => $submissions,
            'progress' => $progress,
            'nextSteps' => $nextSteps,
            'recentActivity' => $recentActivity,
            'showEnrolledBanner' => $showEnrolledBanner,
            'milestoneLabels' => StudentEnrollmentService::milestoneLabels(),
        ]);
    }
}