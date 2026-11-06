<?php

namespace Database\Seeders;

use App\Models\Provider;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Seed 15 tasks, most related to a provider (requirement.md §8).
     */
    public function run(): void
    {
        $providers = Provider::query()->get();
        $userIds = User::query()->pluck('id');

        for ($i = 0; $i < 15; $i++) {
            $factory = Task::factory();

            if ($i % 3 !== 0) {
                $factory = $factory->forProvider($providers->random());
            }

            $factory->create([
                'assigned_user_id' => fake()->boolean(85) ? $userIds->random() : null,
            ]);
        }
    }
}
