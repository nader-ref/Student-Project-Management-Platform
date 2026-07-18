<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ideas', function (Blueprint $table) {
            $table->string('similarity_status', 32)->nullable()->after('reason');
            $table->decimal('similarity_percentage', 5, 1)->nullable()->after('similarity_status');
            $table->string('similarity_level', 16)->nullable()->after('similarity_percentage');
            $table->string('similarity_match_source_type', 32)->nullable()->after('similarity_level');
            $table->unsignedBigInteger('similarity_match_source_id')->nullable()->after('similarity_match_source_type');
            $table->string('similarity_match_title', 255)->nullable()->after('similarity_match_source_id');
            $table->timestamp('similarity_checked_at')->nullable()->after('similarity_match_title');
            $table->string('similarity_model', 64)->nullable()->after('similarity_checked_at');
        });
    }

    public function down(): void
    {
        Schema::table('ideas', function (Blueprint $table) {
            $table->dropColumn([
                'similarity_status',
                'similarity_percentage',
                'similarity_level',
                'similarity_match_source_type',
                'similarity_match_source_id',
                'similarity_match_title',
                'similarity_checked_at',
                'similarity_model',
            ]);
        });
    }
};
