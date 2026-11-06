<?php

namespace Database\Factories;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Models\Document;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fileName = fake()->slug(3).'.pdf';

        return [
            'provider_id' => Provider::factory(),
            'type' => fake()->randomElement(DocumentType::cases()),
            'file' => 'documents/'.$fileName,
            'file_name' => $fileName,
            'issued_at' => fake()->dateTimeBetween('-3 years', '-30 days')->format('Y-m-d'),
            'expires_at' => fake()->dateTimeBetween('-6 months', '+4 years')->format('Y-m-d'),
            'uploaded_by' => User::query()->inRandomOrder()->value('id'),
            'notes' => fake()->optional(0.3)->sentence(),
        ];
    }

    /**
     * The document expired in the past.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes): array => [
            'expires_at' => fake()->dateTimeBetween('-6 months', '-1 day')->format('Y-m-d'),
        ]);
    }

    /**
     * The document expires within the warning window.
     */
    public function expiring(): static
    {
        return $this->state(fn (array $attributes): array => [
            'expires_at' => fake()->dateTimeBetween('+1 day', '+59 days')->format('Y-m-d'),
        ]);
    }

    /**
     * The document is valid well beyond the warning window.
     */
    public function valid(): static
    {
        return $this->state(fn (array $attributes): array => [
            'expires_at' => fake()->dateTimeBetween('+61 days', '+4 years')->format('Y-m-d'),
        ]);
    }

    /**
     * The document type has not been provided yet.
     */
    public function missing(): static
    {
        return $this->state(fn (array $attributes): array => [
            'expires_at' => null,
            'status' => DocumentStatus::Missing,
        ]);
    }
}
