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
    //      Schema::create('materials', function (Blueprint $table)
    //    {
    //        $table->id();
    //        $table->string('ResourceName'); // اسم الملف اللي بيختاره المستخدم
    //        $table->string('FilePath');     // رابط الملف على السيرفر
    //        $table->string('FileType');     // نوع الملف (pdf, png, jpg)

    //        // الربط مع المادة ومع المستخدم
    //        $table->foreignId('SubjectID')->constrained('subjects')->onDelete('cascade');
    //        $table->foreignId('UserID')->constrained('users')->onDelete('cascade');

    //        $table->timestamps();
    //    });
    // }
    public function up(): void
    {
         Schema::create('materials', function (Blueprint $table)
       {
           $table->id();
           $table->string('ResourceName'); 
           $table->string('FilePath');     
           $table->string('FileType');     
           $table->unsignedBigInteger('file_size'); // 🟢 الحقل الجديد: لحفظ حجم الملف بالبايت

           // الربط مع المادة ومع المستخدم
           $table->foreignId('SubjectID')->constrained('subjects')->onDelete('cascade');
           $table->foreignId('UserID')->constrained('users')->onDelete('cascade');

           $table->timestamps();
       });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
