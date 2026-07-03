<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const DATE_COLUMNS = [
        'seminar_1',
        'seminar_2',
        'seminar_3',
        'final',
    ];

    public function up(): void
    {
        $this->assertExistingDateValuesAreValid();

        Schema::table('uni_projects', function (Blueprint $table) {
            $table->date('seminar_1')->nullable()->change();
            $table->date('seminar_2')->nullable()->change();
            $table->date('seminar_3')->nullable()->change();
            $table->date('final')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('uni_projects', function (Blueprint $table) {
            $table->integer('seminar_1')->nullable()->change();
            $table->integer('seminar_2')->nullable()->change();
            $table->integer('seminar_3')->nullable()->change();
            $table->integer('final')->nullable()->change();
        });
    }

    private function assertExistingDateValuesAreValid(): void
    {
        $invalid = [];

        foreach (DB::table('uni_projects')->get(self::DATE_COLUMNS) as $project) {
            foreach (self::DATE_COLUMNS as $column) {
                $value = $project->{$column};

                if ($value === null) {
                    continue;
                }

                $stringValue = (string) $value;

                if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $stringValue)) {
                    $invalid[] = "project {$project->id} {$column}={$stringValue}";

                    continue;
                }

                $parsed = date_create_immutable($stringValue);

                if ($parsed === false || $parsed->format('Y-m-d') !== $stringValue) {
                    $invalid[] = "project {$project->id} {$column}={$stringValue}";
                }
            }
        }

        if ($invalid !== []) {
            throw new \RuntimeException(
                'Cannot change uni_projects milestone columns to date: invalid existing values detected: '
                .implode('; ', $invalid)
            );
        }
    }
};
