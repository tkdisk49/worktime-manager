<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'work_status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'work_status' => 'integer',
    ];

    public const WORK_OFF_DUTY = 0;
    public const WORK_WORKING = 1;
    public const WORK_ON_BREAK = 2;
    public const WORK_LEFT_WORK = 3;

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function attendanceModifications()
    {
        return $this->hasMany(AttendanceModification::class);
    }

    public function breakTimeModifications()
    {
        return $this->hasMany(BreakTimeModification::class);
    }
}
