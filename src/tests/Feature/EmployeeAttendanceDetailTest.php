<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Attendance $attendance;
    protected BreakTime $breakTime;
    protected Carbon $now;

    protected function setUp(): void
    {
        parent::setUp();

        $this->now = Carbon::create(2025, 5, 10, 8, 0, 0, 'Asia/Tokyo');
        Carbon::setTestNow($this->now);

        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'web');

        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => $this->now->toDateString(),
        ]);

        $this->breakTime = BreakTime::factory()->create([
            'attendance_id' => $this->attendance->id,
        ]);
    }

    public function testAttendanceDetailDisplaysLoggedInUserName()
    {
        $response = $this->get(route('attendance.modification.show', [
            'id' => $this->attendance->id,
        ]));

        $response->assertStatus(200)
            ->assertSeeText($this->user->name);
    }

    public function testAttendanceDetailDisplaysSelectedWorkDate()
    {
        $response = $this->get(route('attendance.modification.show', [
            'id' => $this->attendance->id,
        ]));

        $workDate = Carbon::parse($this->attendance->work_date);
        $formattedYear = $workDate->isoFormat('YYYY年');
        $formattedMonthDay = $workDate->isoFormat('M月D日');

        $response->assertStatus(200)
            ->assertSeeText($formattedYear)
            ->assertSeeText($formattedMonthDay);
    }

    public function testAttendanceDetailDisplaysCorrectClockInAndOutTime()
    {
        $response = $this->get(route('attendance.modification.show', [
            'id' => $this->attendance->id,
        ]));

        $response->assertStatus(200)
            ->assertSee('value="' . $this->attendance->formatted_clock_in . '"', false)
            ->assertSee('value="' . $this->attendance->formatted_clock_out . '"', false);
    }

    public function testAttendanceDetailDisplaysCorrectBreakStartAndEndTime()
    {
        $response = $this->get(route('attendance.modification.show', [
            'id' => $this->attendance->id,
        ]));

        $response->assertStatus(200)
            ->assertSee('value="' . $this->breakTime->formatted_break_start . '"', false)
            ->assertSee('value="' . $this->breakTime->formatted_break_end . '"', false);
    }
}