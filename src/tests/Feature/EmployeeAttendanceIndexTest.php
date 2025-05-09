<?php

namespace Tests\Feature;

use App\Models\Attendance;
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

        $this->now = Carbon::create(2025, 5, 10, 8, 0, 0, 'Asia/Tokyo');
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
        $attendanceYesterday = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => $this->yesterday,
        ]);

        $attendanceToday = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => $this->today,
        ]);

        $response = $this->get(route('attendance.index'));

        $response->assertStatus(200)
            ->assertSeeText($attendanceYesterday->formatted_work_date)
            ->assertSeeText($attendanceToday->formatted_work_date);
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

        $response->assertStatus(200)
            ->assertSeeText($previousMonthLabel)
            ->assertSeeText($attendancePreviousMonth->formatted_work_date);
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

        $response->assertStatus(200)
            ->assertSeeText($nextMonthLabel)
            ->assertSeeText($attendanceNextMonth->formatted_work_date);
    }

    public function testNavigatesToDetailPageWhenDetailLinkClicked()
    {
        $attendanceYesterday = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => $this->yesterday,
        ]);

        $attendanceToday = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => $this->today,
        ]);

        $yesterdayFormattedYear = Carbon::parse($this->yesterday)->isoFormat('YYYY年');
        $yesterdayFormattedMonthDay = Carbon::parse($this->yesterday)->isoFormat('M月D日');

        $response = $this->get(route('attendance.index'));

        $response->assertStatus(200)
            ->assertSee('href="' . route('attendance.modification.show', ['id' => $attendanceYesterday->id]) . '"', false);

        $response = $this->get(route('attendance.modification.show', [
            'id' => $attendanceYesterday->id,
        ]));

        $response->assertStatus(200)
            ->assertSeeText($yesterdayFormattedYear)
            ->assertSeeText($yesterdayFormattedMonthDay);
    }
}
