<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\LogoutResponse;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;

class AdminLoginController extends AuthenticatedSessionController
{
    public function show()
    {
        return view('admin.auth.login');
    }

    public function store(FortifyLoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::guard('admin')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('admin/attendance/list');
        }

        throw ValidationException::withMessages([
            'email' => ['ログイン情報が登録されていません'],
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect('/admin/login');
    }
}
