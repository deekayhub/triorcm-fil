<?php

namespace Database\Seeders;

use App\Enums\EnrollmentStage;
use App\Models\Enrollment;
use App\Models\Payer;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Database\Seeder;

class EnrollmentSeeder extends Seeder
{
    /**
     * Seed ~60 enrollments spread across all stages (requirement.md §15).
     */
    public function run(): void
    {
        $providerIds = Provider::query()->pluck('id');
        $payerIds = Payer::query()->pluck('id');
        $userIds = User::query()->pluck('id');

        $stagePattern = [
            EnrollmentStage::Intake,
            EnrollmentStage::Intake,
            EnrollmentStage::Verification,
            EnrollmentStage::Verification,
            EnrollmentStage::Submitted,
            EnrollmentStage::Submitted,
            EnrollmentStage::Approved,
            EnrollmentStage::Denied,
        ];

        for ($i = 0; $i < 60; $i++) {
            Enrollment::factory()
                ->forStage($stagePattern[$i % count($stagePattern)])
                ->create([
                    'provider_id' => $providerIds[$i % $providerIds->count()],
                    'payer_id' => $payerIds->random(),
                    'assigned_user_id' => fake()->boolean(75) ? $userIds->random() : null,
                ]);
        }
    }
}
