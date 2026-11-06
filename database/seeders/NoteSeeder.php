<?php

namespace Database\Seeders;

use App\Models\Note;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Database\Seeder;

class NoteSeeder extends Seeder
{
    /**
     * Seed 20 provider notes (requirement.md §3.2 tab 8).
     */
    public function run(): void
    {
        $providers = Provider::query()->get();
        $userIds = User::query()->pluck('id');

        for ($i = 0; $i < 20; $i++) {
            Note::factory()->create([
                'provider_id' => $providers->random()->id,
                'user_id' => $userIds->random(),
            ]);
        }
    }
}
