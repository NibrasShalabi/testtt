<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\FlashcardController;
use App\Http\Controllers\PomodoroController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\FcmController;

// --- مسارات عامة (متاحة للجميع) ---
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify-register-otp', [AuthController::class, 'verifyRegistrationOtp']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// --- مسارات محمية (تحتاج تسجيل دخول) ---
Route::middleware('auth:sanctum')->group(function () {

    // الحصول على بيانات المستخدم الحالي
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // إدارة المواد (عرض وإضافة)
    Route::get('/subjects', [SubjectController::class, 'index']);
    Route::post('/subjects', [SubjectController::class, 'store']);
    Route::put('/subjects/{id}', [SubjectController::class, 'update']);
    Route::delete('/subjects/{id}', [SubjectController::class, 'destroy']);

    // تسجيل الخروج لإلغاء التوكن
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/tasks', [TaskController::class, 'store']);
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::put('/tasks/{id}', [TaskController::class, 'update']);
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);
    Route::get('/tasks/stats/general', [TaskController::class, 'getGeneralTaskStats']);
    Route::get('/subjects/{subjectId}/tasks/stats', [TaskController::class, 'getSubjectTaskStats']);

    Route::get('/notes', [NoteController::class, 'index']);
    Route::post('/notes', [NoteController::class, 'store']);
    Route::put('/notes/{id}', [NoteController::class, 'update']);
    Route::delete('/notes/{id}', [NoteController::class, 'destroy']);

    Route::get('/materials', [MaterialController::class, 'index']);
    Route::post('/materials', [MaterialController::class, 'store']);
    Route::put('/materials/{id}', [MaterialController::class, 'update']);
    Route::delete('/materials/{id}', [MaterialController::class, 'destroy']);

    Route::post('/materials/share', [\App\Http\Controllers\MaterialController::class, 'shareResource']);
    Route::post('/materials/shared/{id}/save', [\App\Http\Controllers\MaterialController::class, 'saveSharedResource']);
    Route::get('/materials/shared-with-me', [\App\Http\Controllers\MaterialController::class, 'sharedWithMe']);

    Route::post('/materials/link', [MaterialController::class, 'storeLink']);

    Route::post('/flashcards', [FlashcardController::class, 'store']);
    Route::delete('/flashcards/{id}', [FlashcardController::class, 'destroy']);
    Route::put('/flashcards/{id}', [FlashcardController::class, 'update']);

    Route::get('/subjects/{subjectId}/flashcards', [FlashcardController::class, 'showBySubject']);

    Route::post('/flashcards/attempt', [FlashcardController::class, 'recordAttempt']);
    Route::get('/subjects/{subjectId}/stats', [FlashcardController::class, 'getSubjectStats']);

    Route::post('/flashcards/{id}/answer', [FlashcardController::class, 'answerFlashcard']);


    // بدء جلسة جديدة (عامة أو لمادة محددة) وتحديد الأوقات وتفشيل الجلسات القديمة المعلقة
    Route::post('/pomodoro/start', [PomodoroController::class, 'startSession']);
    // إكمال الجلسة بنجاح وتحديث حالتها بالداتابيز بعد التحقق من مرور الوقت الفعلي بالسيرفر
    Route::put('/pomodoro/{id}/complete', [PomodoroController::class, 'completeSession']);
    // جلب إحصائيات المستخدم (مجموع عدد الجلسات الناجحة وإجمالي دقائق الدراسة الفريش)
    Route::get('/pomodoro/stats', [PomodoroController::class, 'getStats']);


    // مسار جلب جميع التنبيهات للمستخدم الحالي
    Route::get('/alerts', [AlertController::class, 'index']);

    // مسار إنشاء تنبيه جديد
    Route::post('/alerts', [AlertController::class, 'store']);
    Route::delete('/alerts/{id}', [AlertController::class, 'destroy']);
    Route::patch('/alerts/{id}/read', [AlertController::class, 'markAsRead']);


    // رابط إرسال الإشعار بالاسم الجديد المبسط
    Route::post('/send-fcm', [FcmController::class, 'sendNotification']);

    Route::post('/test-pdf', [\App\Http\Controllers\MaterialController::class, 'testPdfText']);
Route::post('/ai/generate-flashcards', [\App\Http\Controllers\MaterialController::class, 'generateFlashcardsFromFile']);
    Route::post('/save-flashcards', [\App\Http\Controllers\MaterialController::class, 'saveFlashcardsFromSummary']);
    Route::get('/download-file', [\App\Http\Controllers\FileProxyController::class, 'download']);
});
