<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiSummary extends Model
{
    protected $fillable = ['UserID', 'file_name', 'content'];
protected $casts = ['content' => 'array']; // عشان لاراڤيل يتعامل مع الـ JSON تلقائياً
}
