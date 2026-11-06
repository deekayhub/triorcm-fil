<?php

namespace Database\Factories;

use App\Enums\WorkHistoryType;
use App\Models\Provider;
use App\Models\ProviderWorkHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProviderWorkHistory>
 */
class ProviderWorkHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(WorkHistoryType::cases());
        $fromDate = fake()->dateTimeBetween('-25 years', '-5 years');
        $toDate = fake()->boolean(70)
            ? fake()->dateTimeBetween($fromDate, 'now')
            : null;

        return [
            'provider_id' => Provider::factory(),
            'type' => $type,
            'institution' => fake()->company().' '.fake()->randomElement([
                'University',
                'Hospital',
                'Medical Center',
                'Residency Program',
                'Clinic',
            ]),
            'from_date' => $fromDate->format('Y-m-d'),
            'to_date' => $toDate?->format('Y-m-d'),
            'notes' => fake()->optional(0.4)->sentence(),
        ];
    }
}
