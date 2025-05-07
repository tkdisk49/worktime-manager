<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    /** @var \App\Models\User */
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function testOffDutyStatusLabelIsDisplayed()
    {
        $this->user->update(['work_status' => User::WORK_OFF_DUTY]);

        $response = $this->actingAs($this->user, 'web')
            ->get(route('attendance.create'));

        $response->assertStatus(200);
        $response->assertSeeText('勤務外');
    }

    public function testWorkingStatusLabelIsDisplayed()
    {
        $this->user->update(['work_status' => User::WORK_WORKING]);

        // 当日の勤怠データがない場合、ステータスが勤務外にリセットされるため作成
        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => Carbon::today(),
            'clock_in' => '09:00:00',
            'clock_out' => null,
        ]);

        $response = $this->actingAs($this->user, 'web')
            ->get(route('attendance.create'));

        $response->assertStatus(200);
        $response->assertSeeText('出勤中');
    }

    public function testOnBreakStatusLabelIsDisplayed()
    {
        $this->user->update(['work_status' => User::WORK_ON_BREAK]);

        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => Carbon::today(),
            'clock_in' => '09:00:00',
            'clock_out' => null,
        ]);

        $response = $this->actingAs($this->user, 'web')
            ->get(route('attendance.create'));

        $response->assertStatus(200);
        $response->assertSeeText('休憩中');
    }

    public function testLeftWorkStatusLabelIsDisplayed()
    {
        $this->user->update(['work_status' => User::WORK_LEFT_WORK]);

        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => Carbon::today(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($this->user, 'web')
            ->get(route('attendance.create'));

        $response->assertStatus(200);
        $response->assertSeeText('退勤済');
    }
}
