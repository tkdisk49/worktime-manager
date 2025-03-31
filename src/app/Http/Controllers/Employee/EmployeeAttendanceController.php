<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmployeeAttendanceController extends Controller
{
    public function create()
    {
        $now = Carbon::now();
        $date = $now->isoFormat('YYYY年M月D日(ddd)');
        $time = $now->format('H:i');

        $user = Auth::user();

        $statusLabel = User::getWorkStatusLabel($user->work_status);

        return view('employee.attendances.create', compact('date', 'time', 'user', 'statusLabel'));
    }

    public function store()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $now = Carbon::now();
        $today = $now->toDateString();
        $currentTime = $now->format('H:i:s');

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->first();

        if ($attendance || !$user->isOffDuty()) {
            return redirect()->back()->with('error', '本日は出勤済みです');
        }

        DB::transaction(function () use ($user, $today, $currentTime) {
            Attendance::create([
                'user_id' => $user->id,
                'work_date' => $today,
                'clock_in' => $currentTime,
            ]);

            $user->update(['work_status' => User::WORK_WORKING]);
        });

        return redirect()->back();
    }

    public function clockOut()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $now = Carbon::now();
        $today = $now->toDateString();
        $currentTime = $now->format('H:i:s');

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->first();

        if (!$attendance) {
            return redirect()->back()->with('error', '本日の勤怠記録がありません');
        }

        if (!$user->isWorking()) {
            return redirect()->back()->with('error', '勤務中ではないため退勤できません');
        }

        $clockInTime = Carbon::parse($attendance->clock_in);
        $clockOutTime = Carbon::parse($currentTime);
        $totalWorkMinutes = $clockInTime->diffInMinutes($clockOutTime);

        DB::transaction(function () use ($user, $currentTime, $attendance, $totalWorkMinutes) {
            $attendance->update([
                'clock_out' => $currentTime,
                'total_work_time' => $totalWorkMinutes,
            ]);

            $user->update(['work_status' => User::WORK_LEFT_WORK]);
        });

        return redirect()->back();
    }
}
