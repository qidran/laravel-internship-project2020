<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
//Default
Route::get('/', function () {
return redirect('/login');
});

//Authentications
Auth::routes();

//Resources
Route::resource('admins', AdminController::class)->middleware('role:1');
Route::resource('users', UserController::class);
Route::resource('applications', ApplicationController::class );
Route::resource('holidays', HolidayController::class );

//Indexes
Route::get('/employee/dashboard', [UserController::class, 'index'])->middleware('role:2');
Route::get('/approver/dashboard', [UserController::class, 'index'])->middleware('role:3');


//Applications
Route::get('application/apply', [ApplicationController::class, 'create'])->name('applications.create');
Route::get('application/adminshow/{id}', [ApplicationController::class, 'adminAppShow'])->name('applications.adminAppShow');
Route::get('application/list', [ApplicationController::class, 'list'])->name('applications.list');
Route::get('application/delete/{id}', [ApplicationController::class,'destroy'])->name('applications.destroy');


//Admins
Route::get('admin/holidayadd', [HolidayController::class, 'create'])->name('holidays.create');
Route::get('admin/holidaylist', [HolidayController::class, 'index'])->name('holidays.index');
Route::get('admin/holidayedit/{id}', [HolidayController::class, 'edit'])->name('holidays.edit');
Route::get('admin/holidaylist/delete/{id}', [HolidayController::class,'destroy'])->name('holidays.destroy');

Route::get('report/overview', [ReportController::class, 'overview'])->name('report.overview');
Route::get('report/individual', [ReportController::class, 'individual'])->name('report.individual');
Route::get('report/individual/{id}', [ReportController::class, 'findindividual'])->name('report.findindividual');

Route::get('admin/employeeadd', [AdminController::class, 'employeeadd'])->name('admins.employeeadd');
Route::get('admin/employeelist',[AdminController::class, 'employeelist'])->name('admins.employeelist');
Route::get('admin/employeeshow/{id}',[AdminController::class, 'show'])->name('admins.empshow');
Route::get('admin/employeelist/delete/{id}',[AdminController::class, 'destroy'])->name('admins.delete');
Route::get('admin/employeeedit/{id}',[AdminController::class, 'edit'])->name('admins.empedit');
Route::post('admin/employeeedit/{id}',[AdminController::class, 'update'])->name('admins.update');

Route::get('admin/applicationlist',[AdminController::class, 'applicationlist'])->name('admins.applicationlist');

//Employees & Approvers
Route::get('approver/pendinglist/{id}',[UserController::class, 'pendinglist'])->name('users.pendinglist');
Route::get('approver/applicantlist/{id}', [UserController::class, 'applicantlist'])->name('users.applicantlist');
Route::get('approver/approve/{id}', [UserController::class, 'approve'])->name('users.approve');
Route::get('approver/reject/{id}', [UserController::class, 'reject'])->name('users.reject');
Route::get('employee/employeedetail/{id}', [UserController::class ,'show'])->name('users.show');

//Change Password

Route::get('change-password', [ResetPasswordController::class , 'change_page'])->name('change_page');
Route::post('change-password', [ResetPasswordController::class , 'change'])->name('users.change_password');

//Forgot Password
Route::get('forgot-password', [ForgotPasswordController::class , 'getEmail']);
Route::post('forgot-password', [ForgotPasswordController::class , 'postEmail'])->name('forgot.postEmail');

Route::get('forgot-reset-password/{token}', [ForgotPasswordController::class , 'reset_page']);
Route::post('forgot-reset-password', [ForgotPasswordController::class , 'reset'])->name('forgot.reset');


