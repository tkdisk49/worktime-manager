<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\BreakTimeModification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BreakTimeModificationFactory extends Factory
{
    protected $model = BreakTimeModification::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'break_time_id' => BreakTime::factory(),
            'attendance_id' => Attendance::factory(),
            'user_id' => User::factory(),
            'new_break_start' => '12:00:00',
            'new_break_end' => '13:00:00',
            'approval_status' => 0,
            'approved_by' => null,
        ];
    }
}
