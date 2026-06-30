<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projectrequests', function (Blueprint $table) {
            $table->boolean('rejected')->default(false)->after('accepted');
            $table->string('reason')->nullable()->after('rejected');
        });
    }

    public function down(): void
    {
        Schema::table('projectrequests', function (Blueprint $table) {
            $table->dropColumn(['rejected', 'reason']);
        });
    }
};
