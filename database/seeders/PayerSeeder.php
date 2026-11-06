<?php

namespace Database\Seeders;

use App\Models\Payer;
use Illuminate\Database\Seeder;

class PayerSeeder extends Seeder
{
    /**
     * Seed the ten demo payers (requirement.md §15).
     */
    public function run(): void
    {
        Payer::factory()->count(10)->create();
    }
}
