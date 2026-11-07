<?php

namespace App\Models;

use App\Enums\Gender;
use App\Enums\ProviderStatus;
use Database\Factories\ProviderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'first_name',
    'middle_name',
    'last_name',
    'suffix',
    'date_of_birth',
    'gender',
    'ssn_last4',
    'npi',
    'caqh_id',
    'primary_email',
    'phone',
    'status',
    'assigned_user_id',
    'practice_id',
    'primary_specialty',
    'secondary_specialty',
    'taxonomy_code',
    'board_certified',
    'board_name',
    'certification_date',
    'expiry_date',
    'attested_at',
    'attestation_due_at',
])]
class Provider extends Model
{
    /** @use HasFactory<ProviderFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    /**
     * Attributes recorded in the audit log (encrypted fields excluded).
     *
     * @var list<string>
     */
    protected array $logAttributes = [
        'first_name',
        'last_name',
        'npi',
        'status',
        'practice_id',
        'assigned_user_id',
        'primary_specialty',
        'attested_at',
        'attestation_due_at',
    ];

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
            'date_of_birth' => 'encrypted',
            'ssn_last4' => 'encrypted',
            'gender' => Gender::class,
            'status' => ProviderStatus::class,
            'board_certified' => 'boolean',
            'certification_date' => 'date',
            'expiry_date' => 'date',
            'attested_at' => 'date',
            'attestation_due_at' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Provider $provider): void {
            $provider->validateNpi();
        });
    }

    /**
     * Model-level validation rules, reusable by Filament forms.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'npi' => [
                'required',
                'string',
                'regex:/^\d{10}$/',
                Rule::unique(Provider::class, 'npi')->ignore($this->id),
            ],
        ];
    }

    public function practice(): BelongsTo
    {
        return $this->belongsTo(Practice::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(ProviderLicense::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(ProviderLocation::class);
    }

    public function workHistory(): HasMany
    {
        return $this->hasMany(ProviderWorkHistory::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function attestations(): HasMany
    {
        return $this->hasMany(Attestation::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'related');
    }

    private function validateNpi(): void
    {
        Validator::make(
            ['npi' => $this->npi],
            $this->rules(),
            ['npi.regex' => 'The NPI must be exactly 10 digits.'],
        )->validate();
    }

    /**
     * Provider's full name, including middle name and suffix when present.
     */
    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
            $this->suffix,
        ])));
    }
}
