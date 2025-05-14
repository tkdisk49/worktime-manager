<?php

use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\ApprovalController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\EmployeeLoginController;
use App\Http\Controllers\Employee\EmployeeAttendanceController;
use App\Http\Controllers\Employee\EmployeeAttendanceModificationController;
use App\Http\Controllers\Employee\EmployeeRequestController;
use App\Http\Middleware\CheckAttendanceStatus;
use Illuminate\Support\Facades\Route;

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

// 管理者用ルート パス(/admin/...)
Route::prefix('admin')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminLoginController::class, 'create'])->name('admin.login');
        Route::post('/login', [AdminLoginController::class, 'store'])->name('admin.login.store');
    });

    Route::middleware('auth:admin')->group(function () {
        Route::post('/logout', [AdminLoginController::class, 'destroy'])->name('admin.logout');

        Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.attendance.list');

        Route::patch('/attendance/{id}', [AdminAttendanceController::class, 'update'])->name('admin.attendance.update');

        Route::get('/staff/list', [StaffController::class, 'index'])->name('admin.staff.index');
        Route::get('/attendance/staff/{id}', [StaffController::class, 'showMonthlyAttendance'])->name('admin.staff.attendance.monthly');
        Route::get('/attendance/staff/{id}/export', [StaffController::class, 'exportCsv'])->name('admin.staff.attendance.monthly.csv');
    });
});

// 管理者用ルート プレフィックスなし
Route::middleware('auth:admin')->group(function () {
    Route::get('/stamp_correction_request/approve/{attendance_correct_request}', [ApprovalController::class, 'show'])->name('admin.approval.show');
    Route::patch('/stamp_correction_request/approve/{attendance_correct_request}', [ApprovalController::class, 'update'])->name('admin.approval.update');
});

// 一般ユーザー用ルート
Route::middleware('guest:web')->group(function () {
    Route::get('/login', [EmployeeLoginController::class, 'create'])->name('login');
    Route::post('/login', [EmployeeLoginController::class, 'store'])->name('login.store');
});

// メール認証ルート
Route::middleware('auth:web')->group(function () {
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->middleware('signed')->name('verification.verify');
    Route::post('/email/resend', [EmailVerificationController::class, 'resend'])->middleware('throttle:6,1')->name('verification.resend');
});

// 開発環境でのMailHogへの画面遷移
if (app()->isLocal() || config('app.env') === 'testing') {
    Route::get('/mailhog', function () {
        return redirect('http://localhost:8025');
    })->name('mailhog.redirect');
}

// 勤怠登録画面表示時にwork_statusチェックを実施
Route::middleware(['auth:web', 'verified', CheckAttendanceStatus::class])->group(function () {
    Route::get('/attendance', [EmployeeAttendanceController::class, 'create'])->name('attendance.create');
});

Route::middleware(['auth:web', 'verified'])->group(function () {
    Route::post('/logout', [EmployeeLoginController::class, 'destroy'])->name('logout');

    Route::get('/attendance/list', [EmployeeAttendanceController::class, 'index'])->name('attendance.index');

    Route::post('/attendance', [EmployeeAttendanceController::class, 'recordWorkStart'])->name('attendance.work_start');
    Route::patch('/attendance', [EmployeeAttendanceController::class, 'recordWorkEnd'])->name('attendance.work_end');

    Route::post('/attendance/break', [EmployeeAttendanceController::class, 'recordBreakStart'])->name('attendance.break_start');
    Route::patch('/attendance/break', [EmployeeAttendanceController::class, 'recordBreakEnd'])->name('attendance.break_end');

    Route::post('/attendance/{id}', [EmployeeAttendanceModificationController::class, 'store'])->name('attendance.modification.store');
});

// 管理者 一般ユーザー同一ルート
Route::middleware('auth:web,admin')->group(function () {
    Route::get('/attendance/{id}', [EmployeeAttendanceModificationController::class, 'show'])->name('attendance.modification.show');

    Route::get('/stamp_correction_request/list', [EmployeeRequestController::class, 'index'])->name('employee.requests.index');
});
