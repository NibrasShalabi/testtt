<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'subjectName',
        'description',
        'user_id',
        'study_status'
    ];

    // علاقة عكسية: المادة تنتمي لمستخدم واحد
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function resources() {
    return $this->hasMany(Material::class, 'SubjectID');
}
}