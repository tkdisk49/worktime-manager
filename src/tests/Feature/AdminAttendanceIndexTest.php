<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceIndexTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;
    protected Carbon $now;
    protected string $today;
    protected string $yesterday;
    protected string $tomorrow;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::factory()->create();
        $this->actingAs($this->admin, 'admin');

        $users = User::factory()->count(5)->create();

        $this->now = Carbon::create(2025, 5, 10, 18, 0, 0, 'Asia/Tokyo');
        Carbon::setTestNow($this->now);

        $this->today = $this->now->toDateString();
        $this->yesterday = $this->now->copy()->subDay()->toDateString();
        $this->tomorrow = $this->now->copy()->addDay()->toDateString();

        foreach ($users as $user) {
            Attendance::factory()->create([
                'user_id' => $user->id,
                'work_date' => $this->yesterday,
            ]);

            Attendance::factory()->create([
                'user_id' => $user->id,
                'work_date' => $this->today,
            ]);

            Attendance::factory()->create([
                'user_id' => $user->id,
                'work_date' => $this->tomorrow,
            ]);
        }
    }

    public function testAttendanceRecordsForAllUsersOnTheDay()
    {
        $response = $this->get(route('admin.attendance.list'));

        $response->assertStatus(200);

        $attendances = Attendance::whereDate('work_date', $this->today)->get();

        foreach ($attendances as $attendance) {
            $response->assertSeeText($attendance->user->name);
            $response->assertSeeText($attendance->formatted_clock_in);
            $response->assertSeeText($attendance->formatted_clock_out);
            $response->assertSeeText($attendance->formatted_total_break_time);
            $response->assertSeeText($attendance->formatted_total_work_time);
        }
    }

    public function testDisplaysCurrentDateOnAttendanceList()
    {
        $response = $this->get(route('admin.attendance.list'));

        $response->assertStatus(200)
            ->assertSeeText($this->now->format('Y/m/d'));
    }

    public function testDisplaysPreviousDayRecordsWhenPreviousButtonClicked()
    {
        $this->get(route('admin.attendance.list'));

        $response = $this->get(route('admin.attendance.list', [
            'date' => $this->yesterday,
        ]));

        $response->assertStatus(200);

        $attendances = Attendance::whereDate('work_date', $this->yesterday)->get();

        foreach ($attendances as $attendance) {
            $response->assertSeeText($attendance->user->name);
            $response->assertSeeText($attendance->formatted_clock_in);
            $response->assertSeeText($attendance->formatted_clock_out);
            $response->assertSeeText($attendance->formatted_total_break_time);
            $response->assertSeeText($attendance->formatted_total_work_time);
        }
    }

    public function testDisplaysNextDayRecordsWhenNextButtonClicked()
    {
        $this->get(route('admin.attendance.list'));

        $response = $this->get(route('admin.attendance.list', [
            'date' => $this->tomorrow,
        ]));

        $response->assertStatus(200);

        $attendances = Attendance::whereDate('work_date', $this->tomorrow)->get();

        foreach ($attendances as $attendance) {
            $response->assertSeeText($attendance->user->name);
            $response->assertSeeText($attendance->formatted_clock_in);
            $response->assertSeeText($attendance->formatted_clock_out);
            $response->assertSeeText($attendance->formatted_total_break_time);
            $response->assertSeeText($attendance->formatted_total_work_time);
        }
    }
}
