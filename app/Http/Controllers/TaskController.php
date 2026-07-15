<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Statistic;

class TaskController extends Controller {

    public function store(Request $request) {

     $request->validate([
        'Description' => 'required',
        'TaskPriority' => 'required|in:high,medium,low',
        'SubjectID' => 'required|exists:subjects,id'
    ]);

    // تحويل القيمة إلى 1 أو 0 بشكل صحيح
    $stateValue = filter_var($request->State, FILTER_VALIDATE_BOOLEAN);

    $task = Task::create([
        'Description'  => $request->Description,
        'TaskPriority' => $request->TaskPriority,
        'State'        => $stateValue,
        'UserID'       => Auth::id(),
        'SubjectID'    => $request->SubjectID
    ]);

    // 🔥 التعديل الجديد: إذا تمت إضافة المهمة وهي مكتملة من البداية، سجلها بالإحصائيات
    if ($task->State == true) {
        \App\Models\Statistic::create([
            'UserID'           => Auth::id(),
            'activity_type'    => 'task_completion',
            'related_id'       => $task->id,
            'score_percentage' => 100,
            'details'          => ['subject_id' => $task->SubjectID]
        ]);
    }

    return $this->successResponse('تم إضافة المهمة بنجاح', $task, 201);
}


    public function index() {
        $tasks = Task::where('UserID', Auth::id())->get();
        // 🟢 تم التعديل لتوحيد الرد بنجاح
        return $this->successResponse('تم جلب المهام بنجاح', $tasks, 200);
    }

    // public function update(Request $request, $id)
    // {
    //     $task = Task::where('id', $id)->where('UserID', Auth::id())->firstOrFail();

    //     $task->update([
    //         'Description' => $request->Description ?? $task->Description,
    //         'TaskPriority' => $request->TaskPriority ?? $task->TaskPriority,
    //         'State' => $request->has('State') ? $request->State : $task->State,
    //     ]);

    //     // 🟢 تم التعديل لتوحيد الرد بنجاح عند التعديل
    //     return $this->successResponse('تم تحديث المهمة بنجاح', $task, 200);
    // }
   public function update(Request $request, $id)
    {



    $task = Task::where('id', $id)->where('UserID', Auth::id())->firstOrFail();

    // استقبال القيمة الجديدة وتحويلها بوضوح
    $newState = $request->has('State') ? filter_var($request->State, FILTER_VALIDATE_BOOLEAN) : $task->State;

    // 🔥 الشرط الذكي: نتحقق هل المهمة تحولت "الآن" من غير مكتملة (0) إلى مكتملة (1)؟
    // إذا كانت 1 من الأساس وأرسلتِ 1 مجدداً، لن يدخل للشرط!
    $wasCompletedJustNow = ($task->State == false && $newState == true);

    $task->update([
        'Description'  => $request->Description ?? $task->Description,
        'TaskPriority' => $request->TaskPriority ?? $task->TaskPriority,
        'State'        => $newState,
    ]);

    // إذا اكتملت للتو، نتحقق أيضاً من قاعدة البيانات كخط حماية ثانٍ
    if ($wasCompletedJustNow) {
        $alreadyLogged = \App\Models\Statistic::where('activity_type', 'task_completion')
                                              ->where('related_id', $task->id)
                                              ->exists();

        if (!$alreadyLogged) {
            \App\Models\Statistic::create([
                'UserID'           => Auth::id(),
                'activity_type'    => 'task_completion',
                'related_id'       => $task->id,
                'score_percentage' => 100,
                'details'          => ['subject_id' => $task->SubjectID]
            ]);
        }
    } else {
        // حبل الأمان: إذا قام المستخدم بإلغاء "الصح" (أعادها إلى 0)، نحذف الإحصائية الخاصة بها
        if ($newState == false) {
            \App\Models\Statistic::where('activity_type', 'task_completion')
                                  ->where('related_id', $task->id)
                                  ->delete();
        }
    }

    return $this->successResponse('تم تحديث المهمة بنجاح', $task, 200);
}
      public function getGeneralTaskStats() {
    $userId = Auth::id();

    $totalTasks = Task::where('UserID', $userId)->count();
    // نقرأ عدد المهام المكتملة من الجدول الموحد
    $completedTasks = \App\Models\Statistic::where('UserID', $userId)
                                           ->where('activity_type', 'task_completion')
                                           ->count();

    $pendingTasks = $totalTasks - $completedTasks;
    $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) . '%' : '0%';

    return $this->successResponse('تم جلب إحصائيات المهام العامة بنجاح', [
        'total_tasks' => $totalTasks,
        'completed_tasks' => $completedTasks,
        'pending_tasks' => $pendingTasks,
        'completion_rate' => $completionRate
    ], 200);
}

    // 2. إحصائيات المهام المربوطة بمادة معينة (مثلاً كم مهمة خلصت بمادة أمن المعلومات)
    // public function getSubjectTaskStats($subjectId) {
    //     $userId = Auth::id();

    //     $totalTasks = Task::where('UserID', $userId)->where('SubjectID', $subjectId)->count();
    //     $completedTasks = Task::where('UserID', $userId)->where('SubjectID', $subjectId)->where('State', true)->count();
    //     $pendingTasks = $totalTasks - $completedTasks;

    //     $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) . '%' : '0%';

    //     // 🟢 تم التعديل لتوحيد الرد بنجاح وتمرير المصفوفة بداخل الـ data
    //     return $this->successResponse('تم جلب إحصائيات مهام المادة بنجاح', [
    //         'subject_id' => $subjectId,
    //         'total_tasks' => $totalTasks,
    //         'completed_tasks' => $completedTasks,
    //         'pending_tasks' => $pendingTasks,
    //         'completion_rate' => $completionRate
    //     ], 200);
    // }
     public function getSubjectTaskStats($subjectId) {
    $userId = Auth::id();

    $totalTasks = Task::where('UserID', $userId)->where('SubjectID', $subjectId)->count();

    // جلب الإحصائيات المكتملة لنفس المادة
    $completedTasks = \App\Models\Statistic::where('UserID', $userId)
                                           ->where('activity_type', 'task_completion')
                                           ->where('details->subject_id', $subjectId)
                                           ->count();

    $pendingTasks = $totalTasks - $completedTasks;
    $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) . '%' : '0%';

    return $this->successResponse('تم جلب إحصائيات مهام المادة بنجاح', [
        'subject_id' => $subjectId,
        'total_tasks' => $totalTasks,
        'completed_tasks' => $completedTasks,
        'pending_tasks' => $pendingTasks,
        'completion_rate' => $completionRate
    ], 200);
}
}
