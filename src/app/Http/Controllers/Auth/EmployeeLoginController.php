<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;


class EmployeeLoginController extends AuthenticatedSessionController
{
    public function show()
    {
        return view('employee.auth.login');
    }

    public function store(FortifyLoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::guard('web')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/attendance');
        }

        throw ValidationException::withMessages([
            'email' => ['ログイン情報が登録されていません'],
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect('/login');
    }
}
