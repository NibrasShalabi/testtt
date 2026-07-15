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
       Schema::create('tasks', function (Blueprint $table) {
       $table->id();
       $table->text('Description');
       $table->enum('TaskPriority', ['high', 'medium', 'low']);
       $table->boolean('State')->default(false);
    
       // الربط مع المستخدم ومع المادة
       $table->foreignId('UserID')->constrained('users')->onDelete('cascade');
       $table->foreignId('SubjectID')->constrained('subjects')->onDelete('cascade');
    
        $table->timestamps();
        }); 
   }  

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
