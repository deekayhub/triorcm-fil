<?php

namespace App\Filament\Resources\Documents\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Document Details')
                    ->schema([
                        Select::make('type')
                            ->label('Type')
                            ->options([
                                'npi_certificate' => 'NPI Certificate',
                                'license_document' => 'License Document',
                                'contract' => 'Contract',
                                'certification' => 'Certification',
                                'identifier' => 'Identifier',
                                'other' => 'Other',
                            ])
                            ->required(),

                        TextInput::make('issued_at')
                            ->label('Issued At')
                            ->type('date'),

                        TextInput::make('expires_at')
                            ->label('Expires At')
                            ->type('date'),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'valid' => 'Valid',
                                'expiring' => 'Expiring (within 60 days)',
                                'expired' => 'Expired',
                                'missing' => 'Missing',
                                'under_review' => 'Under Review',
                            ])
                            ->required(),

                        TextInput::make('uploaded_by')
                            ->label('Uploaded By')
                            ->maxLength(255),

                        TextInput::make('notes')
                            ->label('Notes')
                            ->rows(3),
                    ]),

                Section::make('File')
                    ->schema([
                        Select::make('file')
                            ->label('File')
                            ->disk('private')
                            ->directory('documents'),
                    ]),
            ]);
    }
}
