<?php

namespace App\Models;

use Database\Factories\ProviderLocationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'provider_id',
    'address_line1',
    'address_line2',
    'city',
    'state',
    'zip',
    'phone',
    'fax',
    'is_primary',
    'accepting_new_patients',
])]
class ProviderLocation extends Model
{
    /** @use HasFactory<ProviderLocationFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'accepting_new_patients' => 'boolean',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
