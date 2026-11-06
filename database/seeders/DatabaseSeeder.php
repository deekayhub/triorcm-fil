<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with the demo dataset
     * described in requirement.md §15.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            PracticeSeeder::class,
            PayerSeeder::class,
            ProviderSeeder::class,
            DocumentSeeder::class,
            EnrollmentSeeder::class,
            AttestationSeeder::class,
            TaskSeeder::class,
            NoteSeeder::class,
            LoginEventSeeder::class,
            AuditLogSeeder::class,
            SettingSeeder::class,
        ]);
    }
}
