<?php

namespace Database\Seeders;

use App\Models\LoginEvent;
use Illuminate\Database\Seeder;

class LoginEventSeeder extends Seeder
{
    /**
     * Seed 30 login events over the last 7 days (requirement.md §12.3).
     */
    public function run(): void
    {
        LoginEvent::factory()
            ->count(30)
            ->create()
            ->each(function (LoginEvent $event): void {
                $event->created_at = fake()->dateTimeBetween('-7 days');
                $event->save();
            });
    }
}
