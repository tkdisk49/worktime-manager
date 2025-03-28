<?php

use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Auth\AdminLoginController;
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

// 管理者用ルート
Route::prefix('admin')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminLoginController::class, 'create'])->name('admin.login');
        Route::post('/login', [AdminLoginController::class, 'store']);
    });

    Route::middleware('auth:admin')->group(function () {
        Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.attendance.list');
        Route::post('/logout', [AdminLoginController::class, 'destroy'])->name('admin.logout');
    });
});

// 一般ユーザー用ルート
Route::middleware('guest:web')->group(function () {
    Route::get('/login', [EmployeeLoginController::class, 'show'])->name('login');
    Route::post('/login', [EmployeeLoginController::class, 'store']);
});

Route::middleware('auth:web')->group(function () {
    Route::get('/attendance', [EmployeeAttendanceController::class, 'create'])->name('attendance.create');
    Route::post('/logout', [EmployeeLoginController::class, 'logout'])->name('logout');
});
