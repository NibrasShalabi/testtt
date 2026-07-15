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
    //     Schema::create('resource_shares', function (Blueprint $table) {
    //         $table->id();
    //         $table->timestamps();
    //     });
    // }
    public function up(): void
    {
        Schema::create('material_shares', function (Blueprint $table) {
            $table->id();

            // 1. هاد بضل متل ما هو (مربوط بجدول المواد)
            $table->foreignId('material_id')->constrained('materials')->onDelete('cascade');

            // 2. التعديل هون: حذفنا سطر الـ shared_with_email وحطينا بداله الـ user_id
            // هاد السطر بيربط جدول المشاركة بـ ID المستخدم بجدول الـ users ومستحيل يتغير
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // 3. هاد بضل متل ما هو (صلاحية الوصول)
            $table->string('access_level')->default('View');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_shares');
    }
};
