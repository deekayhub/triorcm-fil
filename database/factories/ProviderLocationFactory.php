<?php

namespace Database\Factories;

use App\Models\Provider;
use App\Models\ProviderLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProviderLocation>
 */
class ProviderLocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'provider_id' => Provider::factory(),
            'address_line1' => fake()->streetAddress(),
            'address_line2' => fake()->optional()->secondaryAddress(),
            'city' => fake()->city(),
            'state' => fake()->stateAbbr(),
            'zip' => fake()->postcode(),
            'phone' => fake()->numerify('(###) ###-####'),
            'fax' => fake()->optional(0.6)->numerify('(###) ###-####'),
            'is_primary' => false,
            'accepting_new_patients' => fake()->boolean(75),
        ];
    }
}
