<?php

namespace App\Filament\Resources\Documents\Tables;

use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->label('Type')
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('Status'),

                TextColumn::make('provider.name')
                    ->label('Provider')
                    ->sortable(),

                TextColumn::make('issued_at')
                    ->label('Issued At')
                    ->dateTime(),

                TextColumn::make('expires_at')
                    ->label('Expires At')
                    ->dateTime(),

                TextColumn::make('uploaded_by')
                    ->label('Uploaded By')
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'npi_certificate' => 'NPI Certificate',
                        'license_document' => 'License Document',
                        'contract' => 'Contract',
                        'certification' => 'Certification',
                        'identifier' => 'Identifier',
                        'other' => 'Other',
                    ]),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'valid' => 'Valid',
                        'expiring' => 'Expiring (within 60 days)',
                        'expired' => 'Expired',
                        'missing' => 'Missing',
                        'under_review' => 'Under Review',
                    ]),
            ])
            ->emptyStateActions([]);
    }
}
