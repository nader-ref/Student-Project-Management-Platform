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
        Schema::create('project_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('uni_projects')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->unsignedTinyInteger('position');
            $table->timestamps();

            $table->unique(['project_id', 'user_id']);
            $table->unique(['project_id', 'position']);
        });

        $unmatched = [];

        DB::table('uni_projects')->orderBy('id')->each(function ($project) use (&$unmatched) {
            $legacySlots = [
                1 => $project->student_one_id,
                2 => $project->student_two_id,
                3 => $project->student_three_id,
            ];

            foreach ($legacySlots as $position => $legacyStudentId) {
                if (blank($legacyStudentId)) {
                    continue;
                }

                $user = DB::table('users')
                    ->where('university_number', (string) $legacyStudentId)
                    ->first();

                if (! $user) {
                    $unmatched[] = [
                        'project_id' => $project->id,
                        'project_name' => $project->name,
                        'position' => $position,
                        'legacy_student_id' => $legacyStudentId,
                    ];

                    continue;
                }

                DB::table('project_members')->updateOrInsert(
                    [
                        'project_id' => $project->id,
                        'user_id' => $user->id,
                    ],
                    [
                        'position' => $position,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                );
            }
        });

        if ($unmatched !== []) {
            Log::warning('Project member backfill skipped unmatched legacy student IDs.', $unmatched);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('project_members');
    }
};
