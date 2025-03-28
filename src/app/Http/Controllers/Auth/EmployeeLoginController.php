<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Employee\AttemptToAuthenticate;
use App\Responses\EmployeeLoginResponse;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Pipeline;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession;
use Laravel\Fortify\Http\Requests\LoginRequest;


class EmployeeLoginController extends Controller
{
    /**
     * The guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected $guard;

    /**
     * Create a new controller instance.
     *
     * @param  \Illuminate\Contracts\Auth\StatefulGuard  $guard
     * @return void
     */
    public function __construct(StatefulGuard $guard)
    {
        $this->guard = $guard;
    }

    /**
     * Show the login view.
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
     */
    public function create()
    {
        return view('employee.auth.login', ['guard' => 'web']);
    }

    /**
     * Attempt to authenticate a new session.
     *
     * @param  \Laravel\Fortify\Http\Requests\LoginRequest   $request
     * @return mixed
     */
    public function store(LoginRequest $request)
    {
        return $this->loginPipeline($request)->then(function ($request) {
            return app(EmployeeLoginResponse::class);
        });
    }

    /**
     * Get the authentication pipeline instance.
     *
     * @param  \Laravel\Fortify\Http\Requests\LoginRequest  $request
     * @return \Illuminate\Pipeline\Pipeline
     */
    protected function loginPipeline(LoginRequest $request)
    {
        return (new Pipeline(app()))->send($request)->through(array_filter([
            AttemptToAuthenticate::class,
            PrepareAuthenticatedSession::class,
        ]));
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        $this->guard->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect('/login');
    }

    // public function show()
    // {
    //     return view('employee.auth.login');
    // }

    // public function store(FortifyLoginRequest $request)
    // {
    //     $credentials = $request->only('email', 'password');

    //     if (Auth::guard('web')->attempt($credentials)) {
    //         $request->session()->regenerate();
    //         return redirect()->intended('/attendance');
    //     }

    //     throw ValidationException::withMessages([
    //         'email' => ['ログイン情報が登録されていません'],
    //     ]);
    // }

    // public function logout(Request $request)
    // {
    //     Auth::guard('web')->logout();

    //     if ($request->hasSession()) {
    //         $request->session()->invalidate();
    //         $request->session()->regenerateToken();
    //     }

    //     return redirect('/login');
    // }
}
