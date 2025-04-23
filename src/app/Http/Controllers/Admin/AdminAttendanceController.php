<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceModificationRequest;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();
        $dateParam = $request->input('date');

        $currentDate = $dateParam
            ? Carbon::parse($dateParam)
            : $today;

        $attendances = Attendance::with('user')
            ->whereDate('work_date', $currentDate)
            ->orderBy('user_id')
            ->get();

        return view('admin.attendances.index', compact('currentDate', 'attendances'));
    }

    public function edit($id)
    {
        $attendance = Attendance::with(['user', 'breakTimes'])
            ->findOrFail($id);

        $hasPendingRequest = false;

        $workDate = Carbon::parse($attendance->work_date);
        $formattedYear = $workDate->isoFormat('YYYY年');
        $formattedMonthDay = $workDate->isoFormat('M月D日');

        $formAction = route('admin.attendance.update', ['id' => $attendance->id]);
        $formMethod = 'patch';

        $layout = 'layouts.admin_app';

        return view('employee.attendances.show', compact(
            'attendance',
            'hasPendingRequest',
            'formattedYear',
            'formattedMonthDay',
            'formAction',
            'formMethod',
            'layout',
        ));
    }

    public function update(AttendanceModificationRequest $request, $id)
    {
        $attendance = Attendance::with('breakTimes')->findOrFail($id);

        $newClockIn = $request->input('new_clock_in');
        $newClockOut = $request->input('new_clock_out');
        $existingBreaks = $request->input('existing_breaks', []);
        $newBreakStart = $request->input('new_break_start');
        $newBreakEnd = $request->input('new_break_end');
        $newRemarks = $request->input('new_remarks');

        $start = Carbon::parse($newClockIn);
        $end = Carbon::parse($newClockOut);
        $newTotalWorkMinutes = $end->diffInMinutes($start);


        DB::transaction(function () use (
            $attendance,
            $newClockIn,
            $newClockOut,
            $newTotalWorkMinutes,
            $existingBreaks,
            $newBreakStart,
            $newBreakEnd,
            $newRemarks,
        ) {
            $totalBreakMinutes = 0;

            foreach ($existingBreaks as $break) {
                $breakTime = $attendance->breakTimes()->find($break['id']);
                if ($breakTime) {
                    $breakTime->update([
                        'break_start' => $break['start'],
                        'break_end' => $break['end'],
                    ]);

                    $totalBreakMinutes += Carbon::parse($break['end'])
                        ->diffInMinutes(Carbon::parse($break['start']));
                }
            }

            if ($newBreakStart && $newBreakEnd) {
                $attendance->breakTimes()->create([
                    'break_start' => $newBreakStart,
                    'break_end' => $newBreakEnd,
                ]);

                $totalBreakMinutes += Carbon::parse($newBreakEnd)
                    ->diffInMinutes(Carbon::parse($newBreakStart));
            }

            $netWorkMinutes = max(0, $newTotalWorkMinutes - $totalBreakMinutes);

            $attendance->update([
                'clock_in' => $newClockIn,
                'clock_out' => $newClockOut,
                'total_work_time' => $netWorkMinutes,
                'total_break_time' => $totalBreakMinutes,
                'remarks' => $newRemarks,
            ]);
        });

        return redirect()->route('attendance.modification.show', ['id' => $attendance->id])->with('success', '勤怠情報を修正しました');
    }
}
