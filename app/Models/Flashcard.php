<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Flashcard extends Model
{
   // السماح بتعبئة الحقول
    protected $fillable = ['Question', 'Answer', 'SubjectID', 'UserID'];

    // علاقة: البطاقة تنتمي لمادة
    public function subject() {
        return $this->belongsTo(Subject::class, 'SubjectID');
    }
}
