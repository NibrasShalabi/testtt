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
    Schema::create('flashcards', function (Blueprint $table) {
        $table->id();
        $table->text('Question'); // السؤال
        $table->text('Answer');   // الجواب
        
        // الربط مع المادة (هاد اللي أكدتي عليه)
        $table->foreignId('SubjectID')->nullable()->constrained('subjects')->onDelete('cascade');
        // الربط مع المستخدم
        $table->foreignId('UserID')->constrained('users')->onDelete('cascade');
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flashcards');
    }
};
