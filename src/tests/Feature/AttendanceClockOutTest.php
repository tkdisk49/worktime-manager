<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceClockOutTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Carbon $clockInTime;
    protected Carbon $clockOutTime;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clockInTime = Carbon::create(2025, 5, 10, 8, 0, 0, 'Asia/Tokyo');
        $this->clockOutTime = Carbon::create(2025, 5, 10, 18, 0, 0, 'Asia/Tokyo');

        Carbon::setTestNow($this->clockInTime);

        $this->user = User::factory()->create();
    }

    public function testClockOutButtonWorksCorrectly()
    {
        $this->user->update(['work_status' => User::WORK_WORKING]);
        $this->actingAs($this->user, 'web');

        Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => $this->clockInTime->toDateString(),
            'clock_in' => $this->clockInTime->format('H:i:s'),
            'clock_out' => null,
            'total_work_time' => 0,
            'total_break_time' => 0,
        ]);

        $response = $this->get(route('attendance.create'));

        $response->assertStatus(200)
            ->assertSee('<button type="submit" class="attendance-create__button">退勤</button>', false);

        Carbon::setTestNow($this->clockOutTime);

        $response = $this->followingRedirects()
            ->patch(route('attendance.work_end'));

        $response->assertStatus(200)
            ->assertSeeText('退勤済');
    }

    public function testClockOutTimeIsVisibleInAttendanceList()
    {
        $this->user->update(['work_status' => User::WORK_OFF_DUTY]);
        $this->actingAs($this->user, 'web');

        $this->get(route('attendance.create'));
        $this->post(route('attendance.work_start'));

        Carbon::setTestNow($this->clockOutTime);
        $this->patch(route('attendance.work_end'));

        $response = $this->get(route('attendance.index'));

        $response->assertStatus(200)
            ->assertSeeText($this->clockOutTime->isoFormat('MM/DD(ddd)'))
            ->assertSeeText($this->clockOutTime->format('H:i'));
    }
}
