<?php

namespace App\Filament\Resources\Practices\Schemas;

use App\Enums\PracticeStatus;
use App\Enums\PracticeType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PracticeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Practice Details')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Select::make('type')
                            ->label('Practice type')
                            ->options(PracticeType::class)
                            ->required(),
                        TextInput::make('contact_person')
                            ->label('Contact person')
                            ->maxLength(255),
                        Select::make('status')
                            ->options(PracticeStatus::class)
                            ->default(PracticeStatus::Active->value)
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Address')
                    ->schema([
                        TextInput::make('address')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('city')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('state')
                            ->label('State / Region')
                            ->required()
                            ->maxLength(2),
                        TextInput::make('zip')
                            ->label('ZIP / Postal code')
                            ->required()
                            ->maxLength(10),
                    ])
                    ->columns(2),

                Section::make('Contact')
                    ->schema([
                        TextInput::make('tax_id')
                            ->label('Tax ID')
                            ->helperText('Stored encrypted.')
                            ->required()
                            ->maxLength(20),
                        TextInput::make('phone')
                            ->tel()
                            ->required()
                            ->maxLength(30),
                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2),
            ]);
    }
}
