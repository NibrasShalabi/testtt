<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
     public function up()
{
    Schema::table('ai_summaries', function (Blueprint $table) {
        // إضافة الحقل وربطه بجدول المواد
        $table->foreignId('subject_id')
              ->nullable()
              ->after('UserID') // مشان يترتب الحقل تحت حقل اليوزر
              ->constrained('subjects')
              ->cascadeOnDelete();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_summaries', function (Blueprint $table) {
        // حذف الربط والحقل في حال عملنا تراجع
        $table->dropForeign(['subject_id']);
        $table->dropColumn('subject_id');
    });
    }
};
