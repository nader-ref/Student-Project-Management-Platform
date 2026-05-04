<?php

use Illuminate\Support\Facades\Route;
Use App\Http\Controllers\UserController;
Use App\Http\Controllers\ProjectrequestController;
Use App\Http\Controllers\MessageController;
Use App\Http\Controllers\SupervisorController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

//register
Route::get('signup',[UserController::class,'show']);
Route::post('signup',[UserController::class,'Create']);
Route::get('/Logout',[UserController::class,'Out']);
Route::get('Login',[UserController::class,'Show2']);
Route::post('Login',[UserController::class,'Enter']);
Route::get('ChangePassword',[UserController::class,'Change']);
Route::post('change',[UserController::class,'changepost']);


Route::get('/ForgetPassword', [UserController::class, 'showForm'])->name('password.request');

// Handle forgot password form submission
Route::post('/ForgetPassword', [UserController::class, 'sendResetLink'])->name('password.email');

// Show reset password form (with token)
Route::get('/reset-password/{token}', [UserController::class, 'showForms'])->name('password.reset');

// Handle reset password submission
Route::post('/reset-password', [UserController::class, 'reset'])->name('password.update');


//Student Dashboard
Route::get('/StudentDashboard',[UserController::class,'showDash']);
Route::POST('/RequstAdd',[ProjectrequestController::class,'request']);
Route::get('/StudentDashboard/acceptance',[ProjectrequestController::class,'accept']);
Route::POST('/RequstIdea',[ProjectrequestController::class,'idea']);
Route::get('/StudentDashboard/acceptanceidea',[ProjectrequestController::class,'acceptidea']);
Route::POST('/Message',[MessageController::class,'message']);
Route::get('/StudentDashboard/replay',[MessageController::class,'replay']);

//supervisor
Route::get('supervisorSignup',[SupervisorController::class,'show']);
Route::post('supervisorSignup',[SupervisorController::class,'login']);
Route::get('supervisorDashboard',[SupervisorController::class,'showdash']);
Route::get('supervisorDashboard/logout',[SupervisorController::class,'logout']);
Route::post('/addproject',[SupervisorController::class,'addproject']);
Route::post('/acceptrequest',[SupervisorController::class,'acceptrequest']);
Route::post('/acceptidea',[SupervisorController::class,'acceptidea']);
Route::post('/',[SupervisorController::class,'rejectidea']);