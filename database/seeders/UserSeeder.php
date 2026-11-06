<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Seed one user per role (requirement.md §15), password "password".
     */
    public function run(): void
    {
        $users = [
            ['name' => 'Super Admin', 'email' => 'admin@triorcm.test', 'role' => 'Super Admin'],
            ['name' => 'Casey Admin', 'email' => 'credentialing.admin@triorcm.test', 'role' => 'Admin'],
            ['name' => 'Morgan Manager', 'email' => 'credentialing.manager@triorcm.test', 'role' => 'Credentialing Manager'],
            ['name' => 'Sam Specialist', 'email' => 'specialist@triorcm.test', 'role' => 'Specialist'],
            ['name' => 'Riley Reader', 'email' => 'viewer@triorcm.test', 'role' => 'Read-only'],
        ];

        foreach ($users as $data) {
            $user = User::query()->firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => 'password',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
            );

            $user->syncRoles([$data['role']]);
        }
    }
}
