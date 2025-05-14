<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeAttendanceModificationValidationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Carbon $now;
    protected Attendance $attendance;
    protected BreakTime $breakTime;
    protected string $today;

    protected function setUp(): void
    {
        parent::setUp();

        $this->now = Carbon::create(2025, 5, 10, 18, 0, 0, 'Asia/Tokyo');
        Carbon::setTestNow($this->now);

        $this->today = $this->now->toDateString();

        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'web');

        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => $this->today,
        ]);

        $this->breakTime = BreakTime::factory()->create([
            'attendance_id' => $this->attendance->id,
        ]);
    }

    public function testDisplaysErrorWhenClockInIsAfterClockOut()
    {
        $this->get(route('attendance.modification.show', [
            'id' => $this->attendance->id,
        ]))->assertStatus(200);

        $response = $this->post(route('attendance.modification.store', [
            'id' => $this->attendance->id,
        ]), [
            'new_clock_in' => '19:00',
            'new_clock_out' => '18:00',
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

        $response = $this->post(route('attendance.modification.store', [
            'id' => $this->attendance->id,
        ]), [
            'new_clock_in' => '08:00',
            'new_clock_out' => '18:00',
            'new_remarks' => 'Test',
            'existing_breaks' => [
                [
                    'start' => '19:00',
                    'end' => '20:00',
                ],
            ],
        ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors([
                'existing_breaks.0.start' => '休憩時間が勤務時間外です',
            ]);

        $response = $this->post(route('attendance.modification.store', [
            'id' => $this->attendance->id,
        ]), [
            'new_clock_in' => '08:00',
            'new_clock_out' => '18:00',
            'new_remarks' => 'Test',
            'new_break_start' => '19:00',
            'new_break_end' => '20:00',
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

        $response = $this->post(route('attendance.modification.store', [
            'id' => $this->attendance->id,
        ]), [
            'new_clock_in' => '08:00',
            'new_clock_out' => '18:00',
            'new_remarks' => 'Test',
            'existing_breaks' => [
                [
                    'start' => '17:00',
                    'end' => '19:00',
                ],
            ],
        ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors([
                'existing_breaks.0.start' => '休憩時間が勤務時間外です',
            ]);

        $response = $this->post(route('attendance.modification.store', [
            'id' => $this->attendance->id,
        ]), [
            'new_clock_in' => '08:00',
            'new_clock_out' => '18:00',
            'new_remarks' => 'Test',
            'new_break_start' => '17:00',
            'new_break_end' => '19:00',
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

        $response = $this->post(route('attendance.modification.store', [
            'id' => $this->attendance->id,
        ]), [
            'new_clock_in' => '08:00',
            'new_clock_out' => '18:00',
            'new_remarks' => '',
        ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors([
                'new_remarks' => '備考を記入してください',
            ]);
    }
}
