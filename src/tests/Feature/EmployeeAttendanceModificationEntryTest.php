<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\AttendanceModification;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeAttendanceModificationEntryTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Admin $admin;
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

    public function testAttendanceModificationRequestIsProcessed()
    {
        $newClockIn = '10:00';
        $newClockOut = '18:00';
        $newRemarks = 'Test';
        $newBreakStart = '12:00';
        $newBreakEnd = '13:00';

        $this->get(route('attendance.modification.show', [
            'id' => $this->attendance->id,
        ]))->assertStatus(200);

        $this->post(route('attendance.modification.store', [
            'id' => $this->attendance->id,
        ]), [
            'new_clock_in' => $newClockIn,
            'new_clock_out' => $newClockOut,
            'new_remarks' => $newRemarks,
            'existing_breaks' => [
                [
                    'id' => $this->breakTime->id,
                    'start' => $newBreakStart,
                    'end' => $newBreakEnd,
                ],
            ],
        ]);

        $this->admin = Admin::factory()->create();
        $this->actingAs($this->admin, 'admin');

        $modification = AttendanceModification::where('attendance_id', $this->attendance->id)
            ->where('user_id', $this->user->id)
            ->first();

        $response =  $this->get(route('employee.requests.index'));

        $response->assertStatus(200);
        $response->assertSee('<td>承認待ち</td>', false);
        $response->assertSeeText($this->user->name);
        $response->assertSeeText($modification->formatted_work_date);
        $response->assertSeeText($newRemarks);
        $response->assertSeeText($modification->formatted_created_at);

        $response = $this->get(route('admin.approval.show', [
            'attendance_correct_request' => $modification->attendance_id,
        ]));

        $formattedYear = Carbon::parse($this->attendance->work_date)->isoFormat('YYYY年');
        $formattedMonthDay = Carbon::parse($this->attendance->work_date)->isoFormat('M月D日');

        $response->assertStatus(200);
        $response->assertSeeText($this->user->name);
        $response->assertSeeText($formattedYear);
        $response->assertSeeText($formattedMonthDay);
        $response->assertSeeText($newClockIn);
        $response->assertSeeText($newClockOut);
        $response->assertSeeText($newBreakStart);
        $response->assertSeeText($newBreakEnd);
        $response->assertSeeText($newRemarks);
    }

    public function testNavigatesToRequestDetailPageWhenDetailLinkClicked()
    {
        $newClockIn = '10:00';
        $newClockOut = '18:00';
        $newRemarks = 'Test';
        $newBreakStart = '12:00';
        $newBreakEnd = '13:00';

        $this->get(route('attendance.modification.show', [
            'id' => $this->attendance->id,
        ]))->assertStatus(200);

        $this->post(route('attendance.modification.store', [
            'id' => $this->attendance->id,
        ]), [
            'new_clock_in' => $newClockIn,
            'new_clock_out' => $newClockOut,
            'new_remarks' => $newRemarks,
            'existing_breaks' => [
                [
                    'id' => $this->breakTime->id,
                    'start' => $newBreakStart,
                    'end' => $newBreakEnd,
                ],
            ],
        ]);

        $modification = AttendanceModification::where('attendance_id', $this->attendance->id)
            ->where('user_id', $this->user->id)
            ->first();

        $this->get(route('employee.requests.index'));

        $response = $this->get(route('attendance.modification.show', [
            'id' => $modification->attendance_id,
        ]));

        $formattedYear = Carbon::parse($this->attendance->work_date)->isoFormat('YYYY年');
        $formattedMonthDay = Carbon::parse($this->attendance->work_date)->isoFormat('M月D日');

        $response->assertStatus(200);
        $response->assertSeeText($this->user->name);
        $response->assertSeeText($formattedYear);
        $response->assertSeeText($formattedMonthDay);
        $response->assertSeeText($newClockIn);
        $response->assertSeeText($newClockOut);
        $response->assertSeeText($newBreakStart);
        $response->assertSeeText($newBreakEnd);
        $response->assertSeeText($newRemarks);
    }
}
