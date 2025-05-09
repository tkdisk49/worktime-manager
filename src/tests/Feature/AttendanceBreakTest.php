<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AttendanceBreakTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Carbon $now;

    protected function setUp(): void
    {
        parent::setUp();

        $this->now = Carbon::create(2025, 5, 10, 13, 0, 0, 'Asia/Tokyo');
        Carbon::setTestNow($this->now);

        $this->user = User::factory()->create();

        $this->user->update(['work_status' => User::WORK_WORKING]);

        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => $this->now->toDateString(),
            'clock_out' => null,
            'total_work_time' => 0,
            'total_break_time' => 0,
        ]);
    }

    public function testBreakStartButtonWorksCorrectly()
    {
        $response = $this->actingAs($this->user, 'web')
            ->get(route('attendance.create'));

        $response->assertStatus(200)
            ->assertSee('<button type="submit" class="attendance-create__button attendance-create__button--white">休憩入</button>', false);

        $response = $this->actingAs($this->user, 'web')
            ->followingRedirects()
            ->post(route('attendance.break_start'));

        $response->assertStatus(200)
            ->assertSeeText('休憩中');
    }

    public function testBreakStartCanBeCalledMultipleTimesPerDay()
    {
        $this->actingAs($this->user, 'web')
            ->get(route('attendance.create'));

        $this->actingAs($this->user, 'web')
            ->followingRedirects()
            ->post(route('attendance.break_start'));

        $response = $this->actingAs($this->user, 'web')
            ->followingRedirects()
            ->patch(route('attendance.break_end'));

        $response->assertStatus(200)
            ->assertSee('<button type="submit" class="attendance-create__button attendance-create__button--white">休憩入</button>', false);
    }

    public function testBreakEndButtonWorksCorrectly()
    {
        $this->actingAs($this->user, 'web')
            ->get(route('attendance.create'));

        $response = $this->actingAs($this->user, 'web')
            ->followingRedirects()
            ->post(route('attendance.break_start'));

        $response->assertStatus(200)
            ->assertSee('<button type="submit" class="attendance-create__button attendance-create__button--white">休憩戻</button>', false);

        $response = $this->actingAs($this->user, 'web')
            ->followingRedirects()
            ->patch(route('attendance.break_end'));

        $response->assertStatus(200)
            ->assertSeeText('出勤中');
    }

    public function testBreakEndCanBeCalledMultipleTimesPerDay()
    {
        $this->actingAs($this->user, 'web')
            ->get(route('attendance.create'));

        $this->actingAs($this->user, 'web')
            ->followingRedirects()
            ->post(route('attendance.break_start'));

        $this->actingAs($this->user, 'web')
            ->followingRedirects()
            ->patch(route('attendance.break_end'));

        $response = $this->actingAs($this->user, 'web')
            ->followingRedirects()
            ->post(route('attendance.break_start'));

        $response->assertStatus(200)
            ->assertSee('<button type="submit" class="attendance-create__button attendance-create__button--white">休憩戻</button>', false);
    }

    public function testBreakTimeIsVisibleInAttendanceList()
    {
        $this->actingAs($this->user, 'web')
            ->get(route('attendance.create'));

        $this->actingAs($this->user, 'web')
            ->followingRedirects()
            ->post(route('attendance.break_start'));

        $this->now = Carbon::create(2025, 5, 10, 14, 0, 0, 'Asia/Tokyo');
        Carbon::setTestNow($this->now);

        $this->actingAs($this->user, 'web')
            ->followingRedirects()
            ->patch(route('attendance.break_end'));

        $response = $this->actingAs($this->user, 'web')
            ->get(route('attendance.index', [
                'month' => $this->now->format('Y-m')
            ]));

        $response->assertStatus(200)
            ->assertSeeText($this->now->isoFormat('MM/DD(ddd)'))
            ->assertSeeText('1:00');
    }
}
