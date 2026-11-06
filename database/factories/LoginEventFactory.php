<?php

namespace Database\Factories;

use App\Enums\LoginEventStatus;
use App\Models\LoginEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoginEvent>
 */
class LoginEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $failed = fake()->boolean(30);

        return [
            'user_id' => $failed
                ? fake()->optional(0.5)->randomElement(User::query()->pluck('id')->all())
                : User::query()->inRandomOrder()->value('id'),
            'ip' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'status' => $failed ? LoginEventStatus::Failed : LoginEventStatus::Success,
        ];
    }
}
