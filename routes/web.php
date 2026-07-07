<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
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
    Route::get('supervisorSignup', fn () => redirect()->route('login'))->name('supervisor.login');
    Route::post('supervisorSignup', fn () => redirect()->route('login'));
});

Route::middleware(['auth', 'account.active'])->group(function () {
    Route::get('/complete-email', [UserController::class, 'showCompleteEmail'])->name('profile.complete-email');
    Route::post('/complete-email', [UserController::class, 'storeCompleteEmail'])->name('profile.complete-email.store');
    Route::post('/logout', [UserController::class, 'Out'])->name('logout');
});

Route::middleware(['auth', 'account.active', 'email.complete', 'admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');
    Route::post('/admin/users/{user}/deactivate', [AdminController::class, 'deactivateUser'])->name('admin.users.deactivate');
    Route::post('/admin/users/{user}/activate', [AdminController::class, 'activateUser'])->name('admin.users.activate');
    Route::get('/admin/users/{user}/reset-password', [AdminController::class, 'showResetPassword'])->name('admin.users.reset-password');
    Route::post('/admin/users/{user}/reset-password', [AdminController::class, 'resetUserPassword'])->name('admin.users.reset-password.store');
    Route::get('/admin/projects', [AdminController::class, 'projects'])->name('admin.projects');
    Route::get('/admin/requests', [AdminController::class, 'requests'])->name('admin.requests');
    Route::get('/admin/ideas', [AdminController::class, 'ideas'])->name('admin.ideas');
    Route::get('/admin/submissions', [AdminController::class, 'submissions'])->name('admin.submissions');
    Route::get('/admin/supervisors/create', [AdminController::class, 'createSupervisor'])->name('admin.supervisors.create');
    Route::post('/admin/supervisors', [AdminController::class, 'storeSupervisor'])->name('admin.supervisors.store');
    Route::get('/admin/students/create', [AdminController::class, 'createStudent'])->name('admin.students.create');
    Route::post('/admin/students', [AdminController::class, 'storeStudent'])->name('admin.students.store');
});

Route::get('/ForgetPassword', [UserController::class, 'showForm'])->name('password.request');
Route::post('/ForgetPassword', [UserController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [UserController::class, 'showForms'])->name('password.reset');
Route::post('/reset-password', [UserController::class, 'reset'])->name('password.update');

// Student portal
Route::middleware(['auth', 'account.active', 'email.complete', 'student'])->group(function () {
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
Route::middleware(['auth', 'account.active', 'email.complete', 'supervisor'])->group(function () {
    Route::get('supervisorDashboard', [SupervisorController::class, 'showdash'])->name('supervisor.dashboard');
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
