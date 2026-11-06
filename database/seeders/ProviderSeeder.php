<?php

namespace Database\Seeders;

use App\Models\Practice;
use App\Models\Provider;
use App\Models\ProviderLicense;
use App\Models\ProviderLocation;
use App\Models\ProviderWorkHistory;
use Illuminate\Database\Seeder;

class ProviderSeeder extends Seeder
{
    /**
     * Seed 35 providers with licenses, locations and work history (requirement.md §15).
     */
    public function run(): void
    {
        $practiceIds = Practice::query()->pluck('id');

        $providers = Provider::factory()->count(35)->create([
            'practice_id' => $practiceIds->random(),
        ]);

        $providers->each(function (Provider $provider): void {
            ProviderLicense::factory()->count(fake()->numberBetween(2, 4))->create([
                'provider_id' => $provider->id,
            ]);

            $locations = ProviderLocation::factory()->count(fake()->numberBetween(1, 2))->create([
                'provider_id' => $provider->id,
            ]);

            $locations->first()->update(['is_primary' => true]);

            ProviderWorkHistory::factory()->count(fake()->numberBetween(2, 4))->create([
                'provider_id' => $provider->id,
            ]);
        });
    }
}
