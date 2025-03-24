<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTimeModification extends Model
{
    use HasFactory;

    protected $fillable = [
        'break_time_id',
        'user_id',
        'new_break_start',
        'new_break_end',
        'approval_status',
    ];

    public const APPROVAL_PENDING = 0;
    public const APPROVAL_APPROVED = 1;

    public function breakTime()
    {
        return $this->belongsTo(BreakTime::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
