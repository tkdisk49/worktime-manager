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

class EmployeeAttendanceModificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Admin $admin;
    protected Carbon $now;
    protected Attendance $attendance;
    protected BreakTime $breakTime;
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

        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => $this->yesterday,
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

        $this->assertDatabaseHas('attendance_modifications', [
            'attendance_id' => $this->attendance->id,
            'user_id' => $this->user->id,
            'new_clock_in' => $newClockIn,
            'new_clock_out' => $newClockOut,
            'new_remarks' => $newRemarks,
        ]);

        $this->assertDatabaseHas('break_time_modifications', [
            'attendance_id' => $this->attendance->id,
            'break_time_id' => $this->breakTime->id,
            'user_id' => $this->user->id,
            'new_break_start' => $newBreakStart,
            'new_break_end' => $newBreakEnd,
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

    public function testAllPendingRequestsAreDisplayed()
    {
        $newRemarks = 'Test';

        $attendances = collect([
            Attendance::factory()->create([
                'user_id' => $this->user->id,
                'work_date' => $this->today,
            ]),

            Attendance::factory()->create([
                'user_id' => $this->user->id,
                'work_date' => $this->yesterday,
            ]),

            Attendance::factory()->create([
                'user_id' => $this->user->id,
                'work_date' => $this->dayBeforeYesterday,
            ]),
        ]);

        $pairs = $attendances->map(function ($attendance) {
            $breakTime = BreakTime::factory()->create([
                'attendance_id' => $attendance->id,
            ]);
            return [
                'attendance' => $attendance,
                'breakTime' => $breakTime,
            ];
        });

        foreach ($pairs as $pair) {
            $attendance = $pair['attendance'];
            $breakTime = $pair['breakTime'];

            $this->get(route('attendance.modification.show', [
                'id' => $attendance->id,
            ]))->assertStatus(200);

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
        }

        $modifications = AttendanceModification::where('user_id', $this->user->id)
            ->get();

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

        $attendances = collect([
            Attendance::factory()->create([
                'user_id' => $this->user->id,
                'work_date' => $this->today,
            ]),

            Attendance::factory()->create([
                'user_id' => $this->user->id,
                'work_date' => $this->yesterday,
            ]),

            Attendance::factory()->create([
                'user_id' => $this->user->id,
                'work_date' => $this->dayBeforeYesterday,
            ]),
        ]);

        $pairs = $attendances->map(function ($attendance) {
            $breakTime = BreakTime::factory()->create([
                'attendance_id' => $attendance->id,
            ]);
            return [
                'attendance' => $attendance,
                'breakTime' => $breakTime,
            ];
        });

        foreach ($pairs as $pair) {
            $attendance = $pair['attendance'];
            $breakTime = $pair['breakTime'];

            $this->get(route('attendance.modification.show', [
                'id' => $attendance->id,
            ]))->assertStatus(200);

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
        }

        $this->admin = Admin::factory()->create();
        $this->actingAs($this->admin, 'admin');

        foreach ($attendances as $attendance) {
            $this->get(route('admin.approval.show', [
                'attendance_correct_request' => $attendance->id,
            ]));

            $this->patch(route('admin.approval.update', [
                'attendance_correct_request' => $attendance->id,
            ]));
        }

        $modifications = AttendanceModification::where('user_id', $this->user->id)
            ->get();

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
