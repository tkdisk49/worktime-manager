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
        $data = $this->getMonthlyAttendances($id, $request->input('month'));

        $staff = $data['staff'];
        $currentDate = $data['currentDate'];
        $attendances = $data['attendances'];

        return view('admin.attendances.staff_attendance', compact('staff', 'currentDate', 'attendances'));
    }

    public function exportCsv(Request $request, $id)
    {
        $data = $this->getMonthlyAttendances($id, $request->input('month'));

        $staff = $data['staff'];
        $currentDate = $data['currentDate'];
        $attendances = $data['attendances'];

        $fileName = sprintf('%s_%s_attendance.csv', $staff->name, $currentDate->format('Y-m'));

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function () use ($attendances) {
            $stream = fopen('php://output', 'w');

            fputs($stream, "\xEF\xBB\xBF"); // Excelでの文字化け防止
            fputcsv($stream, ['日付', '出勤', '退勤', '休憩', '合計']);

            foreach ($attendances as $attendance) {
                fputcsv($stream, [
                    $attendance->formatted_work_date,
                    $attendance->formatted_clock_in ?? '',
                    $attendance->formatted_clock_out ?? '',
                    $attendance->formatted_total_break_time,
                    $attendance->formatted_total_work_time,
                ]);
            }

            fclose($stream);
        };

        return response()->streamDownload($callback, $fileName, $headers);
    }

    private function getMonthlyAttendances($userId, $monthParam)
    {
        $staff = User::findOrFail($userId);

        $currentDate = $monthParam
            ? Carbon::parse($monthParam . '-01')
            : Carbon::now()->startOfMonth();

        $startOfMonth = $currentDate->copy()->startOfMonth();
        $endOfMonth = $currentDate->copy()->endOfMonth();

        $attendances = Attendance::where('user_id', $userId)
            ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
            ->orderBy('work_date')
            ->get();

        return compact('staff', 'currentDate', 'attendances');
    }
}
