<?php

namespace Database\Factories;

use App\Models\Attestation;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Attestation>
 */
class AttestationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $dueAt = Carbon::instance(fake()->dateTimeBetween('-30 days', '+120 days'));
        $attested = fake()->boolean(85);

        return [
            'provider_id' => Provider::factory(),
            'attested_at' => $attested
                ? $dueAt->copy()->subDays(120)->format('Y-m-d')
                : null,
            'due_at' => $dueAt->format('Y-m-d'),
            'attested_by' => $attested
                ? User::query()->inRandomOrder()->value('id')
                : null,
        ];
    }
}
