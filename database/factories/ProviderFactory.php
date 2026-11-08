<?php

namespace Database\Factories;

use App\Enums\Gender;
use App\Enums\ProviderStatus;
use App\Models\Practice;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Provider>
 */
class ProviderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $boardCertified = fake()->boolean(60);

        return [
            'first_name' => fake()->firstName(),
            'middle_name' => fake()->optional()->firstName(),
            'last_name' => fake()->lastName(),
            'suffix' => fake()->randomElement([null, null, null, 'Jr.', 'Sr.', 'II', 'III']),
            'date_of_birth' => fake()->dateTimeBetween('-70 years', '-25 years')->format('Y-m-d'),
            'gender' => fake()->randomElement(Gender::cases()),
            'ssn_last4' => fake()->numerify('####'),
            'npi' => fake()->unique()->numerify('##########'),
            'caqh_id' => fake()->optional(0.6)->numerify('##########'),
            'primary_email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('(###) ###-####'),
            'status' => fake()->randomElement([
                ProviderStatus::Active,
                ProviderStatus::Active,
                ProviderStatus::Active,
                ProviderStatus::Pending,
                ProviderStatus::Pending,
                ProviderStatus::Inactive,
            ]),
            'assigned_user_id' => fake()->boolean(80)
                ? User::query()->inRandomOrder()->value('id')
                : null,
            'practice_id' => Practice::factory(),
            'primary_specialty' => fake()->randomElement([
                'Family Medicine',
                'Internal Medicine',
                'Pediatrics',
                'Cardiology',
                'Orthopedics',
                'Dermatology',
                'Psychiatry',
                'Neurology',
                'OB/GYN',
                'General Surgery',
            ]),
            'secondary_specialty' => fake()->optional(0.35)->randomElement([
                'Family Medicine',
                'Internal Medicine',
                'Emergency Medicine',
                'Geriatrics',
            ]),
            'taxonomy_code' => fake()->numerify('#########X'),
            'board_certified' => $boardCertified,
            'board_name' => $boardCertified
                ? fake()->randomElement(['ABMS', 'ABPS', 'ABIM', 'ABPN', 'AOA'])
                : null,
            'certification_date' => $boardCertified
                ? fake()->dateTimeBetween('-10 years', '-1 year')->format('Y-m-d')
                : null,
            'expiry_date' => $boardCertified
                ? fake()->dateTimeBetween('+1 month', '+4 years')->format('Y-m-d')
                : null,
            'attested_at' => null,
            'attestation_due_at' => null,
        ];
    }
}
