<?php

namespace App\Models;

use App\Enums\PayerType;
use Database\Factories\PayerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'name',
    'type',
    'portal_url',
    'contact_email',
    'contact_phone',
    'notes',
    'requirements',
])]
class Payer extends Model
{
    /** @use HasFactory<PayerFactory> */
    use HasFactory, LogsActivity;

    /**
     * Attributes recorded in the audit log.
     *
     * @var list<string>
     */
    protected array $logAttributes = ['name', 'type'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->logAttributes)
            ->logOnlyDirty();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => PayerType::class,
            'requirements' => 'array',
        ];
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }
}
