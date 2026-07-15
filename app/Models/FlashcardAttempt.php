<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlashcardAttempt extends Model
{
    protected $fillable = ['FlashcardID', 'UserID', 'IsCorrect'];
    // ضيفي هاد الجزء: (علاقة المحاولة بالبطاقة)
    public function flashcard()
    {
        return $this->belongsTo(Flashcard::class, 'FlashcardID');
    }
}
