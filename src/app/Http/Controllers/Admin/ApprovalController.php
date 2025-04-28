<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceModification;
use App\Models\BreakTimeModification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApprovalController extends Controller
{
    public function index()
    {
        $pendingRequests = AttendanceModification::with('attendance', 'user')
            ->join('attendances', 'attendance_modifications.attendance_id', '=', 'attendances.id')
            ->where('attendance_modifications.approval_status', AttendanceModification::APPROVAL_PENDING)
            ->orderBy('attendances.work_date', 'asc')
            ->select('attendance_modifications.*')
            ->get();

        $approvedRequests = AttendanceModification::with('attendance', 'user')
            ->join('attendances', 'attendance_modifications.attendance_id', '=', 'attendances.id')
            ->where('attendance_modifications.approval_status', AttendanceModification::APPROVAL_APPROVED)
            ->orderBy('attendances.work_date', 'asc')
            ->select('attendance_modifications.*')
            ->get();

        $layout = 'layouts.admin_app';

        return view('employee.requests.index', compact('pendingRequests', 'approvedRequests', 'layout'));
    }

    public function show($attendanceCorrectRequest)
    {
        $attendance = Attendance::with([
            'user',
            'modification',
            'breakTimeModifications'
        ])->findOrFail($attendanceCorrectRequest);

        $hasPendingRequest = AttendanceModification::where('attendance_id', $attendance->id)
            ->where('approval_status', AttendanceModification::APPROVAL_PENDING)
            ->exists();

        $workDate = Carbon::parse($attendance->work_date);
        $formattedYear = $workDate->isoFormat('YYYY年');
        $formattedMonthDay = $workDate->isoFormat('M月D日');

        return view('admin.approvals.show', compact('attendance', 'hasPendingRequest', 'formattedYear', 'formattedMonthDay'));
    }

    public function update($attendanceCorrectRequest)
    {
        $attendance = Attendance::findOrFail($attendanceCorrectRequest);

        $attendanceModification = $attendance->modification()
            ->where('approval_status', AttendanceModification::APPROVAL_PENDING)
            ->firstOrFail();

        $breakTimeModifications = $attendance->breakTimeModifications()
            ->where('approval_status', BreakTimeModification::APPROVAL_PENDING)
            ->get();

        DB::transaction(function () use ($attendance, $attendanceModification, $breakTimeModifications) {
            $newClockIn = $attendanceModification->new_clock_in;
            $newClockOut = $attendanceModification->new_clock_out;
            $newRemarks = $attendanceModification->new_remarks;
            $totalBreakMinutes = 0;

            foreach ($breakTimeModifications as $breakTimeModification) {
                if ($breakTimeModification->break_time_id) {
                    $attendance->breakTimes()
                        ->findOrFail($breakTimeModification->break_time_id)
                        ->update([
                            'break_start' => $breakTimeModification->new_break_start,
                            'break_end' => $breakTimeModification->new_break_end,
                        ]);

                    $breakTimeModification->update([
                        'approval_status' => BreakTimeModification::APPROVAL_APPROVED,
                        'approved_by' => Auth::guard('admin')->id(),
                    ]);
                } else {
                    $newBreakTime = $attendance->breakTimes()->create([
                        'break_start' => $breakTimeModification->new_break_start,
                        'break_end' => $breakTimeModification->new_break_end,
                    ]);

                    $breakTimeModification->update([
                        'break_time_id' => $newBreakTime->id,
                        'approval_status' => BreakTimeModification::APPROVAL_APPROVED,
                        'approved_by' => Auth::guard('admin')->id(),
                    ]);
                }

                $totalBreakMinutes += Carbon::parse($breakTimeModification->new_break_end)
                    ->diffInMinutes(Carbon::parse($breakTimeModification->new_break_start));
            }

            $netWorkMinutes = max(0, $attendanceModification->new_total_work_time - $totalBreakMinutes);

            $attendance->update([
                'clock_in' => $newClockIn,
                'clock_out' => $newClockOut,
                'total_work_time' => $netWorkMinutes,
                'total_break_time' => $totalBreakMinutes,
                'remarks' => $newRemarks,
            ]);

            $attendanceModification->update([
                'approval_status' => AttendanceModification::APPROVAL_APPROVED,
                'approved_by' => Auth::guard('admin')->id(),
            ]);
        });

        return redirect()
            ->route('admin.approval.show', ['attendance_correct_request' => $attendance->id])
            ->with('success', '申請を承認しました');
    }
}
