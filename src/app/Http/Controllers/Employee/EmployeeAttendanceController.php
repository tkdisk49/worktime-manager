<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmployeeAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now()->startOfMonth();
        $monthParam = $request->input('month');

        // 意図しない月に変換されないよう日付を月初で固定
        $currentDate = $monthParam
            ? Carbon::parse($monthParam . '-01')
            : $now;

        $startOfMonth = $currentDate->copy()->startOfMonth();
        $endOfMonth = $currentDate->copy()->endOfMonth();

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
            ->orderBy('work_date')
            ->get();

        return view('employee.attendances.index', compact('attendances', 'currentDate'));
    }

    public function create()
    {
        $now = Carbon::now();
        $date = $now->isoFormat('YYYY年M月D日(ddd)');
        $time = $now->format('H:i');

        $user = Auth::user();

        $statusLabel = User::getWorkStatusLabel($user->work_status);

        return view('employee.attendances.create', compact('date', 'time', 'user', 'statusLabel'));
    }

    public function recordWorkStart()
    {
        $data = $this->getTodayAttendanceData();
        $user = $data['user'];
        $today = $data['today'];
        $currentTime = $data['currentTime'];
        $attendance = $data['attendance'];

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

    public function recordWorkEnd()
    {
        $data = $this->getTodayAttendanceData();
        $user = $data['user'];
        $currentTime = $data['currentTime'];
        $attendance = $data['attendance'];

        if (!$attendance) {
            return redirect()->back()->with('error', '本日の勤怠記録がありません');
        }

        if (!$user->isWorking()) {
            return redirect()->back()->with('error', '勤務中ではないため退勤できません');
        }

        $clockInTime = Carbon::parse($attendance->clock_in);
        $clockOutTime = Carbon::parse($currentTime);
        $totalWorkMinutes = $clockInTime->diffInMinutes($clockOutTime);

        $breakMinutes = $attendance->total_break_time ?? 0;
        $netWorkMinutes = max(0, $totalWorkMinutes - $breakMinutes);

        DB::transaction(function () use ($user, $currentTime, $attendance, $netWorkMinutes) {
            $attendance->update([
                'clock_out' => $currentTime,
                'total_work_time' => $netWorkMinutes,
            ]);

            $user->update(['work_status' => User::WORK_LEFT_WORK]);
        });

        return redirect()->back();
    }

    public function recordBreakStart()
    {
        $data = $this->getTodayAttendanceData();
        $user = $data['user'];
        $attendance = $data['attendance'];
        $currentTime = $data['currentTime'];

        if (!$attendance) {
            return redirect()->back()->with('error', '本日の勤怠記録がありません');
        }

        if (!$user->isWorking()) {
            return redirect()->back()->with('error', '勤務中ではないため休憩を記録できません');
        }

        DB::transaction(function () use ($user, $attendance, $currentTime) {
            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_start' => $currentTime,
            ]);

            $user->update(['work_status' => User::WORK_ON_BREAK]);
        });

        return redirect()->back();
    }

    public function recordBreakEnd()
    {
        $data = $this->getTodayAttendanceData();
        $user = $data['user'];
        $attendance = $data['attendance'];
        $currentTime = $data['currentTime'];

        if (!$attendance) {
            return redirect()->back()->with('error', '本日の勤怠記録がありません');
        }

        if (!$user->isOnBreak()) {
            return redirect()->back()->with('error', '休憩中ではないため記録できません');
        }

        $latestBreak = $attendance->breakTimes()
            ->whereNull('break_end')
            ->latest('break_start')
            ->first();

        if (!$latestBreak) {
            return redirect()->back()->with('error', '休憩開始記録が見つかりません');
        }

        $breakStart = Carbon::parse($latestBreak->break_start);
        $breakEnd = Carbon::parse($currentTime);
        $breakMinutes = $breakStart->diffInMinutes($breakEnd);

        if (is_null($attendance->total_break_time)) {
            $attendance->total_break_time = 0;
            $attendance->save();
        }

        DB::transaction(function () use ($user, $attendance, $latestBreak, $breakEnd, $breakMinutes) {
            $latestBreak->update([
                'break_end' => $breakEnd->format('H:i:s'),
            ]);

            $attendance->increment('total_break_time', $breakMinutes);

            $user->update(['work_status' => User::WORK_WORKING]);
        });

        return redirect()->back();
    }

    private function getTodayAttendanceData(): array
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $now = Carbon::now();
        $today = $now->toDateString();
        $currentTime = $now->format('H:i:s');

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->first();

        return compact('user', 'now', 'today', 'currentTime', 'attendance');
    }
}
