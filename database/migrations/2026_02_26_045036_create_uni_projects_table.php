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
        Schema::create('uni_projects', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();          
            $table->foreignId('supervisor_id')
              ->nullable()
              ->constrained('supervisors')
              ->onDelete('cascade');
            $table->string('department')->nullable();
            $table->boolean('taken')->default(false);          
            $table->integer('student_count')->nullable();       
            
            // Fixed duplicate studentOneName and missing studentTwoName
            $table->string('student_one_name')->nullable();
            $table->integer('student_one_id')->nullable();
            $table->string('student_two_name')->nullable();
            $table->integer('student_two_id')->nullable();
            $table->string('student_three_name')->nullable();
            $table->integer('student_three_id')->nullable();
            
            // Consider using a separate table for students if more flexibility is needed
            
            $table->integer('seminar_1')->nullable();           
            $table->integer('seminar_2')->nullable();
            $table->integer('seminar_3')->nullable();
            $table->integer('final')->nullable();
            $table->string('status')->nullable();               
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uni_projects');
    }
};
