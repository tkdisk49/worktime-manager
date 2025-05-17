<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\AttendanceModification;
use App\Models\BreakTime;
use App\Models\BreakTimeModification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;
    protected Carbon $now;

    protected function setUp(): void
    {
        parent::setUp();

        $this->now = Carbon::create(2025, 5, 10, 18, 0, 0, 'Asia/Tokyo');
        Carbon::setTestNow($this->now);

        $users = User::factory()->count(5)->create();

        foreach ($users as $user) {
            $attendance = Attendance::factory()->create([
                'user_id' => $user->id,
                'work_date' => $this->now->toDateString(),
            ]);

            $breakTime = BreakTime::factory()->create([
                'attendance_id' => $attendance->id,
            ]);

            $attendanceModification = AttendanceModification::factory()->create([
                'attendance_id' => $attendance->id,
                'user_id' => $user->id,
            ]);

            BreakTimeModification::factory()->create([
                'break_time_id' => $breakTime->id,
                'attendance_modification_id' => $attendanceModification->id,
                'user_id' => $user->id,
            ]);
        }

        $this->admin = Admin::factory()->create();
        $this->actingAs($this->admin, 'admin');
    }

    public function testShowPendingRequests()
    {
        $pendingRequests = AttendanceModification::where('approval_status', AttendanceModification::APPROVAL_PENDING)->get();

        $response = $this->get(route('employee.requests.index', [
            'status' => 'pending',
        ]));

        $response->assertStatus(200);

        foreach ($pendingRequests as $request) {
            $response->assertSee('<td>承認待ち</td>', false);
            $response->assertSeeText($request->user->name);
            $response->assertSeeText($request->formatted_work_date);
            $response->assertSeeText($request->new_remarks);
            $response->assertSeeText($request->formatted_created_at);
        }
    }

    public function testShowApprovedRequests()
    {
        $attendanceModifications = AttendanceModification::all();

        foreach ($attendanceModifications as $attendanceModification) {
            $this->patch(route('admin.approval.update', [
                'attendance_correct_request' => $attendanceModification->id,
            ]));
        }

        $approvedRequests = AttendanceModification::where('approval_status', AttendanceModification::APPROVAL_APPROVED)->get();

        $response = $this->get(route('employee.requests.index', [
            'status' => 'approved',
        ]));

        $response->assertStatus(200);

        foreach ($approvedRequests as $request) {
            $response->assertSee('<td>承認済み</td>', false);
            $response->assertSeeText($request->user->name);
            $response->assertSeeText($request->formatted_work_date);
            $response->assertSeeText($request->new_remarks);
            $response->assertSeeText($request->formatted_created_at);
        }
    }

    public function testShowRequestDetails()
    {
        $attendanceModification = AttendanceModification::first();
        $attendance = $attendanceModification->attendance;

        $formattedYear = Carbon::parse($attendance->work_date)->isoFormat('YYYY年');
        $formattedMonthDay = Carbon::parse($attendance->work_date)->isoFormat('M月D日');

        $response = $this->get(route('admin.approval.show', [
            'attendance_correct_request' => $attendanceModification->id,
        ]));

        $response->assertStatus(200);
        $response->assertSeeText($attendance->user->name);
        $response->assertSeeText($formattedYear);
        $response->assertSeeText($formattedMonthDay);
        $response->assertSeeText($attendanceModification->formatted_new_clock_in);
        $response->assertSeeText($attendanceModification->formatted_new_clock_out);

        foreach ($attendanceModification->breakTimeModifications as $breakTimeModification) {
            $response->assertSeeText($breakTimeModification->formatted_new_break_start);
            $response->assertSeeText($breakTimeModification->formatted_new_break_end);
        }

        $response->assertSeeText($attendanceModification->new_remarks);
    }

    public function testApproveRequest()
    {
        $attendanceModification = AttendanceModification::first();
        $attendance = $attendanceModification->attendance;

        $this->patch(route('admin.approval.update', [
            'attendance_correct_request' => $attendanceModification->id,
        ]));

        $attendanceModification->refresh();
        $attendance->refresh();

        $breakTimeModifications = $attendanceModification->breakTimeModifications;

        $totalBreakMinutes = 0;
        foreach ($breakTimeModifications as $breakTimeModification) {
            $totalBreakMinutes += Carbon::parse($breakTimeModification->new_break_end)
                ->diffInMinutes(Carbon::parse($breakTimeModification->new_break_start));
        }

        $netWorkMinutes = $attendanceModification->new_total_work_time - $totalBreakMinutes;

        $this->assertEquals(AttendanceModification::APPROVAL_APPROVED, $attendanceModification->approval_status);
        $this->assertEquals($this->admin->id, $attendanceModification->approved_by);

        foreach ($breakTimeModifications as $breakTimeModification) {
            $this->assertEquals(BreakTimeModification::APPROVAL_APPROVED, $breakTimeModification->approval_status);
            $this->assertEquals($this->admin->id, $breakTimeModification->approved_by);
        }

        $this->assertEquals($attendanceModification->new_clock_in, $attendance->clock_in);
        $this->assertEquals($attendanceModification->new_clock_out, $attendance->clock_out);
        $this->assertEquals($netWorkMinutes, $attendance->total_work_time);
        $this->assertEquals($totalBreakMinutes, $attendance->total_break_time);
        $this->assertEquals($attendanceModification->new_remarks, $attendance->remarks);
    }
}
