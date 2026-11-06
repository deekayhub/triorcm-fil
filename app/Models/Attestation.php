<?php

namespace App\Models;

use App\Enums\AttestationStatus;
use Database\Factories\AttestationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'provider_id',
    'attested_at',
    'due_at',
    'attested_by',
    'status',
])]
class Attestation extends Model
{
    /** @use HasFactory<AttestationFactory> */
    use HasFactory, LogsActivity;

    /**
     * Attributes recorded in the audit log.
     *
     * @var list<string>
     */
    protected array $logAttributes = ['provider_id', 'attested_at', 'due_at', 'status', 'attested_by'];

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
            'attested_at' => 'date',
            'due_at' => 'date',
            'status' => AttestationStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Attestation $attestation): void {
            $attestation->status = $attestation->resolveStatus();
        });
    }

    /**
     * Compute the status from the due date.
     */
    public function resolveStatus(): AttestationStatus
    {
        if (blank($this->due_at)) {
            return AttestationStatus::Current;
        }

        if ($this->due_at->isPast()) {
            return AttestationStatus::Overdue;
        }

        if ($this->due_at->lte(now()->addDays(30))) {
            return AttestationStatus::DueSoon;
        }

        return AttestationStatus::Current;
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function attestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'attested_by');
    }
}
