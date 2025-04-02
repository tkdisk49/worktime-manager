<?php

use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\EmployeeLoginController;
use App\Http\Controllers\Employee\EmployeeAttendanceController;
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

// 管理者用ルート
Route::prefix('admin')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminLoginController::class, 'create'])->name('admin.login');
        Route::post('/login', [AdminLoginController::class, 'store']);
    });

    Route::middleware('auth:admin')->group(function () {
        Route::post('/logout', [AdminLoginController::class, 'destroy'])->name('admin.logout');

        Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.attendance.list');
    });
});

// 一般ユーザー用ルート
Route::middleware('guest:web')->group(function () {
    Route::get('/login', [EmployeeLoginController::class, 'create'])->name('login');
    Route::post('/login', [EmployeeLoginController::class, 'store']);
});

// 勤怠登録画面表示時にwork_statusチェックを実施
Route::middleware(['auth:web', CheckAttendanceStatus::class])->group(function () {
    Route::get('/attendance', [EmployeeAttendanceController::class, 'create'])->name('attendance.create');
});

Route::middleware('auth:web')->group(function () {
    Route::post('/logout', [EmployeeLoginController::class, 'destroy'])->name('logout');

    Route::get('/attendance/list', [EmployeeAttendanceController::class, 'index'])->name('attendance.index');

    Route::post('/attendance', [EmployeeAttendanceController::class, 'recordWorkStart'])->name('attendance.work_start');
    Route::patch('/attendance', [EmployeeAttendanceController::class, 'recordWorkEnd'])->name('attendance.work_end');

    Route::post('/attendance/break', [EmployeeAttendanceController::class, 'recordBreakStart'])->name('attendance.break_start');
    Route::patch('/attendance/break', [EmployeeAttendanceController::class, 'recordBreakEnd'])->name('attendance.break_end');
});
