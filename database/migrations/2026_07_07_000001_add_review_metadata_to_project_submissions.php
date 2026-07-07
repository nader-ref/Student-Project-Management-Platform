<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_submissions', function (Blueprint $table) {
            $table->timestamp('reviewed_at')->nullable()->after('supervisor_feedback');
            $table->foreignId('reviewed_by_user_id')
                ->nullable()
                ->after('reviewed_at')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('project_submissions', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by_user_id']);
            $table->dropColumn(['reviewed_at', 'reviewed_by_user_id']);
        });
    }
};
