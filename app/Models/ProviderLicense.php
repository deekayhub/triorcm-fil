<?php

namespace App\Models;

use App\Enums\LicenseStatus;
use App\Enums\LicenseType;
use Database\Factories\ProviderLicenseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'provider_id',
    'type',
    'number',
    'state',
    'issued_at',
    'expires_at',
    'status',
])]
class ProviderLicense extends Model
{
    /** @use HasFactory<ProviderLicenseFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'number' => 'encrypted',
            'type' => LicenseType::class,
            'status' => LicenseStatus::class,
            'issued_at' => 'date',
            'expires_at' => 'date',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
