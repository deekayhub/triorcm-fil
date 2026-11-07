<?php

namespace App\Filament\Resources\Practices\Tables;

use App\Enums\PracticeStatus;
use App\Enums\PracticeType;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PracticesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->weight('medium'),
                TextColumn::make('type')
                    ->badge()
                    ->label('Type')
                    ->color(fn (PracticeType $state): string => match ($state) {
                        PracticeType::Solo => 'info',
                        PracticeType::Group => 'success',
                        PracticeType::Hospital => 'warning',
                    }),
                TextColumn::make('city')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('state')
                    ->label('State')
                    ->placeholder('—'),
                TextColumn::make('phone')
                    ->label('Phone')
                    ->toggleable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->limit(24)
                    ->tooltip(fn (string $state): string => $state)
                    ->toggleable(),
                TextColumn::make('providers_count')
                    ->label('Providers')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'gray')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (PracticeStatus $state): string => match ($state) {
                        PracticeStatus::Active => 'success',
                        PracticeStatus::Inactive => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(PracticeType::class),
                SelectFilter::make('status')
                    ->options(PracticeStatus::class),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
