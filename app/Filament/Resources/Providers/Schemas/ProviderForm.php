<?php

namespace App\Filament\Resources\Providers\Schemas;

use App\Enums\Gender;
use App\Enums\LicenseStatus;
use App\Enums\LicenseType;
use App\Enums\ProviderStatus;
use App\Enums\WorkHistoryType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ProviderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Provider')
                    ->tabs([
                        self::basicInfoTab(),
                        self::specialtiesTab(),
                        self::licensesTab(),
                        self::locationsTab(),
                        self::workHistoryTab(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    private static function basicInfoTab(): Tab
    {
        return Tab::make('Basic Info')
            ->schema([
                TextInput::make('first_name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('last_name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('middle_name')
                    ->maxLength(255),
                TextInput::make('suffix')
                    ->maxLength(10),
                DatePicker::make('date_of_birth')
                    ->label('Date of birth')
                    ->helperText('Stored encrypted.')
                    ->maxDate(today())
                    ->required(),
                Select::make('gender')
                    ->options(Gender::class)
                    ->placeholder('Not specified')
                    ->searchable(),
                TextInput::make('ssn_last4')
                    ->label('SSN (last 4)')
                    ->helperText('Stored encrypted.')
                    ->maxLength(4)
                    ->numeric()
                    ->required(),
                TextInput::make('npi')
                    ->label('NPI')
                    ->helperText('10 digits, must be unique.')
                    ->maxLength(10)
                    ->rule('regex:/^\d{10}$/')
                    ->unique(ignoreRecord: true)
                    ->required(),
                TextInput::make('caqh_id')
                    ->label('CAQH ID')
                    ->maxLength(255),
                TextInput::make('primary_email')
                    ->label('Primary email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->tel()
                    ->required()
                    ->maxLength(30),
                Select::make('status')
                    ->options(ProviderStatus::class)
                    ->default(ProviderStatus::Pending->value)
                    ->required(),
                Select::make('practice_id')
                    ->label('Practice')
                    ->relationship('practice', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('assigned_user_id')
                    ->label('Assigned specialist')
                    ->relationship('assignedUser', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Unassigned'),
            ])
            ->columns(2);

    }

    private static function specialtiesTab(): Tab
    {
        $specialties = [
            'Family Medicine',
            'Internal Medicine',
            'Pediatrics',
            'Cardiology',
            'Orthopedics',
            'Dermatology',
            'Psychiatry',
            'Neurology',
            'OB/GYN',
            'General Surgery',
            'Emergency Medicine',
            'Geriatrics',
        ];

        return Tab::make('Specialties')
            ->schema([
                Select::make('primary_specialty')
                    ->label('Primary specialty')
                    ->options(array_combine($specialties, $specialties))
                    ->placeholder('Not set')
                    ->searchable(),
                Select::make('secondary_specialty')
                    ->label('Secondary specialty')
                    ->options(array_combine($specialties, $specialties))
                    ->placeholder('Not set')
                    ->searchable(),
                TextInput::make('taxonomy_code')
                    ->maxLength(20),
                Toggle::make('board_certified')
                    ->label('Board certified'),
                TextInput::make('board_name')
                    ->label('Board name')
                    ->maxLength(255)
                    ->visible(fn (Get $get): bool => (bool) $get('board_certified')),
                DatePicker::make('certification_date')
                    ->maxDate(today())
                    ->visible(fn (Get $get): bool => (bool) $get('board_certified')),
                DatePicker::make('expiry_date')
                    ->label('Certification expires')
                    ->visible(fn (Get $get): bool => (bool) $get('board_certified')),
            ])
            ->columns(2);
    }

    private static function licensesTab(): Tab
    {
        return Tab::make('Licenses and IDs')
            ->schema([
                Repeater::make('licenses')
                    ->relationship()
                    ->label('Licenses')
                    ->columns(3)
                    ->schema([
                        Select::make('type')
                            ->options(LicenseType::class)
                            ->required(),
                        TextInput::make('number')
                            ->helperText('Stored encrypted.')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('state')
                            ->label('State / Region')
                            ->maxLength(2),
                        DatePicker::make('issued_at')
                            ->label('Issued'),
                        DatePicker::make('expires_at')
                            ->label('Expires'),
                        Select::make('status')
                            ->options(LicenseStatus::class)
                            ->default(LicenseStatus::Active->value)
                            ->required(),
                    ]),
            ]);
    }

    private static function locationsTab(): Tab
    {
        return Tab::make('Practice Locations')
            ->schema([
                Repeater::make('locations')
                    ->relationship()
                    ->label('Locations')
                    ->columns(3)
                    ->schema([
                        TextInput::make('address_line1')
                            ->label('Address line 1')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('address_line2')
                            ->label('Address line 2')
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
                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(30),
                        TextInput::make('fax')
                            ->tel()
                            ->maxLength(30),
                        Toggle::make('is_primary')
                            ->label('Primary location'),
                        Toggle::make('accepting_new_patients')
                            ->label('Accepting new patients')
                            ->default(true),
                    ]),
            ]);
    }

    private static function workHistoryTab(): Tab
    {
        return Tab::make('Education and Work History')
            ->schema([
                Repeater::make('workHistory')
                    ->relationship()
                    ->label('History')
                    ->columns(3)
                    ->schema([
                        Select::make('type')
                            ->options(WorkHistoryType::class)
                            ->required(),
                        TextInput::make('institution')
                            ->required()
                            ->maxLength(255),
                        DatePicker::make('from_date')
                            ->label('From')
                            ->required()
                            ->maxDate(today()),
                        DatePicker::make('to_date')
                            ->label('To')
                            ->maxDate(today()),
                        Textarea::make('notes')
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
