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

    public function edit()
    {
        return view('employee.attendances.show');
    }
}
