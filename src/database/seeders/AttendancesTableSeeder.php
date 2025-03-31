<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::all();
        $today = Carbon::today();
        $startDate = $today->copy()->subMonth(2)->startOfMonth();

        foreach ($users as $user) {
            $date = $startDate->copy();

            while ($date < $today) {
                if (!in_array($date->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY])) {
                    $clockIn = $date->copy()->setTime(9, 0);
                    $clockOut = $date->copy()->setTime(18, 0);

                    $attendance = Attendance::factory()->create([
                        'user_id' => $user->id,
                        'work_date' => $date->format('Y-m-d'),
                        'clock_in' => $clockIn,
                        'clock_out' => $clockOut,
                        'total_work_time' => 480,
                        'total_break_time' => 60,
                    ]);

                    BreakTime::factory()->create([
                        'attendance_id' => $attendance->id,
                        'break_start' => $date->copy()->setTime(12, 0),
                        'break_end' => $date->copy()->setTime(13, 0),
                    ]);
                }

                $date->addDay();
            }
        }
    }
}
