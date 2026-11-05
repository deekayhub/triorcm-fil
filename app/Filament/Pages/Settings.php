<?php

namespace App\Filament\Pages;

use BackedEnum;
use UnitEnum;

class Settings extends ModulePlaceholder
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Settings';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Settings';

    protected static string $statusMessage = 'This module is under construction.';

    protected static string $phaseLabel = 'Ships in Phase 6 of 9';
}
