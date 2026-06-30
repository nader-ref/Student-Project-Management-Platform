<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'university_number')) {
                $table->string('university_number')->nullable()->unique()->after('name');
            }
        });

        $studentRole = DB::table('roles')->where('name', 'student')->first();
        $legacyUserRole = DB::table('roles')->where('name', 'user')->first();

        if ($legacyUserRole && ! $studentRole) {
            DB::table('roles')
                ->where('id', $legacyUserRole->id)
                ->update([
                    'name' => 'student',
                    'display_name' => 'Student',
                    'description' => 'Student role',
                    'updated_at' => now(),
                ]);
        } elseif ($legacyUserRole && $studentRole) {
            DB::table('role_user')
                ->where('role_id', $legacyUserRole->id)
                ->update(['role_id' => $studentRole->id]);

            DB::table('roles')->where('id', $legacyUserRole->id)->delete();
        }

        DB::table('roles')->updateOrInsert(
            ['name' => 'student'],
            [
                'display_name' => 'Student',
                'description' => 'Student role',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        DB::table('roles')->updateOrInsert(
            ['name' => 'supervisor'],
            [
                'display_name' => 'Supervisor',
                'description' => 'Supervisor role',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        DB::table('roles')->updateOrInsert(
            ['name' => 'admin'],
            [
                'display_name' => 'Admin',
                'description' => 'Admin role',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }

    public function down(): void
    {
        $studentRole = DB::table('roles')->where('name', 'student')->first();
        $legacyUserRole = DB::table('roles')->where('name', 'user')->first();

        if ($studentRole && ! $legacyUserRole) {
            DB::table('roles')
                ->where('id', $studentRole->id)
                ->update([
                    'name' => 'user',
                    'display_name' => 'User',
                    'description' => 'Student portal user',
                    'updated_at' => now(),
                ]);
        }

        DB::table('roles')->where('name', 'admin')->delete();

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'university_number')) {
                $table->dropUnique(['university_number']);
                $table->dropColumn('university_number');
            }
        });
    }
};
