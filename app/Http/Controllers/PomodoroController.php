<?php

namespace App\Http\Controllers;

use App\Models\PomodoroSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Statistic;

class PomodoroController extends Controller
{
    // 🟩 1. بدء الجلسة (مرن: عامة أو لمادة محددة + خيارات الوقت الجاهزة أو اليدوية)
    public function startSession(Request $request)
    {
        // التحقق من المدخلات
        $validator = Validator::make($request->all(), [
            'subject_id'     => 'nullable|exists:subjects,id', // مادة اختيارية، ولو أُرسلت يجب أن تكون موجودة بجدول المواد
            'focus_duration' => 'sometimes|integer|min:1|max:120', // وقت الدراسة
            'break_duration' => 'sometimes|integer|min:1|max:60',  // وقت الاستراحة
        ]);

        if ($validator->fails()) {
            // 🔴 تم التعديل لتوحيد رد الخطأ وتمرير أخطاء الـ Validation بداخل الـ data
            return $this->errorResponse('البيانات المرسلة غير صالحة', $validator->errors(), 422);
        }

        // الحماية الذكية: تفشيل أي جلسة قديمة تُرِكت معلقة بسبب خروج المستخدم من التطبيق
        PomodoroSession::where('UserID', auth()->id())
            ->where('status', 'pending')
            ->update(['status' => 'failed', 'ended_at' => now()]);

        // إنشاء الجلسة الجديدة بالخيارات المحددة
        $session = PomodoroSession::create([
            'UserID'         => auth()->id(),
            'subject_id'     => $request->subject_id, // رح تتخزن id المادة، أو null لو كانت دراسة عامة
            'focus_duration' => $request->focus_duration ?? 25, // الافتراضي 25 دقيقة
            'break_duration' => $request->break_duration ?? 5,  // الافتراضي 5 دقائق
            'status'         => 'pending',
            'started_at'     => now(),
        ]);

        // 🟢 تم التعديل لتوحيد الرد بنجاح عند الإنشاء
        return $this->successResponse('تم بدء جلسة البومودورو بنجاح، العداد بدأ الآن.', $session, 201);
    }


    // public function completeSession($id)
    // {
    //     $session = PomodoroSession::where('id', $id)
    //         ->where('UserID', auth()->id())
    //         ->where('status', 'pending')
    //         ->first();

    //     if (!$session) {
    //         // 🔴 تم التعديل لتوحيد رد الخطأ
    //         return $this->errorResponse('الجلسة غير موجودة، أو تم معالجتها مسبقاً.', null, 404);
    //     }

    //     // ⏱️ الحل الجذري: تحويل الأوقات إلى طوابع زمنية رقمية (Timestamps) بالثواني لمنع أي خلل في المناطق الزمنية
    //     $startTimeStamp = \Carbon\Carbon::parse($session->started_at)->timestamp;
    //     $currentTimeStamp = now()->timestamp;

    //     // حساب الثواني الفعلية المارة
    //     $secondsPassed = $currentTimeStamp - $startTimeStamp;

    //     // تحويل مدة الدراسة المطلوبة من دقائق إلى ثواني
    //     $requiredSeconds = $session->focus_duration * 60;

    //     // إذا كانت الثواني المارة أقل من الثواني المطلوبة
    //     if ($secondsPassed < $requiredSeconds) {
    //         // 🔴 تم التعديل لتوحيد رد الخطأ
    //         return $this->errorResponse('لم تنتهِ المدة المحددة للدراسة بعد، لا يمكن إكمال الجلسة.', null, 422);
    //     }

    //     // نجاح الجلسة واحتسابها رسمياً
    //     $session->update([
    //         'status'   => 'completed',
    //         'ended_at' => now()
    //     ]);

    //     // 🟢 تم التعديل لتوحيد الرد بنجاح
    //     return $this->successResponse('تم إكمال وقت الدراسة بنجاح، يمكنك بدء الاستراحة الآن.', $session, 200);
    // }

    // // 🟩 3. جلب الإحصائيات (مجموع الجلسات المكتملة + إجمالي الدقائق)
    // public function getStats()
    // {
    //     $completedSessions = PomodoroSession::where('UserID', auth()->id())
    //         ->where('status', 'completed');

    //     $count = $completedSessions->count();
    //     $totalMinutes = $completedSessions->sum('focus_duration');

    //     // 🟢 تم التعديل لتوحيد الرد بنجاح وتمرير الإحصائيات كمصفوفة بداخل الـ data
    //     return $this->successResponse('تم جلب إحصائيات البومودورو بنجاح.', [
    //         'completed_sessions_count' => $count,
    //         'total_focus_minutes'      => (int)$totalMinutes
    //     ], 200);
    // }

     public function completeSession($id)
    {
        $session = PomodoroSession::where('id', $id)
            ->where('UserID', auth()->id())
            ->where('status', 'pending')
            ->first();

        if (!$session) {
            return $this->errorResponse('الجلسة غير موجودة، أو تم معالجتها مسبقاً.', null, 404);
        }

        $startTimeStamp = \Carbon\Carbon::parse($session->started_at)->timestamp;
        $currentTimeStamp = now()->timestamp;
        $secondsPassed = $currentTimeStamp - $startTimeStamp;
        $requiredSeconds = $session->focus_duration * 60;

        if ($secondsPassed < $requiredSeconds) {
            return $this->errorResponse('لم تنتهِ المدة المحددة للدراسة بعد، لا يمكن إكمال الجلسة.', null, 422);
        }

        // تسجيل الوقت الحالي كـ UTC (هذا هو الوقت الموحد)
        $now = now();

        // نجاح الجلسة واحتسابها رسمياً بالتوقيت الموحد
        $session->update([
            'status'   => 'completed',
            'ended_at' => $now
        ]);

        // 🔥 إضافة الإحصائية في جدول الجوكر مع الحفاظ على نفس التوقيت UTC
        \App\Models\Statistic::create([
            'UserID'           => auth()->id(),
            'activity_type'    => 'pomodoro',
            'related_id'       => $session->id,
            'score_percentage' => 100,
            'details'          => [
                'focus_duration' => $session->focus_duration
            ],
            'created_at'       => $now, // لضمان التطابق
            'updated_at'       => $now
        ]);

        return $this->successResponse('تم إكمال وقت الدراسة بنجاح، يمكنك بدء الاستراحة الآن.', $session, 200);
    }

    public function getStats()
    {
        // الاستعلام عن الإحصائيات (يبقى كما هو لأنه لا يتلاعب بالوقت)
        $stats = \App\Models\Statistic::where('UserID', auth()->id())
            ->where('activity_type', 'pomodoro')
            ->get();

        $count = $stats->count();

        $totalMinutes = $stats->sum(function($stat) {
            return isset($stat->details['focus_duration']) ? $stat->details['focus_duration'] : 0;
        });

        return $this->successResponse('تم جلب إحصائيات البومودورو بنجاح.', [
            'completed_sessions_count' => $count,
            'total_focus_minutes'      => (int)$totalMinutes
        ], 200);
    }
}
