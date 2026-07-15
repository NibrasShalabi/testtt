<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Statistic extends Model
{
    protected $fillable = [
        'UserID',
        'activity_type',
        'related_id',
        'score_percentage',
        'details'
    ];

    protected $casts = [
        'details' => 'array', // تحويل عمود JSON إلى مصفوفة تلقائياً
    ];

    // ربط الإحصائية بالمستخدم
    public function user()
    {
        return $this->belongsTo(User::class, 'UserID');
    }
}
