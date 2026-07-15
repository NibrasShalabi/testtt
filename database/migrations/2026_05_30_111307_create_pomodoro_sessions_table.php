<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   
    public function up(): void
{
    Schema::create('pomodoro_sessions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('UserID')->index()->constrained('users')->onDelete('cascade'); 
        
        // 🌟 السطر الجديد: ربط الجلسة بجدول المواد (nullable يعني اختياري، وممكن يكون فاضي)
        $table->foreignId('subject_id')->nullable()->index()->constrained('subjects')->onDelete('cascade'); 
        
        $table->integer('focus_duration')->default(25);
        $table->integer('break_duration')->default(5);
        $table->enum('status', ['pending', 'completed', 'failed'])->default('pending')->index(); 
        $table->timestamp('started_at');
        $table->timestamp('ended_at')->nullable();
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pomodoro_sessions');
    }
};
