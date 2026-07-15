<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // public function up(): void
    
    // {
    //     Schema::create('subjects', function (Blueprint $table) {
    //         $table->id(); // هاد الـ PK تبع الجدول
    //         $table->string('subjectName'); // اسم المادة (عربي، رياضيات..)
    //         $table->text('description')->nullable(); // وصف اختياري للمادة
            
    //         // ربط المادة بالمستخدم (كل طالب عنده مواده الخاصة)
    //         $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
    //         $table->timestamps(); // تاريخ الإنشاء والتعديل
    //     });
    // }
    public function up(): void
{
    Schema::create('subjects', function (Blueprint $table) {
        $table->id(); 
        $table->string('subjectName'); 
        $table->text('description')->nullable(); 
        
        // --- الإضافات الجديدة ---
        // حالة الدراسة: تم استخدام enum لتحديد خيارات ثابتة
        $table->enum('study_status', ['pending', 'studying', 'completed', 'review'])->default('pending');
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        $table->timestamps();
    });
}
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
