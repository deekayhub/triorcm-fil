<?php

namespace App\Filament\Pages;

use BackedEnum;
use UnitEnum;

class Reports extends ModulePlaceholder
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static string|UnitEnum|null $navigationGroup = 'Insights';

    protected static ?string $navigationLabel = 'Reports';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Reports';

    protected static ?string $badge = 'Soon';

    protected static string $phaseLabel = 'Planned for Phase 8 of 9';
}
