<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'break_start',
        'break_end',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function modifications()
    {
        return $this->hasMany(BreakTimeModification::class);
    }

    public function getFormattedBreakStartAttribute()
    {
        return Carbon::parse($this->break_start)->format('H:i');
    }

    public function getFormattedBreakEndAttribute()
    {
        return $this->break_end
            ? Carbon::parse($this->break_end)->format('H:i')
            : '';
    }
}
