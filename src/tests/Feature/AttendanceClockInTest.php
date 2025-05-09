<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceClockInTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Carbon $now;

    protected function setUp(): void
    {
        parent::setUp();

        $this->now = Carbon::create(2025, 5, 10, 8, 0, 0, 'Asia/Tokyo');
        Carbon::setTestNow($this->now);

        $this->user = User::factory()->create();
    }

    public function testClockInButtonWorksCorrectly()
    {
        $this->user->update(['work_status' => User::WORK_OFF_DUTY]);

        $response = $this->actingAs($this->user, 'web')
            ->get(route('attendance.create'));

        $response->assertStatus(200)
            ->assertSee('<button type="submit" class="attendance-create__button">出勤</button>', false);

        $response = $this->actingAs($this->user, 'web')
            ->followingRedirects()
            ->post(route('attendance.work_start'));

        $response->assertStatus(200)
            ->assertSeeText('出勤中');
    }

    public function testClockInCanOnlyBePerformedOncePerDay()
    {
        $this->user->update(['work_status' => User::WORK_LEFT_WORK]);

        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => $this->now->toDateString(),
        ]);

        $response = $this->actingAs($this->user, 'web')
            ->get(route('attendance.create'));

        $response->assertStatus(200)
            ->assertDontSee('<button type="submit" class="attendance-create__button">出勤</button>', false);
    }

    public function testClockInTimeIsVisibleInAttendanceList()
    {
        $this->user->update(['work_status' => User::WORK_OFF_DUTY]);

        $this->actingAs($this->user, 'web')
            ->post(route('attendance.work_start'));

        $response = $this->actingAs($this->user, 'web')
            ->get(route('attendance.index', [
                'month' => $this->now->format('Y-m')
            ]));

        $response->assertStatus(200)
            ->assertSeeText($this->now->isoFormat('MM/DD(ddd)'))
            ->assertSeeText($this->now->format('H:i'));
    }
}
