<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeAttendanceIndexTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Carbon $now;
    protected string $today;
    protected string $yesterday;
    protected string $previousMonth;
    protected string $nextMonth;

    protected function setUp(): void
    {
        parent::setUp();

        $this->now = Carbon::create(2025, 5, 10, 18, 0, 0, 'Asia/Tokyo');
        Carbon::setTestNow($this->now);

        $this->today = $this->now->toDateString();
        $this->yesterday = $this->now->copy()->subDay()->toDateString();
        $this->previousMonth = $this->now->copy()->subMonth()->format('Y-m');
        $this->nextMonth = $this->now->copy()->addMonth()->format('Y-m');

        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'web');
    }

    public function testShowsAllAttendanceRecords()
    {
        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => $this->yesterday,
        ]);

        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => $this->today,
        ]);

        $attendances = Attendance::where('user_id', $this->user->id)->get();

        $response = $this->get(route('attendance.index'));
        $response->assertStatus(200);

        foreach ($attendances as $attendance) {
            $response->assertSeeText($attendance->formatted_work_date);
            $response->assertSeeText($attendance->formatted_clock_in);
            $response->assertSeeText($attendance->formatted_clock_out);
            $response->assertSeeText($attendance->formatted_total_break_time);
            $response->assertSeeText($attendance->formatted_total_work_time);
        }
    }

    public function testDisplaysCurrentMonthOnAttendanceList()
    {
        $response = $this->get(route('attendance.index'));

        $response->assertStatus(200)
            ->assertSeeText($this->now->format('Y/m'));
    }

    public function testDisplaysPreviousMonthRecordsWhenPreviousButtonClicked()
    {
        $previousMonthWorkDate = $this->previousMonth . '-10';

        $attendancePreviousMonth = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => $previousMonthWorkDate,
        ]);

        $previousMonthLabel = Carbon::parse($this->previousMonth)->format('Y/m');

        $response = $this->get(route('attendance.index', [
            'month' => $this->previousMonth
        ]));

        $response->assertStatus(200);
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

        $attendanceNextMonth = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => $nextMonthWorkDate,
        ]);

        $nextMonthLabel = Carbon::parse($this->nextMonth)->format('Y/m');

        $response = $this->get(route('attendance.index', [
            'month' => $this->nextMonth
        ]));

        $response->assertStatus(200);
        $response->assertSeeText($nextMonthLabel);
        $response->assertSeeText($attendanceNextMonth->formatted_work_date);
        $response->assertSeeText($attendanceNextMonth->formatted_clock_in);
        $response->assertSeeText($attendanceNextMonth->formatted_clock_out);
        $response->assertSeeText($attendanceNextMonth->formatted_total_break_time);
        $response->assertSeeText($attendanceNextMonth->formatted_total_work_time);
    }

    public function testNavigatesToDetailPageWhenDetailLinkClicked()
    {
        $attendanceYesterday = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => $this->yesterday,
        ]);

        $breakTime = BreakTime::factory()->create([
            'attendance_id' => $attendanceYesterday->id,
        ]);

        $yesterdayFormattedYear = Carbon::parse($this->yesterday)->isoFormat('YYYY年');
        $yesterdayFormattedMonthDay = Carbon::parse($this->yesterday)->isoFormat('M月D日');

        $this->get(route('attendance.index'))
            ->assertStatus(200);

        $response = $this->get(route('attendance.modification.show', [
            'id' => $attendanceYesterday->id,
        ]));

        $response->assertStatus(200);
        $response->assertSeeText($yesterdayFormattedYear);
        $response->assertSeeText($yesterdayFormattedMonthDay);
        $response->assertSee('value="' . $attendanceYesterday->formatted_clock_in . '"', false);
        $response->assertSee('value="' . $attendanceYesterday->formatted_clock_out . '"', false);
        $response->assertSee('value="' . $breakTime->formatted_break_start . '"', false);
        $response->assertSee('value="' . $breakTime->formatted_break_end . '"', false);
    }
}
