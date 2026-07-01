<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->assertRelationalDataIsComplete();

        $this->enforceNotNull('projectrequests', ['project_id', 'requested_by_user_id']);
        $this->enforceNotNull('ideas', ['supervisor_id', 'requested_by_user_id']);
        $this->enforceNotNull('contacts', ['student_user_id', 'supervisor_id']);
        $this->enforceNotNull('supcontacts', ['supervisor_id', 'project_id']);
        $this->enforceNotNull('project_submissions', ['submitted_by_user_id']);

        Schema::table('projectrequests', function (Blueprint $table) {
            $table->dropColumn([
                'projectid',
                'nameone',
                'nametwo',
                'namethree',
                'oneid',
                'twoid',
                'threeid',
            ]);
        });

        Schema::table('ideas', function (Blueprint $table) {
            $table->dropColumn([
                'supname',
                'nameone',
                'nametwo',
                'namethree',
                'oneid',
                'twoid',
                'threeid',
            ]);
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn(['email', 'supname']);
        });

        Schema::table('supcontacts', function (Blueprint $table) {
            $table->dropColumn(['supname', 'projectname']);
        });

        Schema::table('project_submissions', function (Blueprint $table) {
            $table->dropColumn(['student_email', 'student_name']);
        });
    }

    public function down(): void
    {
        Schema::table('project_submissions', function (Blueprint $table) {
            $table->string('student_email')->nullable()->after('project_id');
            $table->string('student_name')->nullable()->after('student_email');
        });

        Schema::table('supcontacts', function (Blueprint $table) {
            $table->string('supname')->nullable()->after('id');
            $table->string('projectname')->nullable()->after('supname');
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->string('email')->nullable()->after('id');
            $table->string('supname')->nullable()->after('email');
        });

        Schema::table('ideas', function (Blueprint $table) {
            $table->string('supname')->nullable()->after('projectname');
            $table->string('nameone')->nullable()->after('count');
            $table->integer('oneid')->nullable()->after('nameone');
            $table->string('nametwo')->nullable()->after('oneid');
            $table->integer('twoid')->nullable()->after('nametwo');
            $table->string('namethree')->nullable()->after('twoid');
            $table->integer('threeid')->nullable()->after('namethree');
        });

        Schema::table('projectrequests', function (Blueprint $table) {
            $table->integer('projectid')->nullable()->after('id');
            $table->string('nameone')->nullable()->after('count');
            $table->integer('oneid')->nullable()->after('nameone');
            $table->string('nametwo')->nullable()->after('oneid');
            $table->integer('twoid')->nullable()->after('nametwo');
            $table->string('namethree')->nullable()->after('twoid');
            $table->integer('threeid')->nullable()->after('namethree');
        });

        $this->relaxNotNull('project_submissions', ['submitted_by_user_id']);
        $this->relaxNotNull('supcontacts', ['supervisor_id', 'project_id']);
        $this->relaxNotNull('contacts', ['student_user_id', 'supervisor_id']);
        $this->relaxNotNull('ideas', ['supervisor_id', 'requested_by_user_id']);
        $this->relaxNotNull('projectrequests', ['project_id', 'requested_by_user_id']);
    }

    private function assertRelationalDataIsComplete(): void
    {
        $problems = [];

        $nullProjectRequests = DB::table('projectrequests')
            ->whereNull('project_id')
            ->orWhereNull('requested_by_user_id')
            ->pluck('id')
            ->all();

        if ($nullProjectRequests !== []) {
            $problems[] = 'projectrequests with NULL project_id or requested_by_user_id: '.implode(', ', $nullProjectRequests);
        }

        $requestsWithoutMembers = DB::table('projectrequests as pr')
            ->leftJoin('project_request_members as prm', 'pr.id', '=', 'prm.project_request_id')
            ->whereNull('prm.id')
            ->pluck('pr.id')
            ->all();

        if ($requestsWithoutMembers !== []) {
            $problems[] = 'projectrequests without project_request_members rows: '.implode(', ', $requestsWithoutMembers);
        }

        $nullIdeas = DB::table('ideas')
            ->whereNull('supervisor_id')
            ->orWhereNull('requested_by_user_id')
            ->pluck('id')
            ->all();

        if ($nullIdeas !== []) {
            $problems[] = 'ideas with NULL supervisor_id or requested_by_user_id: '.implode(', ', $nullIdeas);
        }

        $ideasWithoutMembers = DB::table('ideas as i')
            ->leftJoin('idea_members as im', 'i.id', '=', 'im.idea_id')
            ->whereNull('im.id')
            ->pluck('i.id')
            ->all();

        if ($ideasWithoutMembers !== []) {
            $problems[] = 'ideas without idea_members rows: '.implode(', ', $ideasWithoutMembers);
        }

        $nullContacts = DB::table('contacts')
            ->whereNull('student_user_id')
            ->orWhereNull('supervisor_id')
            ->pluck('id')
            ->all();

        if ($nullContacts !== []) {
            $problems[] = 'contacts with NULL student_user_id or supervisor_id: '.implode(', ', $nullContacts);
        }

        $nullAnnouncements = DB::table('supcontacts')
            ->whereNull('supervisor_id')
            ->orWhereNull('project_id')
            ->pluck('id')
            ->all();

        if ($nullAnnouncements !== []) {
            $problems[] = 'supcontacts with NULL supervisor_id or project_id: '.implode(', ', $nullAnnouncements);
        }

        $nullSubmissions = DB::table('project_submissions')
            ->whereNull('submitted_by_user_id')
            ->pluck('id')
            ->all();

        if ($nullSubmissions !== []) {
            $problems[] = 'project_submissions with NULL submitted_by_user_id: '.implode(', ', $nullSubmissions);
        }

        if ($problems !== []) {
            throw new RuntimeException(
                "Phase 11 migration aborted. Fix the following data issues manually, then re-run migrate:\n- "
                .implode("\n- ", $problems)
            );
        }
    }

    private function enforceNotNull(string $table, array $columns): void
    {
        $driver = Schema::getConnection()->getDriverName();

        foreach ($columns as $column) {
            match ($driver) {
                'mysql', 'mariadb' => DB::statement(
                    "ALTER TABLE `{$table}` MODIFY `{$column}` BIGINT UNSIGNED NOT NULL"
                ),
                'pgsql' => DB::statement(
                    "ALTER TABLE {$table} ALTER COLUMN {$column} SET NOT NULL"
                ),
                default => null,
            };
        }
    }

    private function relaxNotNull(string $table, array $columns): void
    {
        $driver = Schema::getConnection()->getDriverName();

        foreach ($columns as $column) {
            match ($driver) {
                'mysql', 'mariadb' => DB::statement(
                    "ALTER TABLE `{$table}` MODIFY `{$column}` BIGINT UNSIGNED NULL"
                ),
                'pgsql' => DB::statement(
                    "ALTER TABLE {$table} ALTER COLUMN {$column} DROP NOT NULL"
                ),
                default => null,
            };
        }
    }
};
