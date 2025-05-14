<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceModificationTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;
    protected Carbon $now;
    protected User $user;
    protected Attendance $attendance;
    protected BreakTime $breakTime;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::factory()->create();
        $this->actingAs($this->admin, 'admin');

        $this->user = User::factory()->create();

        $this->now = Carbon::create(2025, 5, 10, 18, 0, 0, 'Asia/Tokyo');
        Carbon::setTestNow($this->now);

        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => $this->now->toDateString(),
        ]);

        $this->breakTime = BreakTime::factory()->create([
            'attendance_id' => $this->attendance->id,
        ]);
    }

    public function testAttendanceDetailDisplaysSelectedWorkDate()
    {
        $response = $this->get(route('attendance.modification.show', [
            'id' => $this->attendance->id,
        ]));

        $formattedYear = Carbon::parse($this->attendance->work_date)->isoFormat('YYYY年');
        $formattedMonthDay = Carbon::parse($this->attendance->work_date)->isoFormat('M月D日');

        $response->assertStatus(200);
        $response->assertSeeText($this->user->name);
        $response->assertSeeText($formattedYear);
        $response->assertSeeText($formattedMonthDay);
        $response->assertSee('value="' . $this->attendance->formatted_clock_in . '"', false);
        $response->assertSee('value="' . $this->attendance->formatted_clock_out . '"', false);
        $response->assertSee('value="' . $this->breakTime->formatted_break_start . '"', false);
        $response->assertSee('value="' . $this->breakTime->formatted_break_end . '"', false);
    }

    public function testDisplaysErrorWhenClockInIsAfterClockOut()
    {
        $this->get(route('attendance.modification.show', [
            'id' => $this->attendance->id,
        ]))->assertStatus(200);

        $response = $this->patch(route('admin.attendance.update', [
            'id' => $this->attendance->id,
        ]), [
            'new_clock_in' => '19:00',
            'new_clock_out' => '18:00',
            'existing_breaks' => [
                [
                    'id' => $this->breakTime->id,
                    'start' => '12:00',
                    'end' => '13:00',
                ],
            ],
            'new_remarks' => 'Test',
        ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors([
                'new_clock_in' => '出勤時間もしくは退勤時間が不適切な値です',
            ]);
    }

    public function testDisplaysErrorWhenBreakStartIsAfterClockOut()
    {
        $this->get(route('attendance.modification.show', [
            'id' => $this->attendance->id,
        ]))->assertStatus(200);

        $response = $this->patch(route('admin.attendance.update', [
            'id' => $this->attendance->id,
        ]), [
            'new_clock_in' => '09:00',
            'new_clock_out' => '18:00',
            'existing_breaks' => [
                [
                    'id' => $this->breakTime->id,
                    'start' => '19:00',
                    'end' => '20:00',
                ],
            ],
            'new_remarks' => 'Test',
        ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors([
                'existing_breaks.0.start' => '休憩時間が勤務時間外です',
            ]);

        $response = $this->patch(route('admin.attendance.update', [
            'id' => $this->attendance->id,
        ]), [
            'new_clock_in' => '09:00',
            'new_clock_out' => '18:00',
            'new_break_start' => '19:00',
            'new_break_end' => '20:00',
            'new_remarks' => 'Test',
        ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors([
                'new_break_start' => '休憩時間が勤務時間外です',
            ]);
    }

    public function testDisplaysErrorWhenBreakEndIsAfterClockOut()
    {
        $this->get(route('attendance.modification.show', [
            'id' => $this->attendance->id,
        ]))->assertStatus(200);

        $response = $this->patch(route('admin.attendance.update', [
            'id' => $this->attendance->id,
        ]), [
            'new_clock_in' => '09:00',
            'new_clock_out' => '18:00',
            'existing_breaks' => [
                [
                    'id' => $this->breakTime->id,
                    'start' => '12:00',
                    'end' => '19:00',
                ],
            ],
            'new_remarks' => 'Test',
        ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors([
                'existing_breaks.0.start' => '休憩時間が勤務時間外です',
            ]);

        $response = $this->patch(route('admin.attendance.update', [
            'id' => $this->attendance->id,
        ]), [
            'new_clock_in' => '09:00',
            'new_clock_out' => '18:00',
            'new_break_start' => '12:00',
            'new_break_end' => '19:00',
            'new_remarks' => 'Test',
        ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors([
                'new_break_start' => '休憩時間が勤務時間外です',
            ]);
    }

    public function testDisplaysErrorWhenRemarksIsEmpty()
    {
        $this->get(route('attendance.modification.show', [
            'id' => $this->attendance->id,
        ]))->assertStatus(200);

        $response = $this->patch(route('admin.attendance.update', [
            'id' => $this->attendance->id,
        ]), [
            'new_clock_in' => '09:00',
            'new_clock_out' => '18:00',
            'existing_breaks' => [
                [
                    'id' => $this->breakTime->id,
                    'start' => '12:00',
                    'end' => '13:00',
                ],
            ],
            'new_remarks' => '',
        ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors([
                'new_remarks' => '備考を記入してください',
            ]);
    }
}
