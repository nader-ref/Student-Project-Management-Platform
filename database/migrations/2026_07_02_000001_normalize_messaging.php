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
        Schema::table('contacts', function (Blueprint $table) {
            if (! Schema::hasColumn('contacts', 'student_user_id')) {
                $table->foreignId('student_user_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('users')
                    ->restrictOnDelete();
            }

            if (! Schema::hasColumn('contacts', 'supervisor_id')) {
                $table->foreignId('supervisor_id')
                    ->nullable()
                    ->after('student_user_id')
                    ->constrained('supervisors')
                    ->restrictOnDelete();
            }
        });

        Schema::table('supcontacts', function (Blueprint $table) {
            if (! Schema::hasColumn('supcontacts', 'supervisor_id')) {
                $table->foreignId('supervisor_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('supervisors')
                    ->restrictOnDelete();
            }

            if (! Schema::hasColumn('supcontacts', 'project_id')) {
                $table->foreignId('project_id')
                    ->nullable()
                    ->after('supervisor_id')
                    ->constrained('uni_projects')
                    ->restrictOnDelete();
            }
        });

        $this->backfillContacts();
        $this->backfillSupervisorAnnouncements();
    }

    public function down(): void
    {
        Schema::table('supcontacts', function (Blueprint $table) {
            if (Schema::hasColumn('supcontacts', 'project_id')) {
                $table->dropForeign(['project_id']);
                $table->dropColumn('project_id');
            }

            if (Schema::hasColumn('supcontacts', 'supervisor_id')) {
                $table->dropForeign(['supervisor_id']);
                $table->dropColumn('supervisor_id');
            }
        });

        Schema::table('contacts', function (Blueprint $table) {
            if (Schema::hasColumn('contacts', 'supervisor_id')) {
                $table->dropForeign(['supervisor_id']);
                $table->dropColumn('supervisor_id');
            }

            if (Schema::hasColumn('contacts', 'student_user_id')) {
                $table->dropForeign(['student_user_id']);
                $table->dropColumn('student_user_id');
            }
        });
    }

    private function backfillContacts(): void
    {
        $unmatched = [];

        DB::table('contacts')->orderBy('id')->each(function ($contact) use (&$unmatched) {
            $student = DB::table('users')->where('email', $contact->email)->first();
            $supervisor = DB::table('supervisors')->where('name', $contact->supname)->first();

            DB::table('contacts')
                ->where('id', $contact->id)
                ->update([
                    'student_user_id' => $student?->id,
                    'supervisor_id' => $supervisor?->id,
                    'updated_at' => now(),
                ]);

            if (! $student || ! $supervisor) {
                $unmatched[] = [
                    'contact_id' => $contact->id,
                    'email' => $contact->email,
                    'supname' => $contact->supname,
                    'matched_student' => (bool) $student,
                    'matched_supervisor' => (bool) $supervisor,
                ];
            }
        });

        if ($unmatched !== []) {
            Log::warning('Messaging normalization skipped unmatched contact references.', $unmatched);
        }
    }

    private function backfillSupervisorAnnouncements(): void
    {
        $unmatched = [];

        DB::table('supcontacts')->orderBy('id')->each(function ($announcement) use (&$unmatched) {
            $supervisor = DB::table('supervisors')->where('name', $announcement->supname)->first();
            $project = DB::table('uni_projects')->where('name', $announcement->projectname)->first();

            DB::table('supcontacts')
                ->where('id', $announcement->id)
                ->update([
                    'supervisor_id' => $supervisor?->id,
                    'project_id' => $project?->id,
                    'updated_at' => now(),
                ]);

            if (! $supervisor || ! $project) {
                $unmatched[] = [
                    'supcontact_id' => $announcement->id,
                    'supname' => $announcement->supname,
                    'projectname' => $announcement->projectname,
                    'matched_supervisor' => (bool) $supervisor,
                    'matched_project' => (bool) $project,
                ];
            }
        });

        if ($unmatched !== []) {
            Log::warning('Messaging normalization skipped unmatched supervisor announcement references.', $unmatched);
        }
    }
};
