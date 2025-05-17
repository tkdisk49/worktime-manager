<?php

namespace App\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Support\Str;

class EmployeeLoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        $intended = redirect()->intended('/attendance')->getTargetUrl();

        if (Str::startsWith($intended, url('/admin'))) {
            return redirect('/attendance');
        }

        return $request->wantsJson()
            ? response()->json(['two_factor' => false])
            : redirect()->intended('/attendance');
    }
}
