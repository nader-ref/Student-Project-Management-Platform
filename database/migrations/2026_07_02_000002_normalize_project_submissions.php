<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_submissions', function (Blueprint $table) {
            if (! Schema::hasColumn('project_submissions', 'submitted_by_user_id')) {
                $table->foreignId('submitted_by_user_id')
                    ->nullable()
                    ->after('project_id')
                    ->constrained('users')
                    ->restrictOnDelete();
            }
        });

        $this->backfillSubmissions();
    }

    public function down(): void
    {
        Schema::table('project_submissions', function (Blueprint $table) {
            if (Schema::hasColumn('project_submissions', 'submitted_by_user_id')) {
                $table->dropForeign(['submitted_by_user_id']);
                $table->dropColumn('submitted_by_user_id');
            }
        });
    }

    private function backfillSubmissions(): void
    {
        $unmatched = [];

        DB::table('project_submissions')->orderBy('id')->each(function ($submission) use (&$unmatched) {
            $user = DB::table('users')->where('email', $submission->student_email)->first();

            if (! $user) {
                $unmatched[] = [
                    'submission_id' => $submission->id,
                    'project_id' => $submission->project_id,
                    'student_email' => $submission->student_email,
                    'student_name' => $submission->student_name,
                ];

                return;
            }

            if ($user->name !== $submission->student_name) {
                Log::warning('Submission backfill name mismatch.', [
                    'submission_id' => $submission->id,
                    'student_email' => $submission->student_email,
                    'legacy_name' => $submission->student_name,
                    'user_name' => $user->name,
                ]);
            }

            DB::table('project_submissions')
                ->where('id', $submission->id)
                ->update([
                    'submitted_by_user_id' => $user->id,
                    'updated_at' => now(),
                ]);
        });

        if ($unmatched !== []) {
            Log::warning('Project submission normalization skipped unmatched student_email values.', $unmatched);
        }
    }
};
