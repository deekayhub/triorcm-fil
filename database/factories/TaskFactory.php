<?php

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Provider;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->randomElement([
                'Follow up on missing W-9',
                'Chase malpractice insurance renewal',
                'Verify state license expiration',
                'Confirm CAQH attestation',
                'Upload board certification',
                'Re-verify NPI record',
                'Collect signed enrollment forms',
            ]),
            'related_type' => null,
            'related_id' => null,
            'assigned_user_id' => fake()->boolean(80)
                ? User::query()->inRandomOrder()->value('id')
                : null,
            'due_at' => fake()->dateTimeBetween('-14 days', '+30 days')->format('Y-m-d'),
            'priority' => fake()->randomElement([
                TaskPriority::Low,
                TaskPriority::Medium,
                TaskPriority::Medium,
                TaskPriority::High,
                TaskPriority::Urgent,
            ]),
            'status' => fake()->randomElement([
                TaskStatus::Open,
                TaskStatus::Open,
                TaskStatus::Open,
                TaskStatus::InProgress,
                TaskStatus::InProgress,
                TaskStatus::Done,
                TaskStatus::Done,
                TaskStatus::Cancelled,
            ]),
        ];
    }

    /**
     * Relate the task to the given provider.
     */
    public function forProvider(Provider $provider): static
    {
        return $this->state(fn (array $attributes): array => [
            'related_type' => $provider::class,
            'related_id' => $provider->id,
        ]);
    }
}
