<?php

namespace App\Models;

use App\Enums\EnrollmentStage;
use Database\Factories\EnrollmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'provider_id',
    'payer_id',
    'stage',
    'submitted_at',
    'approved_at',
    'effective_date',
    'reference_no',
    'assigned_user_id',
    'notes',
])]
class Enrollment extends Model
{
    /** @use HasFactory<EnrollmentFactory> */
    use HasFactory, LogsActivity;

    /**
     * Attributes recorded in the audit log.
     *
     * @var list<string>
     */
    protected array $logAttributes = ['stage', 'provider_id', 'payer_id', 'assigned_user_id', 'submitted_at', 'approved_at'];

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
            'stage' => EnrollmentStage::class,
            'submitted_at' => 'date',
            'approved_at' => 'date',
            'effective_date' => 'date',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(Payer::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }
}
