<?php

//namespace App\Models;

//use Illuminate\Database\Eloquent\Model;

//class Task extends Model
//{
  //  protected $fillable = ['Description', 'TaskPriority', 'State', 'UserID', 'SubjectID'];

     // علاقة المهمة بالمادة
  //public function subject() {
    //return $this->belongsTo(Subject::class, 'SubjectID');
 //}
//}


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Statistic;

class Task extends Model
{
    protected $fillable = ['Description', 'TaskPriority', 'State', 'UserID', 'SubjectID'];

    protected static function booted()
    {
        // هذا الكود يعمل تلقائياً عند أي عملية update على المهمة
        static::updated(function ($task) {
            // إذا تغيرت الحالة إلى true (مكتملة)
            if ($task->wasChanged('State') && $task->State == true) {
                Statistic::create([
                    'UserID'           => $task->UserID,
                    'activity_type'    => 'task_completion',
                    'related_id'       => $task->id,
                    'score_percentage' => 100,
                    'details'          => ['subject_id' => $task->SubjectID]
                ]);
            }
        });
    }
}
