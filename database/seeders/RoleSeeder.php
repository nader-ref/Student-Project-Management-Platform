<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Laratrust\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['user', 'supervisor'] as $roleName) {
            Role::firstOrCreate(
                ['name' => $roleName],
                ['display_name' => ucfirst($roleName), 'description' => $roleName.' role']
            );
        }
    }
}
