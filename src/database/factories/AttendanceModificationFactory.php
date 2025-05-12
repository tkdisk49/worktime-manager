<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\AttendanceModification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceModificationFactory extends Factory
{
    protected $model = AttendanceModification::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'attendance_id' => Attendance::factory(),
            'user_id' => User::factory(),
            'new_clock_in' => '10:00:00',
            'new_clock_out' => '19:00:00',
            'new_total_work_time' => 540,
            'new_remarks' => 'Test modification',
            'approval_status' => 0,
            'approved_by' => null,
        ];
    }
}
