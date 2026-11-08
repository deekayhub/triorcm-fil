<?php

namespace App\Filament\Resources\Payers;

use App\Filament\Resources\Payers\Pages\CreatePayer;
use App\Filament\Resources\Payers\Pages\EditPayer;
use App\Filament\Resources\Payers\Pages\ListPayers;
use App\Filament\Resources\Payers\Schemas\PayerForm;
use App\Filament\Resources\Payers\Tables\PayersTable;
use App\Models\Payer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PayerResource extends Resource
{
    protected static string|\UnitEnum|null $navigationGroup = 'Network';

    protected static ?string $model = Payer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return PayerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PayersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayers::route('/'),
            'create' => CreatePayer::route('/create'),
            'edit' => EditPayer::route('/{record}/edit'),
        ];
    }
}
