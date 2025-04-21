<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
            ->where('id', $id)
            ->firstOrFail();

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

    public function update()
    {
        //
    }
}
