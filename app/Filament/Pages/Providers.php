<?php

namespace App\Filament\Pages;

use BackedEnum;
use UnitEnum;

class Providers extends ModulePlaceholder
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static string|UnitEnum|null $navigationGroup = 'Provider Management';

    protected static ?string $navigationLabel = 'Providers';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Providers';

    protected static string $statusMessage = 'This module is under construction.';

    protected static string $phaseLabel = 'Ships in Phase 3 of 9';
}
