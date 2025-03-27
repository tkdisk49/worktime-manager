<?php

use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmployeeLoginController;
use App\Http\Controllers\Employee\EmployeeAttendanceController;
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

// 勤怠登録画面
Route::middleware('auth')->group(function () {
    Route::get('/attendance', [EmployeeAttendanceController::class, 'create'])->name('attendance.create');
});

// 管理者用勤怠一覧画面
Route::prefix('admin')->middleware('auth')->group(function () {
    Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.attendance.list');
});

// 管理者ログイン
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'show'])->name('admin.login');
    Route::post('/login', [AdminLoginController::class, 'store']);
    Route::post('/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');
});

// 一般ユーザーログイン
Route::get('/login', [EmployeeLoginController::class, 'show'])->name('login');
Route::post('/login', [EmployeeLoginController::class, 'store']);
Route::post('/logout', [EmployeeLoginController::class, 'logout'])->name('logout');