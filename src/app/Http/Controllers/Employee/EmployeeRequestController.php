<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\AttendanceModification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeRequestController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $pendingRequests = AttendanceModification::with('attendance', 'user')
            ->where('user_id', $user->id)
            ->where('approval_status', AttendanceModification::APPROVAL_PENDING)
            ->orderBy('created_at', 'desc')
            ->get();

        $approvedRequests = AttendanceModification::with('attendance', 'user')
            ->where('user_id', $user->id)
            ->where('approval_status', AttendanceModification::APPROVAL_APPROVED)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('employee.requests.index', compact('pendingRequests', 'approvedRequests'));
    }
}
