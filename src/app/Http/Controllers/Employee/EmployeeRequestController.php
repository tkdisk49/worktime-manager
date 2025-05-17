<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Admin\ApprovalController;
use App\Http\Controllers\Controller;
use App\Models\AttendanceModification;
use Illuminate\Support\Facades\Auth;

class EmployeeRequestController extends Controller
{
    public function index()
    {
        if (Auth::guard('admin')->check()) {
            $adminController = app(ApprovalController::class);
            return $adminController->index();
        }

        $user = Auth::user();

        $pendingRequests = AttendanceModification::with('attendance', 'user')
            ->join('attendances', 'attendance_modifications.attendance_id', '=', 'attendances.id')
            ->where('attendance_modifications.user_id', $user->id)
            ->where('attendance_modifications.approval_status', AttendanceModification::APPROVAL_PENDING)
            ->orderBy('attendances.work_date', 'asc')
            ->select('attendance_modifications.*')
            ->get();

        $approvedRequests = AttendanceModification::with('attendance', 'user')
            ->join('attendances', 'attendance_modifications.attendance_id', '=', 'attendances.id')
            ->where('attendance_modifications.user_id', $user->id)
            ->where('attendance_modifications.approval_status', AttendanceModification::APPROVAL_APPROVED)
            ->orderBy('attendances.work_date', 'asc')
            ->select('attendance_modifications.*')
            ->get();

        $layout = 'layouts.app';

        return view('employee.requests.index', compact('pendingRequests', 'approvedRequests', 'layout'));
    }
}
