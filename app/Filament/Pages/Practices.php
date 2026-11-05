<?php

namespace App\Filament\Pages;

use BackedEnum;
use UnitEnum;

class Practices extends ModulePlaceholder
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    protected static string|UnitEnum|null $navigationGroup = 'Provider Management';

    protected static ?string $navigationLabel = 'Practices';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Practices';

    protected static string $statusMessage = 'This module is under construction.';

    protected static string $phaseLabel = 'Ships in Phase 3 of 9';
}
