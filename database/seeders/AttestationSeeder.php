<?php

namespace Database\Seeders;

use App\Models\Attestation;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AttestationSeeder extends Seeder
{
    /**
     * Seed one attestation per provider with due dates spread so the
     * dashboard shows green, amber and red items (requirement.md §15).
     *
     * Buckets per 7 providers: 2 overdue, 2 due soon, 3 current.
     */
    public function run(): void
    {
        $userIds = User::query()->pluck('id');

        Provider::query()->get()->each(function (Provider $provider, int $index) use ($userIds): void {
            $dueAt = Carbon::instance(match ($index % 7) {
                0, 1 => fake()->dateTimeBetween('-45 days', '-1 day'),
                2, 3 => fake()->dateTimeBetween('+1 day', '+30 days'),
                default => fake()->dateTimeBetween('+31 days', '+120 days'),
            });

            $attestedAt = fake()->boolean(80)
                ? $dueAt->copy()->subDays(120)
                : null;

            Attestation::create([
                'provider_id' => $provider->id,
                'attested_at' => $attestedAt?->format('Y-m-d'),
                'due_at' => $dueAt->format('Y-m-d'),
                'attested_by' => $attestedAt !== null ? $userIds->random() : null,
            ]);

            $provider->update([
                'attested_at' => $attestedAt?->format('Y-m-d'),
                'attestation_due_at' => $dueAt->format('Y-m-d'),
            ]);
        });
    }
}
