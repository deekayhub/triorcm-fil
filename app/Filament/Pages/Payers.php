<?php

namespace App\Filament\Pages;

use BackedEnum;
use UnitEnum;

class Payers extends ModulePlaceholder
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-library';

    protected static string|UnitEnum|null $navigationGroup = 'Network';

    protected static ?string $navigationLabel = 'Payers';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Payers';

    protected static ?string $badge = 'Soon';

    protected static string $phaseLabel = 'Planned for Phase 8 of 9';
}
