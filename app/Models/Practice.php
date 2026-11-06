<?php

namespace App\Models;

use App\Enums\PracticeStatus;
use App\Enums\PracticeType;
use Database\Factories\PracticeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'name',
    'tax_id',
    'type',
    'address',
    'city',
    'state',
    'zip',
    'phone',
    'email',
    'contact_person',
    'status',
])]
class Practice extends Model
{
    /** @use HasFactory<PracticeFactory> */
    use HasFactory, LogsActivity;

    /**
     * Attributes recorded in the audit log (encrypted fields excluded).
     *
     * @var list<string>
     */
    protected array $logAttributes = ['name', 'type', 'status', 'city', 'state'];

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
            'tax_id' => 'encrypted',
            'type' => PracticeType::class,
            'status' => PracticeStatus::class,
        ];
    }

    public function providers(): HasMany
    {
        return $this->hasMany(Provider::class);
    }
}
