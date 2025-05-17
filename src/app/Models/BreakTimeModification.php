<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTimeModification extends Model
{
    use HasFactory;

    protected $fillable = [
        'break_time_id',
        'attendance_modification_id',
        'user_id',
        'new_break_start',
        'new_break_end',
        'approval_status',
        'approved_by',
    ];

    public const APPROVAL_PENDING = 0;
    public const APPROVAL_APPROVED = 1;

    public function breakTime()
    {
        return $this->belongsTo(BreakTime::class);
    }

    public function attendanceModification()
    {
        return $this->belongsTo(AttendanceModification::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }

    public function getFormattedNewBreakStartAttribute()
    {
        return $this->new_break_start
            ? Carbon::parse($this->new_break_start)->format('H:i')
            : '';
    }

    public function getFormattedNewBreakEndAttribute()
    {
        return $this->new_break_end
            ? Carbon::parse($this->new_break_end)->format('H:i')
            : '';
    }
}
