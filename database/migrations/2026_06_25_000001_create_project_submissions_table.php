<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('uni_projects')->cascadeOnDelete();
            $table->string('student_email');
            $table->string('student_name');
            $table->string('milestone');
            $table->string('title');
            $table->string('file_path');
            $table->string('original_filename');
            $table->text('notes')->nullable();
            $table->string('status')->default('submitted');
            $table->text('supervisor_feedback')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_submissions');
    }
};
