<?php

namespace Database\Factories;

use App\Enums\PayerType;
use App\Models\Payer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payer>
 */
class PayerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Aetna',
                'Blue Cross Blue Shield',
                'Cigna',
                'UnitedHealthcare',
                'Humana',
                'Kaiser Permanente',
                'Centene',
                'Molina Healthcare',
                'Anthem',
                'Highmark',
                'Tufts Health Plan',
                'Priority Health',
                'CareFirst',
                'Independence Blue Cross',
                'BCBS Michigan',
                'Oscar Health',
            ]),
            'type' => fake()->randomElement(PayerType::cases()),
            'portal_url' => 'https://portal.'.fake()->domainName(),
            'contact_email' => fake()->companyEmail(),
            'contact_phone' => fake()->phoneNumber(),
            'notes' => fake()->optional(0.5)->sentence(),
            'requirements' => [
                ['label' => 'W-9', 'required' => true],
                ['label' => 'CAQH ProView', 'required' => true],
                ['label' => 'Malpractice Insurance', 'required' => true],
                ['label' => 'State License', 'required' => true],
                ['label' => 'DEA Certificate', 'required' => false],
            ],
        ];
    }
}
