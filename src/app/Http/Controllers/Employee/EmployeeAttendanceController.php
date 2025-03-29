<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeAttendanceController extends Controller
{
    public function create()
    {
        $now = Carbon::now();
        $date = $now->isoFormat('YYYY年M月D日(ddd)');
        $time = $now->format('H:i');

        $user = Auth::user();

        $statusLabel = User::getWorkStatusLabel($user->work_status);

        return view('employee.attendance.create', compact('date', 'time', 'user', 'statusLabel'));
    }
}
