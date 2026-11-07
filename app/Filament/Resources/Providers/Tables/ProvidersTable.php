<?php

namespace App\Filament\Resources\Providers\Tables;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Enums\ProviderStatus;
use App\Filament\Resources\Providers\Actions\MarkAttestedAction;
use App\Filament\Resources\Providers\ProviderResource;
use App\Models\Document;
use App\Models\Provider;
use App\Models\ProviderLicense;
use App\Models\Setting;
use App\Models\User;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProvidersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label('Name')
                    ->weight('medium')
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query
                        ->where(fn (Builder $inner) => $inner
                            ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")))
                    ->url(fn (Provider $record): string => ProviderResource::getUrl('view', ['record' => $record]))
                    ->tooltip(fn (Provider $record): string => $record->full_name),
                TextColumn::make('npi')
                    ->label('NPI'),
                TextColumn::make('primary_specialty')
                    ->label('Specialty')
                    ->placeholder('—')
                    ->limit(18)
                    ->tooltip(fn (?string $state): ?string => $state),
                TextColumn::make('practice.name')
                    ->label('Practice')
                    ->placeholder('—')
                    ->limit(20)
                    ->tooltip(fn (?string $state): ?string => $state),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (ProviderStatus $state): string => match ($state) {
                        ProviderStatus::Active => 'success',
                        ProviderStatus::Pending => 'warning',
                        ProviderStatus::Inactive => 'gray',
                    }),
                TextColumn::make('attestation_due_at')
                    ->label('Attestation due')
                    ->date('M d, Y')
                    ->placeholder('Not set')
                    ->color(fn (Provider $record): ?string => match (true) {
                        $record->attestation_due_at === null => null,
                        $record->attestation_due_at->isPast() => 'danger',
                        $record->attestation_due_at->lte(now()->addDays(30)) => 'warning',
                        default => 'success',
                    }),
                TextColumn::make('documents_complete')
                    ->label('Docs complete')
                    ->badge()
                    ->suffix('%')
                    ->getStateUsing(fn (Provider $record): int => self::documentsCompletePercent($record))
                    ->color(fn (int $state): string => match (true) {
                        $state >= 80 => 'success',
                        $state >= 50 => 'warning',
                        default => 'danger',
                    }),
                TextColumn::make('assignedUser.name')
                    ->label('Specialist')
                    ->placeholder('Unassigned')
                    ->limit(16)
                    ->tooltip(fn (?string $state): ?string => $state),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(ProviderStatus::class),
                SelectFilter::make('primary_specialty')
                    ->label('Specialty')
                    ->options(fn (): array => Provider::query()
                        ->whereNotNull('primary_specialty')
                        ->distinct()
                        ->orderBy('primary_specialty')
                        ->pluck('primary_specialty', 'primary_specialty')
                        ->all()),
                SelectFilter::make('practice_id')
                    ->label('Practice')
                    ->relationship('practice', 'name')
                    ->preload()
                    ->searchable(),
                SelectFilter::make('attestation_state')
                    ->label('Attestation')
                    ->options([
                        'current' => 'Current (> 30 days)',
                        'due_soon' => 'Due soon (≤ 30 days)',
                        'overdue' => 'Overdue',
                    ])
                    ->query(fn (Builder $query, array $state): Builder => match ($state['value'] ?? null) {
                        'overdue' => $query
                            ->whereNotNull('attestation_due_at')
                            ->whereDate('attestation_due_at', '<', today()->toDateString()),
                        'due_soon' => $query
                            ->whereDate('attestation_due_at', '>=', today()->toDateString())
                            ->whereDate('attestation_due_at', '<=', today()->addDays(30)->toDateString()),
                        'current' => $query
                            ->whereDate('attestation_due_at', '>', today()->addDays(30)->toDateString()),
                        default => $query,
                    }),
                SelectFilter::make('assigned_user_id')
                    ->label('Assigned specialist')
                    ->relationship('assignedUser', 'name')
                    ->preload()
                    ->searchable(),
                SelectFilter::make('license_state')
                    ->label('License state')
                    ->options(fn (): array => ProviderLicense::query()
                        ->whereNotNull('state')
                        ->distinct()
                        ->orderBy('state')
                        ->pluck('state', 'state')
                        ->all())
                    ->query(fn (Builder $query, array $state): Builder => filled($state['value'] ?? null)
                        ? $query->whereHas('licenses', fn (Builder $inner) => $inner->where('state', $state['value']))
                        : $query),
                SelectFilter::make('trashed')
                    ->label('Deleted')
                    ->options([
                        'with' => 'Include deleted',
                        'only' => 'Only deleted',
                    ])
                    ->query(fn (Builder $query, array $state): Builder => match ($state['value'] ?? null) {
                        'with' => $query->withoutGlobalScope(SoftDeletingScope::class),
                        'only' => $query->onlyTrashed(),
                        default => $query,
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                MarkAttestedAction::make(),
                DeleteAction::make()
                    ->visible(fn (Provider $record): bool => ! $record->trashed()),
                RestoreAction::make()
                    ->visible(fn (Provider $record): bool => $record->trashed()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    self::assignSpecialistBulkAction(),
                    self::changeStatusBulkAction(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Percentage of required document types the provider has on file.
     */
    private static function documentsCompletePercent(Provider $record): int
    {
        $required = Setting::get('documents.required_types')
            ?? array_column(DocumentType::cases(), 'value');

        $present = $record->documents
            ->filter(fn (Document $document): bool => $document->status !== DocumentStatus::Missing
                && in_array($document->type->value, $required, true))
            ->pluck('type')
            ->unique()
            ->count();

        return (int) round($present / max(count($required), 1) * 100);
    }

    private static function assignSpecialistBulkAction(): BulkAction
    {
        return BulkAction::make('assignSpecialist')
            ->label('Assign specialist')
            ->icon(Heroicon::OutlinedUser)
            ->form([
                Select::make('assigned_user_id')
                    ->label('Specialist')
                    ->options(fn (): array => User::query()
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->preload()
                    ->required(),
            ])
            ->action(function (Collection $records, array $data): void {
                $records->each(fn (Provider $record) => $record->update([
                    'assigned_user_id' => $data['assigned_user_id'],
                ]));

                Notification::make()
                    ->title('Specialist assigned')
                    ->body("{$records->count()} provider(s) updated.")
                    ->success()
                    ->send();
            });
    }

    private static function changeStatusBulkAction(): BulkAction
    {
        return BulkAction::make('changeStatus')
            ->label('Change status')
            ->icon(Heroicon::OutlinedClipboardDocumentCheck)
            ->form([
                Select::make('status')
                    ->options(ProviderStatus::class)
                    ->required(),
            ])
            ->action(function (Collection $records, array $data): void {
                $records->each(fn (Provider $record) => $record->update([
                    'status' => $data['status'],
                ]));

                Notification::make()
                    ->title('Status changed')
                    ->body("{$records->count()} provider(s) updated.")
                    ->success()
                    ->send();
            });
    }
}
