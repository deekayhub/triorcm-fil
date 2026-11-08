<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User Details')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->minLength(8)
                            ->maxLength(255)
                            ->helperText(fn (string $operation): ?string => $operation === 'edit'
                                ? 'Leave blank to keep the current password.'
                                : null),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->helperText('Inactive users cannot sign in to the admin panel.')
                            ->default(true),
                    ])
                    ->columns(2),

                Section::make('Roles & Security')
                    ->schema([
                        Select::make('roles')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->required()
                            ->helperText('Assign at least one role to control what this user can access.'),
                        Toggle::make('two_factor_enabled')
                            ->label('Two-factor authentication')
                            ->helperText('Recommended for Admin roles (requirement.md §12.1).')
                            ->default(false),
                        DateTimePicker::make('last_login_at')
                            ->label('Last login')
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn (string $operation): bool => $operation === 'edit'),
                    ])
                    ->columns(2),
            ]);
    }
}
