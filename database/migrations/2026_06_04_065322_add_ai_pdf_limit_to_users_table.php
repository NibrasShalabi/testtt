<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('users', function (Blueprint $table) {
        // إضافة عمود الرصيد مع إعطاء 5 محاولات مجانية كبداية
        $table->integer('ai_pdf_limit')->default(5)->after('email');
    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('ai_pdf_limit');
    });
}
};
