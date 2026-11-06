<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Seed the roles defined in requirement.md §12.1.
     */
    public function run(): void
    {
        $roles = [
            'Super Admin',
            'Admin',
            'Credentialing Manager',
            'Specialist',
            'Read-only',
        ];

        foreach ($roles as $role) {
            Role::findOrCreate($role, 'web');
        }
    }
}
