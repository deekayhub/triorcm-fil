<?php

namespace App\Filament\Resources\Payers\Tables;

use App\Enums\PayerType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PayersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('type')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->color(fn (PayerType $state): string => match ($state) {
                        PayerType::Commercial => 'success',
                        PayerType::Medicare => 'info',
                        PayerType::Medicaid => 'warning',
                        PayerType::Marketplace => 'gray',
                        PayerType::Other => 'gray',
                    }),
                TextColumn::make('contact_email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->limit(30)
                    ->tooltip(fn (string $state): string => $state),
                TextColumn::make('contact_phone')
                    ->label('Phone')
                    ->searchable()
                    ->copyable()
                    ->limit(16)
                    ->placeholder('—')
                    ->tooltip(fn (?string $state): ?string => $state),
                TextColumn::make('portal_url')
                    ->label('Portal')
                    ->searchable()
                    ->url(fn (?string $state): ?string => $state)
                    ->openUrlInNewTab()
                    ->placeholder('—')
                    ->limit(22)
                    ->tooltip(fn (?string $state): ?string => $state),
                TextColumn::make('enrollments_count')
                    ->label('Enrollments')
                    ->counts('enrollments')
                    ->sortable()
                    ->badge()
                    ->alignCenter(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
