<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in',
        'clock_out',
        'total_work_time',
        'total_break_time',
        'remarks',
    ];

    protected $casts = [
        'total_work_time' => 'integer',
        'total_break_time' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function modifications()
    {
        return $this->hasMany(AttendanceModification::class);
    }

    public function getFormattedWorkDateAttribute()
    {
        return Carbon::parse($this->work_date)->isoFormat('MM/DD(ddd)');
    }

    public function getFormattedClockInAttribute()
    {
        return Carbon::parse($this->clock_in)->format('H:i');
    }

    public function getFormattedClockOutAttribute()
    {
        return $this->clock_out
            ? Carbon::parse($this->clock_out)->format('H:i')
            : '';
    }

    public function getFormattedTotalBreakTimeAttribute()
    {
        if (is_null($this->total_break_time)) {
            return '';
        }

        $hours = intdiv($this->total_break_time, 60);
        $minutes = $this->total_break_time % 60;

        return sprintf('%d:%02d', $hours, $minutes);
    }

    public function getFormattedTotalWorkTimeAttribute()
    {
        if (is_null($this->total_work_time)) {
            return '';
        }

        $hours = intdiv($this->total_work_time, 60);
        $minutes = $this->total_work_time % 60;

        return sprintf('%d:%02d', $hours, $minutes);
    }
}
