<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PomodoroSession extends Model
{
    use HasFactory;

    protected $table = 'pomodoro_sessions';

    // السماح بتعبئة هذه الحقول من خلال الـ API والـ Postman
    protected $fillable = [
        'UserID',
        'subject_id',
        'focus_duration',
        'break_duration',
        'status',
        'started_at',
        'ended_at'
    ];

    // علاقة تربط جلسة البومودورو بالمستخدم صاحب الجلسة
    public function user()
    {
        return $this->belongsTo(User::class, 'UserID');
    }
}