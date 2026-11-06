<?php

namespace Database\Factories;

use App\Enums\LicenseStatus;
use App\Enums\LicenseType;
use App\Models\Provider;
use App\Models\ProviderLicense;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<ProviderLicense>
 */
class ProviderLicenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $expiresAt = Carbon::instance(fake()->dateTimeBetween('-1 year', '+3 years'));

        return [
            'provider_id' => Provider::factory(),
            'type' => fake()->randomElement(LicenseType::cases()),
            'number' => fake()->bothify('??########'),
            'state' => fake()->stateAbbr(),
            'issued_at' => fake()->dateTimeBetween('-8 years', '-1 year')->format('Y-m-d'),
            'expires_at' => $expiresAt->format('Y-m-d'),
            'status' => $expiresAt->isPast()
                ? LicenseStatus::Expired
                : fake()->randomElement([LicenseStatus::Active, LicenseStatus::Active, LicenseStatus::Active, LicenseStatus::Suspended]),
        ];
    }
}
