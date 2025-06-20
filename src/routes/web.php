<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\Admin\AuthController;
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

// 一般ユーザーのログイン・登録（先に定義）
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');  // 一般ユーザー用のログインビュー
    })->name('login');

    Route::get('/register', function () {
        return view('auth.register');  // 一般ユーザー用の登録ビュー
    })->name('register');
});

// 管理者用ルート
Route::prefix('admin')->name('admin.')->group(function () {
    // ログインフォーム表示
    Route::get('/login', [AuthController::class, 'showLoginForm'])
        ->middleware('guest')
        ->name('login');

    // ログイン処理のルート
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware(['web', 'guest'])
        ->name('login.post');

    // 管理者用ダッシュボード等
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/attendance/list', [AdminAttendanceController::class, 'list'])
            ->name('attendance.list');
        Route::get('/attendance/{id}', [AdminAttendanceController::class, 'show'])->name('attendance.show');
        Route::put('/attendance/{id}', [AdminAttendanceController::class, 'update'])->name('attendance.update');
        Route::post('/logout', [AdminAttendanceController::class, 'logout'])->name('logout');
        Route::get('/staff/list', [StaffController::class, 'list'])->name('staff.list');
        Route::get('/staff/monthly', [StaffController::class, 'monthly'])->name('staff.monthly');
        Route::get('/staff/attendance-list/{id}', [StaffController::class, 'attendanceList'])->name('staff.attendance-list');
        Route::get('/stamp_correction_request/list', [AdminStampCorrectionRequestController::class, 'list'])->name('stamp_correction_request.list');
        Route::get('/stamp_correction_request/approve/{attendance_correction_request}', [AdminStampCorrectionRequestController::class, 'showApprove'])->name('stamp_correction_request.showApprove');
        Route::post('/stamp_correction_request/approve/{attendance_correction_request}', [AdminStampCorrectionRequestController::class, 'approve'])->name('stamp_correction_request.approve');
        Route::get('/staff/{id}/attendance/export', [StaffController::class, 'export'])->name('staff.attendance.export');
    });
});

// 一般ユーザー用ルート
Route::middleware(['auth', 'email.verified'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');
    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'list'])->name('stamp-correction.list');
    Route::get('/attendance/{id}', [AttendanceController::class, 'show'])->name('attendance.show');
    Route::put('/attendance/{id}', [AttendanceController::class, 'update'])->name('attendance.update');
});

// メール認証関連のルート
Route::middleware(['auth'])->group(function () {
    Route::get('/email/verify', [EmailVerificationController::class, 'show'])
        ->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed'])
        ->name('verification.verify');

    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->middleware(['throttle:6,1'])
        ->name('verification.resend');
});




