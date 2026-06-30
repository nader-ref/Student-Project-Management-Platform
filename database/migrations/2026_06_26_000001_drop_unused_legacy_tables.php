<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remove legacy tables that are not referenced by the application.
     */
    public function up(): void
    {
        Schema::dropIfExists('requests');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('supervisorlogs');
        Schema::dropIfExists('englishes');
        Schema::dropIfExists('turkeys');
        Schema::dropIfExists('elevels');
        Schema::dropIfExists('tlevels');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Legacy tables are intentionally not recreated.
    }
};
