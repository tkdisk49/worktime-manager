<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Carbon $now;
    protected string $today;

    protected function setUp(): void
    {
        parent::setUp();

        $this->now = Carbon::create(2025, 5, 10, 18, 0, 0, 'Asia/Tokyo');
        Carbon::setTestNow($this->now);

        $this->today = $this->now->toDateString();

        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'web');
    }

    public function testOffDutyStatusLabelIsDisplayed()
    {
        $this->user->update(['work_status' => User::WORK_OFF_DUTY]);

        $response = $this->get(route('attendance.create'));

        $response->assertStatus(200);
        $response->assertSeeText('勤務外');
    }

    public function testWorkingStatusLabelIsDisplayed()
    {
        $this->user->update(['work_status' => User::WORK_WORKING]);

        // 当日の勤怠データがない場合、ステータスが勤務外にリセットされるため作成
        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => $this->today,
            'clock_in' => '09:00:00',
            'clock_out' => null,
        ]);

        $response = $this->get(route('attendance.create'));

        $response->assertStatus(200);
        $response->assertSeeText('出勤中');
    }

    public function testOnBreakStatusLabelIsDisplayed()
    {
        $this->user->update(['work_status' => User::WORK_ON_BREAK]);

        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => $this->today,
            'clock_in' => '09:00:00',
            'clock_out' => null,
        ]);

        $response = $this->get(route('attendance.create'));

        $response->assertStatus(200);
        $response->assertSeeText('休憩中');
    }

    public function testLeftWorkStatusLabelIsDisplayed()
    {
        $this->user->update(['work_status' => User::WORK_LEFT_WORK]);

        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => $this->today,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->get(route('attendance.create'));

        $response->assertStatus(200);
        $response->assertSeeText('退勤済');
    }
}
