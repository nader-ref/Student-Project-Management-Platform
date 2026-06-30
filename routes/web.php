<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProjectrequestController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProjectSubmissionController;
use App\Http\Controllers\SupervisorController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// Guest auth routes
Route::middleware('guest')->group(function () {
    Route::get('signup', [UserController::class, 'show'])->name('register');
    Route::post('signup', [UserController::class, 'Create']);
    Route::get('Login', [UserController::class, 'Show2'])->name('login');
    Route::post('Login', [UserController::class, 'Enter']);
    Route::get('supervisorSignup', [SupervisorController::class, 'show'])->name('supervisor.login');
    Route::post('supervisorSignup', [SupervisorController::class, 'login']);
});

Route::get('/ForgetPassword', [UserController::class, 'showForm'])->name('password.request');
Route::post('/ForgetPassword', [UserController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [UserController::class, 'showForms'])->name('password.reset');
Route::post('/reset-password', [UserController::class, 'reset'])->name('password.update');

// Student portal
Route::middleware('student')->group(function () {
    Route::get('/Logout', [UserController::class, 'Out'])->name('logout');
    Route::get('/StudentDashboard', [UserController::class, 'showDash'])->name('student.dashboard');
    Route::post('/RequstAdd', [ProjectrequestController::class, 'request']);
    Route::get('/StudentDashboard/acceptance', [ProjectrequestController::class, 'accept']);
    Route::post('/RequstIdea', [ProjectrequestController::class, 'idea']);
    Route::get('/StudentDashboard/acceptanceidea', [ProjectrequestController::class, 'acceptidea']);
    Route::post('/Message', [MessageController::class, 'message']);
    Route::get('/StudentDashboard/replay', [MessageController::class, 'replay']);
    Route::post('/student/submission', [ProjectSubmissionController::class, 'store']);
    Route::get('/student/submission/{submission}/download', [ProjectSubmissionController::class, 'download'])
        ->name('student.submission.download');
    Route::get('ChangePassword', [UserController::class, 'Change']);
    Route::post('change', [UserController::class, 'changepost']);
});

// Supervisor portal
Route::middleware('supervisor')->group(function () {
    Route::get('supervisorDashboard', [SupervisorController::class, 'showdash'])->name('supervisor.dashboard');
    Route::get('supervisorDashboard/logout', [SupervisorController::class, 'logout'])->name('supervisor.logout');
    Route::post('/addproject', [SupervisorController::class, 'addproject']);
    Route::post('/updateproject', [SupervisorController::class, 'updateproject']);
    Route::post('/acceptrequest', [SupervisorController::class, 'acceptrequest']);
    Route::post('/rejectrequest', [SupervisorController::class, 'rejectrequest']);
    Route::post('/acceptidea', [SupervisorController::class, 'acceptidea']);
    Route::post('/rejectidea', [SupervisorController::class, 'rejectidea']);
    Route::post('/supervisor/reply', [SupervisorController::class, 'replycontact']);
    Route::post('/supervisor/announce', [SupervisorController::class, 'sendannouncement']);
    Route::post('/supervisor/changepassword', [SupervisorController::class, 'changepassword']);
    Route::post('/supervisor/submission/review', [ProjectSubmissionController::class, 'review']);
    Route::get('/supervisor/submission/{submission}/download', [ProjectSubmissionController::class, 'download'])
        ->name('supervisor.submission.download');
});
