<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceModificationRequest;
use App\Models\Attendance;
use App\Models\AttendanceModification;
use App\Models\BreakTimeModification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmployeeAttendanceModificationController extends Controller
{
    public function show($id)
    {
        if (Auth::guard('admin')->check()) {
            $adminController = app(AdminAttendanceController::class);
            return $adminController->edit($id);
        }

        $user = Auth::user();

        $attendance = Attendance::with([
            'user',
            'breakTimes',
        ])->where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        $hasPendingRequest = AttendanceModification::where('attendance_id', $attendance->id)
            ->where('approval_status', AttendanceModification::APPROVAL_PENDING)
            ->exists();

        $pendingRequest = null;

        if ($hasPendingRequest) {
            $pendingRequest = AttendanceModification::with('breakTimeModifications')
                ->where('attendance_id', $attendance->id)
                ->where('approval_status', AttendanceModification::APPROVAL_PENDING)
                ->first();
        }

        $workDate = Carbon::parse($attendance->work_date);
        $formattedYear = $workDate->isoFormat('YYYY年');
        $formattedMonthDay = $workDate->isoFormat('M月D日');

        $formAction = route('attendance.modification.store', ['id' => $attendance->id]);
        $formMethod = 'post';

        $layout = 'layouts.app';

        return view('employee.attendances.show', compact(
            'attendance',
            'hasPendingRequest',
            'pendingRequest',
            'formattedYear',
            'formattedMonthDay',
            'formAction',
            'formMethod',
            'layout',
        ));
    }

    public function store(AttendanceModificationRequest $request, $id)
    {
        $user = Auth::user();

        $attendance = Attendance::with('breakTimes')
            ->where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        $newClockIn = $request->input('new_clock_in');
        $newClockOut = $request->input('new_clock_out');

        $start = Carbon::parse($newClockIn);
        $end = Carbon::parse($newClockOut);
        $newTotalWorkMinutes = $end->diffInMinutes($start);

        DB::transaction(function () use (
            $request,
            $user,
            $attendance,
            $newClockIn,
            $newClockOut,
            $newTotalWorkMinutes,
        ) {
            $attendanceModification = AttendanceModification::create([
                'attendance_id' => $attendance->id,
                'user_id' => $user->id,
                'new_clock_in' => $newClockIn,
                'new_clock_out' => $newClockOut,
                'new_total_work_time' => $newTotalWorkMinutes,
                'new_remarks' => $request->input('new_remarks'),
            ]);

            foreach ($request->input('existing_breaks', []) as $break) {
                BreakTimeModification::create([
                    'attendance_modification_id' => $attendanceModification->id,
                    'break_time_id' => $break['id'],
                    'user_id' => $user->id,
                    'new_break_start' => $break['start'],
                    'new_break_end' => $break['end'],
                ]);
            }

            if ($request->filled('new_break_start') && $request->filled('new_break_end')) {
                BreakTimeModification::create([
                    'attendance_modification_id' => $attendanceModification->id,
                    'user_id' => $user->id,
                    'new_break_start' => $request->input('new_break_start'),
                    'new_break_end' => $request->input('new_break_end'),
                ]);
            }
        });

        return redirect()->route('attendance.modification.show', ['id' => $attendance->id])->with('success', '申請が完了しました');
    }
}
