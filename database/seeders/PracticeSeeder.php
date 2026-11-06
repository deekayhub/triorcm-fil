<?php

namespace Database\Seeders;

use App\Models\Practice;
use Illuminate\Database\Seeder;

class PracticeSeeder extends Seeder
{
    /**
     * Seed the five demo practices (requirement.md §15).
     */
    public function run(): void
    {
        Practice::factory()->count(5)->create();
    }
}
