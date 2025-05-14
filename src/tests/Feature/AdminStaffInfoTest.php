<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStaffInfoTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Admin $admin;
    protected Carbon $now;
    protected string $today;
    protected string $previousMonth;
    protected string $nextMonth;

    protected function setUp(): void
    {
        parent::setUp();

        $this->now = Carbon::create(2025, 5, 10, 18, 0, 0, 'Asia/Tokyo');
        Carbon::setTestNow($this->now);

        $this->today = $this->now->toDateString();
        $this->previousMonth = $this->now->copy()->subMonth()->format('Y-m');
        $this->nextMonth = $this->now->copy()->addMonth()->format('Y-m');

        $this->user = User::factory()->create();

        $this->admin = Admin::factory()->create();
        $this->actingAs($this->admin, 'admin');
    }

    public function testAdminCanViewAllUsersNameAndEmail()
    {
        $users = User::factory()->count(5)->create();

        $response = $this->get(route('admin.staff.index'));
        $response->assertStatus(200);

        foreach ($users as $user) {
            $response->assertSeeText($user->name);
            $response->assertSeeText($user->email);
        }
    }

    public function testAdminCanViewUserAttendanceInfo()
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => $this->today,
        ]);

        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
        ]);

        $response = $this->get(route('admin.staff.attendance.monthly', [
            'id' => $this->user->id,
        ]));

        $response->assertStatus(200);
        $response->assertSeeText($this->user->name);
        $response->assertSeeText($attendance->formatted_work_date);
        $response->assertSeeText($attendance->formatted_clock_in);
        $response->assertSeeText($attendance->formatted_clock_out);
        $response->assertSeeText($attendance->formatted_total_break_time);
        $response->assertSeeText($attendance->formatted_total_work_time);
    }

    public function testDisplaysPreviousMonthRecordsWhenPreviousButtonClicked()
    {
        $previousMonthWorkDate = $this->previousMonth . '-10';
        $previousMonthLabel = Carbon::parse($this->previousMonth)->format('Y/m');

        $attendancePreviousMonth = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => $previousMonthWorkDate,
        ]);

        BreakTime::factory()->create([
            'attendance_id' => $attendancePreviousMonth->id,
        ]);

        $response = $this->get(route('admin.staff.attendance.monthly', [
            'id' => $this->user->id,
            'month' => $this->previousMonth,
        ]));

        $response->assertStatus(200);
        $response->assertSeeText($this->user->name);
        $response->assertSeeText($previousMonthLabel);
        $response->assertSeeText($attendancePreviousMonth->formatted_work_date);
        $response->assertSeeText($attendancePreviousMonth->formatted_clock_in);
        $response->assertSeeText($attendancePreviousMonth->formatted_clock_out);
        $response->assertSeeText($attendancePreviousMonth->formatted_total_break_time);
        $response->assertSeeText($attendancePreviousMonth->formatted_total_work_time);
    }

    public function testDisplaysNextMonthRecordsWhenNextButtonClicked()
    {
        $nextMonthWorkDate = $this->nextMonth . '-10';
        $nextMonthLabel = Carbon::parse($this->nextMonth)->format('Y/m');

        $attendanceNextMonth = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => $nextMonthWorkDate,
        ]);

        BreakTime::factory()->create([
            'attendance_id' => $attendanceNextMonth->id,
        ]);

        $response = $this->get(route('admin.staff.attendance.monthly', [
            'id' => $this->user->id,
            'month' => $this->nextMonth,
        ]));

        $response->assertStatus(200);
        $response->assertSeeText($this->user->name);
        $response->assertSeeText($nextMonthLabel);
        $response->assertSeeText($attendanceNextMonth->formatted_work_date);
        $response->assertSeeText($attendanceNextMonth->formatted_clock_in);
        $response->assertSeeText($attendanceNextMonth->formatted_clock_out);
        $response->assertSeeText($attendanceNextMonth->formatted_total_break_time);
        $response->assertSeeText($attendanceNextMonth->formatted_total_work_time);
    }

    public function testNavigatesToDetailPageWhenDetailLinkClicked()
    {
        $formattedYear = Carbon::parse($this->today)->isoFormat('YYYY年');
        $formattedMonthDay = Carbon::parse($this->today)->isoFormat('M月D日');

        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => $this->today,
        ]);

        $breakTime = BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
        ]);

        $this->get(route('admin.staff.attendance.monthly', [
            'id' => $this->user->id,
        ]))->assertStatus(200);

        $response = $this->get(route('attendance.modification.show', [
            'id' => $attendance->id,
        ]));

        $response->assertStatus(200);
        $response->assertSeeText($this->user->name);
        $response->assertSeeText($formattedYear);
        $response->assertSeeText($formattedMonthDay);
        $response->assertSee('value="' . $attendance->formatted_clock_in . '"', false);
        $response->assertSee('value="' . $attendance->formatted_clock_out . '"', false);
        $response->assertSee('value="' . $breakTime->formatted_break_start . '"', false);
        $response->assertSee('value="' . $breakTime->formatted_break_end . '"', false);
    }
}
