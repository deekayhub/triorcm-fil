<?php

namespace App\Models;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use Database\Factories\DocumentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'provider_id',
    'type',
    'file',
    'file_name',
    'issued_at',
    'expires_at',
    'status',
    'uploaded_by',
    'notes',
])]
class Document extends Model
{
    /** @use HasFactory<DocumentFactory> */
    use HasFactory, LogsActivity;

    /**
     * Days before expiry a document is considered "Expiring".
     * Overridden by settings from Phase 6.
     */
    public static int $expiryWarningDays = 60;

    /**
     * Attributes recorded in the audit log.
     *
     * @var list<string>
     */
    protected array $logAttributes = ['type', 'status', 'expires_at', 'provider_id', 'uploaded_by'];

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
            'type' => DocumentType::class,
            'status' => DocumentStatus::class,
            'issued_at' => 'date',
            'expires_at' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Document $document): void {
            if (in_array($document->status, [DocumentStatus::Missing, DocumentStatus::UnderReview], true)) {
                return;
            }

            $document->status = $document->computeStatusFromExpiry();
        });
    }

    /**
     * Derive the status from the expiry date:
     * Expired when past, Expiring within the warning window, otherwise Valid.
     */
    public function computeStatusFromExpiry(): DocumentStatus
    {
        if (blank($this->expires_at)) {
            return DocumentStatus::Valid;
        }

        if ($this->expires_at->isPast()) {
            return DocumentStatus::Expired;
        }

        if ($this->expires_at->lte(now()->addDays(static::$expiryWarningDays))) {
            return DocumentStatus::Expiring;
        }

        return DocumentStatus::Valid;
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
