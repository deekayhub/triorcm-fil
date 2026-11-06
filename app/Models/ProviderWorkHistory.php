<?php

namespace App\Models;

use App\Enums\WorkHistoryType;
use Database\Factories\ProviderWorkHistoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'provider_id',
    'type',
    'institution',
    'from_date',
    'to_date',
    'notes',
])]
class ProviderWorkHistory extends Model
{
    /** @use HasFactory<ProviderWorkHistoryFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => WorkHistoryType::class,
            'from_date' => 'date',
            'to_date' => 'date',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
