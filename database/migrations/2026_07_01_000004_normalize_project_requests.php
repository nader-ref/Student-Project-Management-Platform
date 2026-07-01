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
        Schema::table('projectrequests', function (Blueprint $table) {
            $table->foreignId('project_id')
                ->nullable()
                ->after('id')
                ->constrained('uni_projects')
                ->restrictOnDelete();
            $table->foreignId('requested_by_user_id')
                ->nullable()
                ->after('project_id')
                ->constrained('users')
                ->restrictOnDelete();
        });

        Schema::create('project_request_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_request_id')->constrained('projectrequests')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->unsignedTinyInteger('position');
            $table->timestamps();

            $table->unique(['project_request_id', 'user_id']);
            $table->unique(['project_request_id', 'position']);
        });

        $unmatched = [];

        DB::table('projectrequests')->orderBy('id')->each(function ($request) use (&$unmatched) {
            $projectExists = DB::table('uni_projects')->where('id', $request->projectid)->exists();

            if ($projectExists) {
                DB::table('projectrequests')
                    ->where('id', $request->id)
                    ->update([
                        'project_id' => $request->projectid,
                        'updated_at' => now(),
                    ]);
            } elseif (! blank($request->projectid)) {
                $unmatched[] = [
                    'project_request_id' => $request->id,
                    'field' => 'projectid',
                    'value' => $request->projectid,
                ];
            }

            $legacySlots = [
                1 => $request->oneid,
                2 => $request->twoid,
                3 => $request->threeid,
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
                        'project_request_id' => $request->id,
                        'field' => "member_{$position}",
                        'value' => $legacyStudentId,
                    ];

                    continue;
                }

                if ($position === 1) {
                    DB::table('projectrequests')
                        ->where('id', $request->id)
                        ->update([
                            'requested_by_user_id' => $user->id,
                            'updated_at' => now(),
                        ]);
                }

                DB::table('project_request_members')->updateOrInsert(
                    [
                        'project_request_id' => $request->id,
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
            Log::warning('Project request backfill skipped unmatched legacy rows.', $unmatched);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('project_request_members');

        Schema::table('projectrequests', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropForeign(['requested_by_user_id']);
            $table->dropColumn(['project_id', 'requested_by_user_id']);
        });
    }
};
