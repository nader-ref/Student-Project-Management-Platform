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
        Schema::table('ideas', function (Blueprint $table) {
            $table->foreignId('supervisor_id')
                ->nullable()
                ->after('id')
                ->constrained('supervisors')
                ->restrictOnDelete();
            $table->foreignId('requested_by_user_id')
                ->nullable()
                ->after('supervisor_id')
                ->constrained('users')
                ->restrictOnDelete();
        });

        Schema::create('idea_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idea_id')->constrained('ideas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->unsignedTinyInteger('position');
            $table->timestamps();

            $table->unique(['idea_id', 'user_id']);
            $table->unique(['idea_id', 'position']);
        });

        $unmatched = [];

        DB::table('ideas')->orderBy('id')->each(function ($idea) use (&$unmatched) {
            $supervisor = DB::table('supervisors')->where('name', $idea->supname)->first();

            if ($supervisor) {
                DB::table('ideas')
                    ->where('id', $idea->id)
                    ->update([
                        'supervisor_id' => $supervisor->id,
                        'updated_at' => now(),
                    ]);
            } elseif (! blank($idea->supname)) {
                $unmatched[] = [
                    'idea_id' => $idea->id,
                    'field' => 'supname',
                    'value' => $idea->supname,
                ];
            }

            $legacySlots = [
                1 => $idea->oneid,
                2 => $idea->twoid,
                3 => $idea->threeid,
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
                        'idea_id' => $idea->id,
                        'field' => "member_{$position}",
                        'value' => $legacyStudentId,
                    ];

                    continue;
                }

                if ($position === 1) {
                    DB::table('ideas')
                        ->where('id', $idea->id)
                        ->update([
                            'requested_by_user_id' => $user->id,
                            'updated_at' => now(),
                        ]);
                }

                DB::table('idea_members')->updateOrInsert(
                    [
                        'idea_id' => $idea->id,
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
            Log::warning('Idea backfill skipped unmatched legacy rows.', $unmatched);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('idea_members');

        Schema::table('ideas', function (Blueprint $table) {
            $table->dropForeign(['supervisor_id']);
            $table->dropForeign(['requested_by_user_id']);
            $table->dropColumn(['supervisor_id', 'requested_by_user_id']);
        });
    }
};
