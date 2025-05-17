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

class EmployeeAttendanceModificationListAndApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Admin $admin;
    protected Carbon $now;
    protected string $today;
    protected string $yesterday;
    protected string $dayBeforeYesterday;

    protected function setUp(): void
    {
        parent::setUp();

        $this->now = Carbon::create(2025, 5, 10, 18, 0, 0, 'Asia/Tokyo');
        Carbon::setTestNow($this->now);

        $this->today = $this->now->toDateString();
        $this->yesterday = $this->now->copy()->subDay()->toDateString();
        $this->dayBeforeYesterday = $this->now->copy()->subDays(2)->toDateString();

        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'web');
    }

    public function testAllPendingRequestsAreDisplayed()
    {
        $newRemarks = 'Test';
        $dates = [
            $this->today,
            $this->yesterday,
            $this->dayBeforeYesterday,
        ];

        $this->createAttendancesAndModifications($dates, $newRemarks);

        $modifications = AttendanceModification::where('user_id', $this->user->id)->get();

        $response = $this->get(route('employee.requests.index'));
        $response->assertStatus(200);

        foreach ($modifications as $modification) {
            $response->assertSee('<td>承認待ち</td>', false);
            $response->assertSeeText($this->user->name);
            $response->assertSeeText($modification->formatted_work_date);
            $response->assertSeeText($newRemarks);
            $response->assertSeeText($modification->formatted_created_at);
        }
    }

    public function testAllApprovedRequestsAreDisplayed()
    {
        $newRemarks = 'Test';

        $dates = [
            $this->today,
            $this->yesterday,
            $this->dayBeforeYesterday,
        ];

        $attendances = $this->createAttendancesAndModifications($dates, $newRemarks);

        $this->admin = Admin::factory()->create();
        $this->actingAs($this->admin, 'admin');

        foreach ($attendances as $attendance) {
            $modification = AttendanceModification::where('attendance_id', $attendance->id)
                ->where('user_id', $this->user->id)
                ->first();

            $this->get(route('admin.approval.show', [
                'attendance_correct_request' => $modification->id,
            ]));

            $this->patch(route('admin.approval.update', [
                'attendance_correct_request' => $modification->id,
            ]));
        }

        $modifications = AttendanceModification::where('user_id', $this->user->id)->get();

        $response = $this->actingAs($this->user, 'web')
            ->get(route('employee.requests.index', [
                'status' => 'approved',
            ]));
        $response->assertStatus(200);

        foreach ($modifications as $modification) {
            $response->assertSee('<td>承認済み</td>', false);
            $response->assertSeeText($this->user->name);
            $response->assertSeeText($modification->formatted_work_date);
            $response->assertSeeText($newRemarks);
            $response->assertSeeText($modification->formatted_created_at);
        }
    }

    private function createAttendancesAndModifications($dates, $newRemarks)
    {
        $attendances = collect();

        foreach ($dates as $date) {
            $attendance = Attendance::factory()->create([
                'user_id' => $this->user->id,
                'work_date' => $date,
            ]);

            $breakTime = BreakTime::factory()->create([
                'attendance_id' => $attendance->id,
            ]);

            $this->get(route('attendance.modification.show', [
                'id' => $attendance->id,
            ]));

            $this->post(route('attendance.modification.store', [
                'id' => $attendance->id,
            ]), [
                'new_clock_in' => '10:00',
                'new_clock_out' => '18:00',
                'new_remarks' => $newRemarks,
                'existing_breaks' => [
                    [
                        'id' => $breakTime->id,
                        'start' => '12:00',
                        'end' => '13:00',
                    ],
                ],
            ]);

            $attendances->push($attendance);
        }

        return $attendances;
    }
}
