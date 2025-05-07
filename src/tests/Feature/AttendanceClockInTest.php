<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AttendanceClockInTest extends TestCase
{
    use RefreshDatabase;

    /** @var \App\Models\User */
    protected $user;

    /** @var \App\Models\Admin */
    protected $admin;

    /** @var \Carbon\Carbon */
    protected $now;

    protected function setUp(): void
    {
        parent::setUp();

        $this->now = Carbon::create(2025, 5, 1, 8, 0, 0);
        Carbon::setTestNow($this->now);

        $this->user = User::factory()->create();
        $this->admin = Admin::factory()->create();
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
            'work_date' => Carbon::today(),
        ]);

        $response = $this->actingAs($this->user, 'web')
            ->get(route('attendance.create'));

        $response->assertStatus(200)
            ->assertDontSee('<button type="submit" class="attendance-create__button">出勤</button>', false);
    }

    public function testAdminSeesClockInTime() // 管理画面の認識を確認する
    {
        $this->user->update(['work_status' => User::WORK_OFF_DUTY]);

        $this->actingAs($this->user, 'web')
            ->post(route('attendance.work_start'));

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.attendance.list', [
                'id' => $this->user->id,
                'date' => $this->now->format('Y-m-d'),
            ]));

        $response->assertStatus(200)
            ->assertSeeText($this->now->format('Y年m月d日'))
            ->assertSeeText($this->user->name)
            ->assertSeeText($this->now->format('H:i'));
    }
}
