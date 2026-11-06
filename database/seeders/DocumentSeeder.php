<?php

namespace Database\Seeders;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Models\Document;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DocumentSeeder extends Seeder
{
    /**
     * Seed ~120 documents across providers with a realistic expiry mix
     * (requirement.md §15): valid, expiring, expired, missing and under review.
     */
    public function run(): void
    {
        $providers = Provider::query()->get();
        $uploadedBy = User::query()->pluck('id');

        $buckets = [
            'valid' => 55,
            'expired' => 25,
            'expiring' => 20,
            'missing' => 15,
            'under_review' => 5,
        ];

        $minimalPdf = "%PDF-1.4\n1 0 obj<</Type/Catalog>>endobj\ntrailer<</Root 1 0 R>>\n%%EOF\n";
        $index = 0;

        foreach ($buckets as $bucket => $count) {
            for ($i = 0; $i < $count; $i++) {
                $provider = $providers[$index % $providers->count()];
                $type = DocumentType::cases()[$index % count(DocumentType::cases())];
                $index++;

                $factory = Document::factory()->state(match ($bucket) {
                    'valid' => [],
                    'expired' => ['expires_at' => fake()->dateTimeBetween('-6 months', '-1 day')->format('Y-m-d')],
                    'expiring' => ['expires_at' => fake()->dateTimeBetween('+1 day', '+59 days')->format('Y-m-d')],
                    'missing' => ['expires_at' => null, 'status' => DocumentStatus::Missing],
                    'under_review' => ['expires_at' => null, 'status' => DocumentStatus::UnderReview],
                });

                $fileName = $type->value.'_'.$provider->last_name.'.pdf';

                $document = $factory->create([
                    'provider_id' => $provider->id,
                    'type' => $type,
                    'file' => 'documents/'.fake()->unique()->slug(4).'.pdf',
                    'file_name' => $fileName,
                    'uploaded_by' => $uploadedBy->random(),
                ]);

                if (! in_array($bucket, ['missing', 'under_review'], true)) {
                    Storage::disk('local')->put($document->file, $minimalPdf);
                }
            }
        }
    }
}
