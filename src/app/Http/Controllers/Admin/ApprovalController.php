<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceModification;
use Illuminate\Http\Request;

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
        return view('admin.approvals.show');
    }
}
