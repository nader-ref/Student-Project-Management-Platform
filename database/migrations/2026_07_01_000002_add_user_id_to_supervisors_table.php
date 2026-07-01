<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $unmatchedSupervisors = DB::table('supervisors')
            ->leftJoin('users', 'supervisors.email', '=', 'users.email')
            ->whereNull('users.id')
            ->select('supervisors.id', 'supervisors.email')
            ->get();

        if ($unmatchedSupervisors->isNotEmpty()) {
            $supervisors = $unmatchedSupervisors
                ->map(fn ($supervisor) => "ID {$supervisor->id} ({$supervisor->email})")
                ->implode(', ');

            throw new RuntimeException(
                "Cannot link supervisors to users. Create matching users for: {$supervisors}"
            );
        }

        Schema::table('supervisors', function (Blueprint $table) {
            if (! Schema::hasColumn('supervisors', 'user_id')) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('email')
                    ->unique()
                    ->constrained('users')
                    ->restrictOnDelete();
            }
        });

        $linkedSupervisors = DB::table('supervisors')
            ->join('users', 'supervisors.email', '=', 'users.email')
            ->select('supervisors.id as supervisor_id', 'users.id as user_id')
            ->get();

        foreach ($linkedSupervisors as $linkedSupervisor) {
            DB::table('supervisors')
                ->where('id', $linkedSupervisor->supervisor_id)
                ->update(['user_id' => $linkedSupervisor->user_id]);
        }
    }

    public function down(): void
    {
        Schema::table('supervisors', function (Blueprint $table) {
            if (Schema::hasColumn('supervisors', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropUnique(['user_id']);
                $table->dropColumn('user_id');
            }
        });
    }
};
