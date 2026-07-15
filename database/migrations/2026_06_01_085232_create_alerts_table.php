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
    Schema::create('alerts', function (Blueprint $table) {
        $table->id('AlertID'); // المفتاح الأساسي كما بالمخطط
        $table->string('Address');
        $table->text('Message');
        $table->boolean('IsRead')->default(false); // 0 يعني غير مقروء، 1 يعني مقروء
        $table->foreignId('UserID')->constrained('users')->onDelete('cascade'); // ربطه بجدول المستخدمين
        $table->timestamp('Creat_at')->useCurrent(); // وقت الإنشاء
        $table->timestamps(); // لتوفير created_at و updated_at الافتراضية لـ Laravel
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
