<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('ai_summaries', function (Blueprint $table) {
        $table->id();
        $table->foreignId('UserID')->constrained('users')->onDelete('cascade');
        $table->string('file_name')->nullable(); // اسم ملف الـ PDF
        $table->json('content'); // رح نخزن هون الأسئلة والأجوبة اللي رجعها الذكاء الاصطناعي
        $table->timestamps();
    });
}
    public function down(): void
    {
        Schema::dropIfExists('ai_summaries');
    }
};
