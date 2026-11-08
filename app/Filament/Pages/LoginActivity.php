<?php

namespace App\Filament\Pages;

use App\Models\LoginEvent;
use BackedEnum;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class LoginActivity extends \Filament\Pages\Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-key';

    protected static string|UnitEnum|null $navigationGroup = 'Security';

    protected static ?string $navigationLabel = 'Login Activity';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Login Activity';

    protected string $view = 'filament.pages.login-activity';

    public function table(Table $table): Table
    {
        return $table
            ->query(LoginEvent::query()->with('user'))
            ->columns([
                Tables\Columns\TextColumn::make('user.email')->label('User')->searchable(),
                Tables\Columns\TextColumn::make('ip')->label('IP'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => $state->color()),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
