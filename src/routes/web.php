<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\StampCorrectionRequestController as AdminStampCorrectionRequestController;
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

// 一般ユーザー用ルート
Route::middleware('auth')->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');
    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'list'])->name('stamp-correction.list');
    Route::get('/attendance/{id}', [AttendanceController::class, 'show'])->name('attendance.show');
    Route::put('/attendance/{id}', [AttendanceController::class, 'update'])->name('attendance.update');
});

// 管理者ログイン画面
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
});

// 管理者用ダッシュボード等
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/attendance/list', [AdminAttendanceController::class, 'list'])->name('attendance.list');
    Route::get('/attendance/{id}', [AdminAttendanceController::class, 'show'])->name('attendance.show');
    Route::put('/attendance/{id}', [AdminAttendanceController::class, 'update'])->name('attendance.update');
    Route::post('/logout', [AdminAttendanceController::class, 'logout'])->name('logout');
    Route::get('/staff/list', [StaffController::class, 'list'])->name('staff.list');
    Route::get('/staff/monthly', [StaffController::class, 'monthly'])->name('staff.monthly');
    Route::get('/staff/attendance-list/{id}', [StaffController::class, 'attendanceList'])->name('staff.attendance-list');
    Route::get('/stamp_correction_request/list', [AdminStampCorrectionRequestController::class, 'list'])->name('stamp_correction_request.list');
    Route::get('/stamp_correction_request/approve/{attendance_correction_request}', [AdminStampCorrectionRequestController::class, 'showApprove'])->name('stamp_correction_request.showApprove');
    Route::post('/stamp_correction_request/approve/{attendance_correction_request}', [AdminStampCorrectionRequestController::class, 'approve'])->name('stamp_correction_request.approve');
});


