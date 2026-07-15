<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{public function up()
{
    Schema::create('statistics', function (Blueprint $table) {
        $table->id();
        // ربط الإحصائية بالطالب اللي عم يشتغل
        $table->foreignId('UserID')->constrained('users')->onDelete('cascade');

        // 🔥 هون بنحدد نوع الميزة: 'task', 'manual_flashcard', 'ai_flashcard', 'pomodoro'
        $table->string('activity_type');

        // آيدي المادة أو التلخيص أو المهمة (إذا وجد، وممكن يكون null في البومودورو مثلاً)
        $table->unsignedBigInteger('related_id')->nullable();

        // النسبة المئوية أو النتيجة النهائية (إذا الميزة ما فيها نسبة حطيها 0)
        $table->decimal('score_percentage', 5, 2)->default(0);

        // 🔥 السحر كله هنا: عمود JSON بياخد أي حسابات طالعة من أكوادك وبيحفظها فوراً
        $table->json('details')->nullable();

        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('statistics');
}
};
