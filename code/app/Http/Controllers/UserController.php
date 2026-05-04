<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UniProject;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;


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
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|max:8|confirmed',// This checks
            // if password matches confirm_password
            ]);

            // Create a new user instance
            $user = User::create([
                'name'=> request('name'),
                'email'=>request('email'),
                'password'=>Hash::make(request('password'))
            ]);

            if (request()->has('remember')) {
                cookie()->queue('remember_user', $user->id, 60 * 24 * 7); // 7 days
            }
            $user->addRole('user');
            //how to see the cookie
            //Right-click your page → Inspect → Application tab
            //Go to Storage → Cookies → your site
            //Look for a cookie named remember_user
             Auth::login($user);
            Session::put('email',request('email'));
            Session::put('name',request('name'));
            return redirect('/StudentDashboard')->with('success', 'User registered successfully!');
    }

      public function Out()
    {
         Session::flush();
        return redirect('/')->with('success', 'User Unregistered successfully!');
    }

    public function Show2()
    {
         return view('register.Login');
    }

    public function Enter(Request $request)
    {
                    $log =request()->validate([
                        'email' => 'required|string|email',
                        'password' => 'required|string',
                    ]);
                   
                    if(!Auth::attempt($log)){
                    //    $admin = Admin::where('email',request('email'))->first();
                    //    if($admin){
                    //      if(Hash::check(request('password'), $admin->password)&& $admin->name == request('name')){
                    //         Session::put('email',request('email'));
                    //         Session::put('name',request('name'));
                    //         return view('admin.dashboard',['users'=>MyUser::All(),'finishs'=>finish::All(),'rates'=>rate::All(), 'contacts'=>AdminRate::All(),'images'=>Adminprofile::all()]);
                    //      }
                    //    }

                        throw ValidationException::withMessages([
                        'email' =>'sorry you dont have ana account',
                        
                        ]);
                    };
                    if (request()->has('remember')) {
                        cookie()->queue('remember_user', $user->id, 60 * 24 * 7); // 7 days
                    }
                    $user = User::where('email', request('email'))->first();
                    if (!$user->hasRole($request->role)) {
                                return response()->json(["message" => "user not found"], 404);
                    }

              

                    //how to see the cookie
                    //Right-click your page → Inspect → Application tab
                    //Go to Storage → Cookies → your site
                    //Look for a cookie named remember_user
         
                    
                    request()->session()->regenerate();
                    Session::put('email',request('email'));
                    Session::put('name',$user->name);
                    return redirect('/StudentDashboard')->with('success', 'User registered successfully!');
      

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
        $userMain = User::where('email', $request->email)->first();
        $userMain->password = Hash::make($request->password);
        $userMain->save();

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect('/Login')->with('status', 'Password reset successful!')
            : back()->withErrors(['email' => [__($status)]]);
            //search the last link in the laravel log 
    }
    
    public function showDash()
    {
        return view('student.dashboard',['projects'=> UniProject::All()]);
    }
}