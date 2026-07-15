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
    Schema::create('flashcard_attempts', function (Blueprint $table) {
        $table->id();
        $table->foreignId('FlashcardID')->constrained('flashcards')->onDelete('cascade');
        $table->foreignId('UserID')->constrained('users')->onDelete('cascade');
        $table->boolean('IsCorrect'); // true = عرف الجواب، false = ما عرفه
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flashcard_attempts');
    }
};
