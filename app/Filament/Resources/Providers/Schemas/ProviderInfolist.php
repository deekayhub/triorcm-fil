<?php

namespace App\Filament\Resources\Providers\Schemas;

use App\Enums\DocumentStatus;
use App\Enums\EnrollmentStage;
use App\Enums\Gender;
use App\Enums\LicenseStatus;
use App\Enums\LicenseType;
use App\Enums\ProviderStatus;
use App\Enums\WorkHistoryType;
use App\Models\Provider;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;

class ProviderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Provider Profile')
                    ->tabs([
                        Tab::make('Basic Info')
                            ->schema(self::basicInfoEntries())
                            ->columns(2),
                        Tab::make('Specialties')
                            ->schema(self::specialtyEntries())
                            ->columns(2),
                        Tab::make('Licenses and IDs')
                            ->schema(fn (Provider $record): array => self::licenseRows($record)),
                        Tab::make('Practice Locations')
                            ->schema(fn (Provider $record): array => self::locationRows($record)),
                        Tab::make('Education and Work History')
                            ->schema(fn (Provider $record): array => self::workHistoryRows($record)),
                        Tab::make('Documents')
                            ->schema(fn (Provider $record): array => self::documentRows($record)),
                        Tab::make('Enrollments')
                            ->schema(fn (Provider $record): array => self::enrollmentRows($record)),
                        Tab::make('Notes and Activity')
                            ->schema(fn (Provider $record): array => self::notesAndActivity($record)),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    /**
     * @return list<Component>
     */
    private static function basicInfoEntries(): array
    {
        return [
            TextEntry::make('first_name'),
            TextEntry::make('last_name')
                ->label('Last name'),
            TextEntry::make('middle_name')
                ->placeholder('—'),
            TextEntry::make('suffix')
                ->placeholder('—'),
            TextEntry::make('date_of_birth')
                ->label('Date of birth')
                ->date('M d, Y'),
            TextEntry::make('gender')
                ->badge()
                ->formatStateUsing(fn (Gender $state): string => Str::headline($state->value))
                ->placeholder('—'),
            TextEntry::make('ssn_last4')
                ->label('SSN (last 4)'),
            TextEntry::make('npi')
                ->label('NPI'),
            TextEntry::make('caqh_id')
                ->label('CAQH ID')
                ->placeholder('—'),
            TextEntry::make('primary_email')
                ->label('Primary email')
                ->copyable(),
            TextEntry::make('phone'),
            TextEntry::make('status')
                ->badge()
                ->color(fn (ProviderStatus $state): string => match ($state) {
                    ProviderStatus::Active => 'success',
                    ProviderStatus::Pending => 'warning',
                    ProviderStatus::Inactive => 'gray',
                }),
            TextEntry::make('assignedUser.name')
                ->label('Assigned specialist')
                ->placeholder('Unassigned'),
            TextEntry::make('practice.name')
                ->label('Practice'),
            TextEntry::make('attested_at')
                ->label('Last attested')
                ->date('M d, Y')
                ->placeholder('Never'),
            TextEntry::make('attestation_due_at')
                ->label('Attestation due')
                ->date('M d, Y')
                ->color(fn (?Provider $record): ?string => match (true) {
                    $record?->attestation_due_at === null => null,
                    $record->attestation_due_at->isPast() => 'danger',
                    $record->attestation_due_at->lte(now()->addDays(30)) => 'warning',
                    default => 'success',
                })
                ->placeholder('Not scheduled'),
        ];
    }

    /**
     * @return list<Component>
     */
    private static function specialtyEntries(): array
    {
        return [
            TextEntry::make('primary_specialty')
                ->label('Primary specialty')
                ->placeholder('—'),
            TextEntry::make('secondary_specialty')
                ->label('Secondary specialty')
                ->placeholder('—'),
            TextEntry::make('taxonomy_code')
                ->placeholder('—'),
            IconEntry::make('board_certified')
                ->label('Board certified')
                ->boolean(),
            TextEntry::make('board_name')
                ->placeholder('—'),
            TextEntry::make('certification_date')
                ->label('Certified on')
                ->date('M d, Y')
                ->placeholder('—'),
            TextEntry::make('expiry_date')
                ->label('Certification expires')
                ->date('M d, Y')
                ->placeholder('—'),
        ];
    }

    /**
     * @return list<Component>
     */
    private static function licenseRows(Provider $record): array
    {
        if ($record->licenses->isEmpty()) {
            return [
                TextEntry::make('licenses_empty')
                    ->state('No licenses on file.'),
            ];
        }

        return [
            Section::make()
                ->schema(
                    $record->licenses
                        ->sortBy(fn ($license) => $license->type->value)
                        ->values()
                        ->map(fn ($license, int $index) => Group::make([
                            TextEntry::make("license_type_{$index}")
                                ->state(Str::headline($license->type->value))
                                ->badge()
                                ->color($license->type === LicenseType::Dea ? 'danger' : 'info'),
                            TextEntry::make("license_number_{$index}")
                                ->label('Number')
                                ->state($license->number),
                            TextEntry::make("license_state_{$index}")
                                ->label('State')
                                ->state($license->state ?? '—'),
                            TextEntry::make("license_issued_{$index}")
                                ->label('Issued')
                                ->state($license->issued_at?->format('M d, Y') ?? '—'),
                            TextEntry::make("license_expires_{$index}")
                                ->label('Expires')
                                ->state($license->expires_at?->format('M d, Y') ?? '—')
                                ->color(match (true) {
                                    $license->expires_at === null => null,
                                    $license->expires_at->isPast() => 'danger',
                                    $license->expires_at->lte(now()->addDays(60)) => 'warning',
                                    default => null,
                                }),
                            TextEntry::make("license_status_{$index}")
                                ->label('Status')
                                ->state(Str::headline($license->status->value))
                                ->badge()
                                ->color(match ($license->status) {
                                    LicenseStatus::Active => 'success',
                                    LicenseStatus::Expired => 'danger',
                                    LicenseStatus::Suspended => 'warning',
                                    LicenseStatus::Pending => 'gray',
                                }),
                        ])->columns(6))
                        ->all(),
                ),
        ];
    }

    /**
     * @return list<Component>
     */
    private static function locationRows(Provider $record): array
    {
        if ($record->locations->isEmpty()) {
            return [
                TextEntry::make('locations_empty')
                    ->state('No locations on file.'),
            ];
        }

        return [
            Section::make()
                ->schema(
                    $record->locations
                        ->values()
                        ->map(fn ($location, int $index) => Group::make([
                            TextEntry::make("location_address_{$index}")
                                ->label('Address')
                                ->state(trim("{$location->address_line1} {$location->address_line2}")),
                            TextEntry::make("location_city_{$index}")
                                ->label('City')
                                ->state("{$location->city}, {$location->state} {$location->zip}"),
                            TextEntry::make("location_phone_{$index}")
                                ->label('Phone')
                                ->state($location->phone ?? '—'),
                            TextEntry::make("location_fax_{$index}")
                                ->label('Fax')
                                ->state($location->fax ?? '—'),
                            TextEntry::make("location_primary_{$index}")
                                ->label('Primary')
                                ->state($location->is_primary ? 'Yes' : 'No')
                                ->badge()
                                ->color($location->is_primary ? 'success' : 'gray'),
                            TextEntry::make("location_accepting_{$index}")
                                ->label('Accepting new patients')
                                ->state($location->accepting_new_patients ? 'Yes' : 'No')
                                ->badge()
                                ->color($location->accepting_new_patients ? 'info' : 'gray'),
                        ])->columns(6))
                        ->all(),
                ),
        ];
    }

    /**
     * @return list<Component>
     */
    private static function workHistoryRows(Provider $record): array
    {
        if ($record->workHistory->isEmpty()) {
            return [
                TextEntry::make('work_history_empty')
                    ->state('No education or work history on file.'),
            ];
        }

        return [
            Section::make()
                ->schema(
                    $record->workHistory
                        ->sortBy('from_date')
                        ->values()
                        ->map(fn ($entry, int $index) => Group::make([
                            TextEntry::make("history_type_{$index}")
                                ->state(Str::headline($entry->type->value))
                                ->badge()
                                ->color(match ($entry->type) {
                                    WorkHistoryType::Education => 'info',
                                    WorkHistoryType::Training => 'warning',
                                    WorkHistoryType::Employment => 'success',
                                }),
                            TextEntry::make("history_institution_{$index}")
                                ->label('Institution')
                                ->state($entry->institution),
                            TextEntry::make("history_dates_{$index}")
                                ->label('Dates')
                                ->state($entry->from_date->format('M Y').' – '.($entry->to_date?->format('M Y') ?? 'Present')),
                            TextEntry::make("history_notes_{$index}")
                                ->label('Notes')
                                ->state($entry->notes ?? '—')
                                ->limit(60)
                                ->tooltip($entry->notes),
                        ])->columns(4))
                        ->all(),
                ),
        ];
    }

    /**
     * @return list<Component>
     */
    private static function documentRows(Provider $record): array
    {
        $documents = $record->documents()->orderBy('type')->get();

        if ($documents->isEmpty()) {
            return [
                TextEntry::make('documents_empty')
                    ->state('No documents on file.'),
            ];
        }

        return [
            Section::make()
                ->schema(
                    $documents->map(fn ($document, int $index) => Group::make([
                        TextEntry::make("document_type_{$index}")
                            ->state(Str::headline($document->type->value))
                            ->badge(),
                        TextEntry::make("document_file_{$index}")
                            ->label('File')
                            ->state($document->file_name ?? $document->file)
                            ->limit(32)
                            ->tooltip($document->file_name ?? $document->file),
                        TextEntry::make("document_expires_{$index}")
                            ->label('Expires')
                            ->state($document->expires_at?->format('M d, Y') ?? '—'),
                        TextEntry::make("document_status_{$index}")
                            ->label('Status')
                            ->state(Str::headline($document->status->value))
                            ->badge()
                            ->color(match ($document->status) {
                                DocumentStatus::Valid => 'success',
                                DocumentStatus::Expiring => 'warning',
                                DocumentStatus::Expired => 'danger',
                                DocumentStatus::Missing => 'danger',
                                DocumentStatus::UnderReview => 'info',
                            }),
                    ])->columns(4))
                        ->all(),
                ),
        ];
    }

    /**
     * @return list<Component>
     */
    private static function enrollmentRows(Provider $record): array
    {
        $enrollments = $record->enrollments()->with('payer')->orderBy('id')->get();

        if ($enrollments->isEmpty()) {
            return [
                TextEntry::make('enrollments_empty')
                    ->state('No enrollments yet.'),
            ];
        }

        return [
            Section::make()
                ->schema(
                    $enrollments->map(fn ($enrollment, int $index) => Group::make([
                        TextEntry::make("enrollment_payer_{$index}")
                            ->label('Payer')
                            ->state($enrollment->payer?->name ?? '—'),
                        TextEntry::make("enrollment_stage_{$index}")
                            ->label('Stage')
                            ->state(Str::headline($enrollment->stage->value))
                            ->badge()
                            ->color(match ($enrollment->stage) {
                                EnrollmentStage::Approved => 'success',
                                EnrollmentStage::Denied => 'danger',
                                EnrollmentStage::Submitted => 'info',
                                EnrollmentStage::Verification => 'warning',
                                EnrollmentStage::Intake => 'gray',
                            }),
                        TextEntry::make("enrollment_submitted_{$index}")
                            ->label('Submitted')
                            ->state($enrollment->submitted_at?->format('M d, Y') ?? '—'),
                        TextEntry::make("enrollment_effective_{$index}")
                            ->label('Effective')
                            ->state($enrollment->effective_date?->format('M d, Y') ?? '—'),
                        TextEntry::make("enrollment_reference_{$index}")
                            ->label('Reference')
                            ->state($enrollment->reference_no ?? '—'),
                    ])->columns(5))
                        ->all(),
                ),
        ];
    }

    /**
     * @return list<Component>
     */
    private static function notesAndActivity(Provider $record): array
    {
        $notes = $record->notes()
            ->with('user')
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn ($note): string => sprintf(
                '%s — %s: %s',
                $note->created_at->format('M d, Y H:i'),
                $note->user?->name ?? 'Unknown',
                Str::limit($note->body, 140),
            ));

        $activity = Activity::query()
            ->where('subject_type', Provider::class)
            ->where('subject_id', $record->id)
            ->with('causer')
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (Activity $entry): string => sprintf(
                '%s — %s: %s',
                $entry->created_at->format('M d, Y H:i'),
                $entry->causer?->name ?? 'System',
                $entry->description,
            ));

        return [
            Section::make('Notes')
                ->schema([
                    TextEntry::make('notes_list')
                        ->state($notes->all())
                        ->listWithLineBreaks()
                        ->placeholder('No notes yet.'),
                ]),

            Section::make('Recent activity')
                ->schema([
                    TextEntry::make('activity_list')
                        ->state($activity->all())
                        ->listWithLineBreaks()
                        ->placeholder('No activity recorded yet.'),
                ]),
        ];
    }
}
