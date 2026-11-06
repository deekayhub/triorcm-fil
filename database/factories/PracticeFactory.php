<?php

namespace Database\Factories;

use App\Enums\PracticeStatus;
use App\Enums\PracticeType;
use App\Models\Practice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Practice>
 */
class PracticeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->lastName().' '.fake()->randomElement([
                'Family Clinic',
                'Medical Group',
                'Specialty Associates',
                'Health Center',
                'Care Partners',
            ]),
            'tax_id' => fake()->numerify('##-#######'),
            'type' => fake()->randomElement(PracticeType::cases()),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->stateAbbr(),
            'zip' => fake()->postcode(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->companyEmail(),
            'contact_person' => fake()->name(),
            'status' => fake()->randomElement([
                PracticeStatus::Active,
                PracticeStatus::Active,
                PracticeStatus::Active,
                PracticeStatus::Inactive,
            ]),
        ];
    }
}
