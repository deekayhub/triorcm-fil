<?php

namespace App\Filament\Pages;

use BackedEnum;
use UnitEnum;

class Notifications extends ModulePlaceholder
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-bell';

    protected static string|UnitEnum|null $navigationGroup = 'Insights';

    protected static ?string $navigationLabel = 'Notifications';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Notifications';

    protected static ?string $badge = 'Soon';

    protected static string $phaseLabel = 'Planned for Phase 8 of 9';
}
