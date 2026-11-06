<?php

namespace Database\Factories;

use App\Enums\EnrollmentStage;
use App\Models\Enrollment;
use App\Models\Payer;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Enrollment>
 */
class EnrollmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $stage = fake()->randomElement([
            EnrollmentStage::Intake,
            EnrollmentStage::Intake,
            EnrollmentStage::Verification,
            EnrollmentStage::Verification,
            EnrollmentStage::Submitted,
            EnrollmentStage::Submitted,
            EnrollmentStage::Approved,
            EnrollmentStage::Denied,
        ]);

        return array_merge($this->stageAttributes($stage), [
            'provider_id' => Provider::factory(),
            'payer_id' => Payer::factory(),
            'assigned_user_id' => fake()->boolean(75)
                ? User::query()->inRandomOrder()->value('id')
                : null,
            'notes' => fake()->optional(0.4)->sentence(),
        ]);
    }

    /**
     * Pin the enrollment to a stage with consistent lifecycle dates.
     */
    public function forStage(EnrollmentStage $stage): static
    {
        return $this->state(fn (array $attributes): array => $this->stageAttributes($stage));
    }

    /**
     * Stage plus the dates that stage implies.
     *
     * @return array<string, mixed>
     */
    private function stageAttributes(EnrollmentStage $stage): array
    {
        $submittedAt = in_array($stage, [EnrollmentStage::Submitted, EnrollmentStage::Approved, EnrollmentStage::Denied], true)
            ? fake()->dateTimeBetween('-90 days', '-3 days')
            : null;

        $approvedAt = $stage === EnrollmentStage::Approved && $submittedAt !== null
            ? fake()->dateTimeBetween($submittedAt, '-1 day')
            : null;

        return [
            'stage' => $stage,
            'submitted_at' => $submittedAt?->format('Y-m-d'),
            'approved_at' => $approvedAt?->format('Y-m-d'),
            'effective_date' => $approvedAt !== null
                ? fake()->dateTimeBetween($approvedAt, '+60 days')->format('Y-m-d')
                : null,
            'reference_no' => $submittedAt !== null
                ? fake()->numerify('REF########')
                : null,
        ];
    }
}
