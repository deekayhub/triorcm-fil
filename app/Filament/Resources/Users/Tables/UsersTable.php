<?php

namespace App\Filament\Resources\Users\Tables;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->limit(30)
                    ->tooltip(fn (string $state): string => $state),
                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->separator(',')
                    ->color(fn (string $state): string => match ($state) {
                        'Super Admin' => 'danger',
                        'Admin' => 'warning',
                        'Credentialing Manager' => 'info',
                        'Specialist' => 'success',
                        default => 'gray',
                    }),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                IconColumn::make('two_factor_enabled')
                    ->label('2FA')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('last_login_at')
                    ->label('Last login')
                    ->dateTime('M d, Y H:i')
                    ->placeholder('Never')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->label('Role')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
                TernaryFilter::make('is_active')
                    ->label('Active'),
                TernaryFilter::make('two_factor_enabled')
                    ->label('Two-factor'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn (User $record): bool => auth()->id() !== $record->id),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    self::setActiveBulkAction(true),
                    self::setActiveBulkAction(false),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function setActiveBulkAction(bool $isActive): BulkAction
    {
        return BulkAction::make($isActive ? 'activate' : 'deactivate')
            ->label($isActive ? 'Activate' : 'Deactivate')
            ->icon($isActive ? Heroicon::OutlinedCheckCircle : Heroicon::OutlinedXCircle)
            ->color($isActive ? 'success' : 'warning')
            ->visible(fn (): bool => UserResource::canCreate())
            ->requiresConfirmation()
            ->action(function (Collection $records) use ($isActive): void {
                $records->each(fn (User $record): bool => $record->update(['is_active' => $isActive]));

                Notification::make()
                    ->title($isActive ? 'Users activated' : 'Users deactivated')
                    ->body("{$records->count()} user(s) updated.")
                    ->success()
                    ->send();
            });
    }
}
