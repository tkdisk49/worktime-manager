<?php

namespace App\Http\Middleware;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAttendanceStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            $attendance = Attendance::where('user_id', $user->id)
                ->whereDate('work_date', Carbon::today())
                ->first();

            if (!$attendance && $user->work_status !== User::WORK_OFF_DUTY) {
                $user->update(['work_status' => User::WORK_OFF_DUTY]);
            }
        }

        return $next($request);
    }
}
