<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('action');
            $table->text('description');
            $table->foreignId('target_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at');

            $table->index('action');
            $table->index('actor_user_id');
            $table->index('target_user_id');
            $table->index(['subject_type', 'subject_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
