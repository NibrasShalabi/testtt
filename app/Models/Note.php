<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    //مشان الحماية
    protected $fillable = ['Title', 'Content', 'SubjectID', 'UserID'];
}
