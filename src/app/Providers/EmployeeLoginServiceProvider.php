<?php

namespace App\Providers;

use App\Actions\Employee\AttemptToAuthenticate;
use App\Http\Controllers\Auth\EmployeeLoginController;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class EmployeeLoginServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app
            ->when([EmployeeLoginController::class, AttemptToAuthenticate::class])
            ->needs(StatefulGuard::class)
            ->give(function () {
                return Auth::guard('web');
            });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
