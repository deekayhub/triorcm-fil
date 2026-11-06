<?php

namespace Database\Seeders;

use App\Models\Provider;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Activitylog\Models\Activity;

class AuditLogSeeder extends Seeder
{
    /**
     * Add causer-attributed audit entries (logins, document access, status
     * changes) and backdate all activity so the dashboard feed spans 60 days
     * (requirement.md §15: 100+ audit log entries).
     */
    public function run(): void
    {
        $users = User::query()->get();
        $providers = Provider::query()->get();

        for ($i = 0; $i < 40; $i++) {
            $user = $users->random();
            $provider = $providers->random();

            match ($i % 4) {
                0 => activity()
                    ->causedBy($user)
                    ->log('logged in'),
                1 => activity()
                    ->causedBy($user)
                    ->performedOn($provider)
                    ->withProperties(['ip' => fake()->ipv4()])
                    ->log('downloaded a provider document'),
                2 => activity()
                    ->causedBy($user)
                    ->performedOn($provider)
                    ->log('updated provider status'),
                default => activity()
                    ->causedBy($user)
                    ->performedOn($provider)
                    ->log('marked provider as attested'),
            };
        }

        Activity::query()
            ->get()
            ->each(function (Activity $activity): void {
                $activity->created_at = fake()->dateTimeBetween('-60 days');
                $activity->save();
            });
    }
}
