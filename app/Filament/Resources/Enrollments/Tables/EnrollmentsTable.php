<?php

namespace App\Filament\Resources\Enrollments\Tables;

use App\Enums\EnrollmentStage;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EnrollmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('provider.name')
                    ->label('Provider')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->placeholder('—')
                    ->limit(22)
                    ->tooltip(fn (?string $state): ?string => $state),
                TextColumn::make('payer.name')
                    ->label('Payer')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—')
                    ->limit(22)
                    ->tooltip(fn (?string $state): ?string => $state),
                TextColumn::make('stage')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->color(fn (EnrollmentStage $state): string => match ($state) {
                        EnrollmentStage::Intake => 'gray',
                        EnrollmentStage::Verification => 'warning',
                        EnrollmentStage::Submitted => 'info',
                        EnrollmentStage::Approved => 'success',
                        EnrollmentStage::Denied => 'danger',
                    }),
                TextColumn::make('reference_no')
                    ->label('Reference no')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—')
                    ->limit(16)
                    ->tooltip(fn (?string $state): ?string => $state),
                TextColumn::make('submitted_at')
                    ->label('Submitted')
                    ->date('M d, Y')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('approved_at')
                    ->label('Approved')
                    ->date('M d, Y')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('effective_date')
                    ->label('Effective')
                    ->date('M d, Y')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('assignedUser.name')
                    ->label('Assigned to')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Unassigned')
                    ->limit(16)
                    ->tooltip(fn (?string $state): ?string => $state),
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
