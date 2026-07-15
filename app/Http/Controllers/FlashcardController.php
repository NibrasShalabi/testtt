<?php

namespace App\Http\Controllers;

use App\Models\Flashcard;
 use App\Models\FlashcardAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Statistic;
class FlashcardController extends Controller {

    // إضافة بطاقة (يدوياً)
    public function store(Request $request) {
        $request->validate([
            'Question' => 'required|string',
            'Answer' => 'required|string',
            'SubjectID' => 'nullable|exists:subjects,id'
        ]);

        $flashcard = Flashcard::create([
            'Question' => $request->Question,
            'Answer' => $request->Answer,
            'SubjectID' => $request->SubjectID,
            'UserID' => Auth::id()
        ]);
     return $this->successResponse('تم إضافة الكرت الذكي بنجاح', $flashcard, 201);
    }

    // عرض البطاقات الخاصة بمادة معينة (عشان يدرسها)
    public function showBySubject($subjectId) {
        $flashcards = Flashcard::where('SubjectID', $subjectId)
                                ->where('UserID', Auth::id())
                                ->get();
        return $this->successResponse('تم جلب البطاقات بنجاح', $flashcards);
    }

public function destroy($id)
{
    // جلب البطاقة والتأكد أن المستخدم الحالي هو صاحبها الفعلي باستخدام UserID
    $flashcard = Flashcard::where('id', $id)
                          ->where('UserID', auth()->id())
                          ->first();

    // إذا لم يتم العثور على البطاقة أو كانت تابعة لمستخدم آخر
    if (!$flashcard) {
        return $this->errorResponse('البطاقة غير موجودة أو غير مصرح لك بحذفها', null, 404);
    }

    // حذف البطاقة من قاعدة البيانات
    $flashcard->delete();

   return $this->successResponse('تم حذف بطاقة الفلاش كارد بنجاح', null, 200);
}

public function update(Request $request, $id)
{
    // 1. استخدام sometimes يعني الحقل مطلوب "فقط في حال تم إرساله"
    $request->validate([
        'Question' => 'sometimes|string',
        'Answer'   => 'required_with:Answer|string', // اختياري، لكن لو انبعت ما يكون فاضي
    ]);

    // 2. جلب البطاقة والتأكد من ملكية المستخدم لها
    $flashcard = Flashcard::where('id', $id)
                          ->where('UserID', auth()->id())
                          ->first();

    if (!$flashcard) {
       return $this->errorResponse('البطاقة غير موجودة أو غير مصرح لك بتعديلها', null, 404);
    }

    // 3. التعديل الذكي: يدمج القديم مع الجديد القادم بالطلب فقط
    $flashcard->update(array_filter([
        'Question' => $request->Question ?? $flashcard->Question,
        'Answer'   => $request->Answer ?? $flashcard->Answer,
    ]));

    return $this->successResponse('تم تعديل بطاقة الفلاش كارد بنجاح', $flashcard);

}




// // 1. دالة لتسجيل إذا الطالب عرف الجواب ولا لا
// public function recordAttempt(Request $request)
// {
//     $request->validate([
//         'FlashcardID' => 'required|exists:flashcards,id',
//         'IsCorrect' => 'required|boolean'
//     ]);

//     // 1. فحص إذا كان هناك محاولة سابقة لهذه البطاقة من قبل هذا المستخدم
//     $exists = FlashcardAttempt::where('FlashcardID', $request->FlashcardID)
//                               ->where('UserID', Auth::id())
//                               ->exists();

//     // 2. إذا لم تكن موجودة، قم بإنشائها
//     if (!$exists) {
//         FlashcardAttempt::create([
//             'FlashcardID' => $request->FlashcardID,
//             'UserID' => Auth::id(),
//             'IsCorrect' => $request->IsCorrect
//         ]);

//         return response()->json([
//             'status' => true,
//             'message' => 'تم تسجيل محاولتك الأولى بنجاح'
//         ]);
//     }

//     // 3. إذا كانت موجودة، لا نفعل شيئاً ونخبره أنها مسجلة مسبقاً
//     return response()->json([
//         'status' => false,
//         'message' => 'لقد قمت باختبار هذه البطاقة مسبقاً، الإحصائيات لن تتغير'
//     ]);
// }

// // 2. دالة لجلب إحصائيات مادة معينة
// public function getSubjectStats($subjectId) {
//     // عدد المحاولات الكلية لهي المادة
//     $total = FlashcardAttempt::whereHas('flashcard', function($q) use ($subjectId) {
//         $q->where('SubjectID', $subjectId);
//     })->where('UserID', Auth::id())->count();

//     // عدد الإجابات الصحيحة
//     $correct = FlashcardAttempt::whereHas('flashcard', function($q) use ($subjectId) {
//         $q->where('SubjectID', $subjectId);
//     })->where('UserID', Auth::id())->where('IsCorrect', true)->count();

//     return $this->successResponse('تم جلب إحصائيات المادة بنجاح', [
//         'total_attempts' => $total,
//         'correct_count' => $correct,
//         'wrong_count' => $total - $correct,
//             'success_rate' => $total > 0 ? round(($correct / $total) * 100, 2) . '%' : '0%'
//         ]);


// }
    public function recordAttempt(Request $request)
{
    $request->validate([
        'FlashcardID' => 'required|exists:flashcards,id',
        'IsCorrect' => 'required|boolean'
    ]);

    // 1. فحص إذا كان هناك محاولة سابقة لهذه البطاقة في جدول الإحصائيات الموحد
    $exists = Statistic::where('activity_type', 'flashcard_attempt')
                        ->where('related_id', $request->FlashcardID)
                        ->where('UserID', Auth::id())
                        ->exists();

    // 2. إذا لم تكن موجودة، قم بإنشائها داخل الجدول الموحد
    if (!$exists) {
        Statistic::create([
            'UserID' => Auth::id(),
            'activity_type' => 'flashcard_attempt', // تحديد نوع النشاط هنا
            'related_id' => $request->FlashcardID,   // ربط الإحصائية بآيدي البطاقة
            'score_percentage' => $request->IsCorrect ? 100.00 : 0.00, // 100% إذا صح و 0% إذا خطأ
            'details' => json_encode([
                'is_correct' => $request->IsCorrect
            ])
        ]);

        return response()->json([
            'status' => true,
            'message' => 'تم تسجيل محاولتك الأولى بنجاح'
        ]);
    }

    // 3. إذا كانت موجودة، لا نفعل شيئاً ونخبره أنها مسجلة مسبقاً
    return response()->json([
        'status' => false,
        'message' => 'لقد قمت باختبار هذه البطاقة مسبقاً، الإحصائيات لن تتغير'
    ]);
}

// // 2. دالة لجلب إحصائيات مادة معينة (معدلة لتقرأ من الجدول الموحد)
// public function getSubjectStats($subjectId) {

//     // جلب جميع آيديات الفلاش كاردز التابعة لهذه المادة أولاً لربطها بالإحصائيات
//     $flashcardIds = Flashcard::where('SubjectID', $subjectId)->pluck('id');

//     // عدد المحاولات الكلية لهذه المادة من جدول الإحصائيات الموحد
//     $total = Statistic::where('activity_type', 'flashcard_attempt')
//                       ->whereIn('related_id', $flashcardIds)
//                       ->where('UserID', Auth::id())
//                       ->count();

//     // عدد الإجابات الصحيحة (التي سجلت نسبة 100)
//     $correct = Statistic::where('activity_type', 'flashcard_attempt')
//                         ->whereIn('related_id', $flashcardIds)
//                         ->where('UserID', Auth::id())
//                         ->where('score_percentage', 100)
//                         ->count();

//     return $this->successResponse('تم جلب إحصائيات المادة بنجاح', [
//         'total_attempts' => $total,
//         'correct_count' => $correct,
//         'wrong_count' => $total - $correct,
//         'success_rate' => $total > 0 ? round(($correct / $total) * 100, 2) . '%' : '0%'
//     ]);
// }
// 2. دالة لجلب إحصائيات مادة معينة (معدلة لتقرأ من الجدول الموحد)
    public function getSubjectStats($subjectId) {

        // جلب جميع آيديات الفلاش كاردز التابعة لهذه المادة أولاً لربطها بالإحصائيات
        $flashcardIds = Flashcard::where('SubjectID', $subjectId)->pluck('id');

        // عدد المحاولات الكلية لهذه المادة من جدول الإحصائيات الموحد
        $total = Statistic::where('activity_type', 'flashcard_attempt')
                          ->whereIn('related_id', $flashcardIds)
                          ->where('UserID', Auth::id())
                          ->count();

        // عدد الإجابات الصحيحة (التي سجلت نسبة 100)
        $correct = Statistic::where('activity_type', 'flashcard_attempt')
                            ->whereIn('related_id', $flashcardIds)
                            ->where('UserID', Auth::id())
                            ->where('score_percentage', 100)
                            ->count();

        return $this->successResponse('تم جلب إحصائيات المادة بنجاح', [
            'total_attempts' => $total,
            'correct_count' => $correct,
            'wrong_count' => $total - $correct,
            'success_rate' => $total > 0 ? round(($correct / $total) * 100, 2) . '%' : '0%'
        ]);
    }

    // دالة الإجابة على الفلاش كارد (سواء AI أو يدوي)
    public function answerFlashcard(Request $request, $id)
    {
        // نستقبل هل أجاب الطالب بشكل صحيح أم لا؟ (true / false)
        $request->validate([
            'is_correct' => 'required|boolean'
        ]);

        $flashcard = \App\Models\Flashcard::findOrFail($id);
        $userId = \Illuminate\Support\Facades\Auth::id();

        // 1. 🔍 الشرط الذهبي: هل هذه أول مرة يحل فيها الطالب هذا السؤال؟
        $isFirstAttempt = !\App\Models\Statistic::where('UserID', $userId)
            ->where('activity_type', 'flashcard_answer')
            ->where('related_id', $flashcard->id)
            ->exists();

        if ($isFirstAttempt) {
            // 2. 🟢 إذا أول محاولة، نسجلها بجدول "statistics" (جدول الجوكر)
            \App\Models\Statistic::create([
                'UserID' => $userId,
                'activity_type' => 'flashcard_answer',
                'related_id' => $flashcard->id,
                'score_percentage' => $request->is_correct ? 100 : 0, // 100 إذا صح، 0 إذا خطأ
                'details' => [
                    'subject_id' => $flashcard->SubjectID,
                    'is_correct' => $request->is_correct
                ]
            ]);

            return $this->successResponse('تم الإجابة! وتم تسجيل نتيجتك في الإحصائيات (أول محاولة)', null, 200);
        }

        // 3. 🟡 إذا كانت محاولة مكررة (عم يراجع)، لا نسجلها حتى لا نخرب نسبة مستواه الحقيقي
        return $this->successResponse('تمت الإجابة! (محاولة مكررة للمراجعة - لم تُسجل بالإحصائيات)', null, 200);
    }

}



