<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceModification extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'user_id',
        'new_clock_in',
        'new_clock_out',
        'new_total_work_time',
        'new_remarks',
        'approval_status',
        'approved_by',
    ];

    public const APPROVAL_PENDING = 0;
    public const APPROVAL_APPROVED = 1;

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }
}
