<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function index()
    {
        $staffs = User::all();
        return view('admin.attendances.staff_list', compact('staffs'));
    }

    public function showMonthlyAttendance(Request $request, $id)
    {
        $staff = User::findOrFail($id);
        $now = Carbon::now()->startOfMonth();
        $monthParam = $request->input('month');

        $currentDate = $monthParam
            ? Carbon::parse($monthParam . '-01')
            : $now;

        $startOfMonth = $currentDate->copy()->startOfMonth();
        $endOfMonth = $currentDate->copy()->endOfMonth();

        $attendances = Attendance::where('user_id', $id)
            ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
            ->orderBy('work_date')
            ->get();

        return view('admin.attendances.staff_attendance', compact('staff', 'currentDate', 'attendances'));
    }

    public function exportCsv()
    {
        //
    }
}
