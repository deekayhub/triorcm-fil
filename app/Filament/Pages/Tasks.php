<?php

namespace App\Filament\Pages;

use BackedEnum;
use UnitEnum;

class Tasks extends ModulePlaceholder
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-list-bullet';

    protected static string|UnitEnum|null $navigationGroup = 'Credentialing';

    protected static ?string $navigationLabel = 'Tasks';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'Tasks';

    protected static ?string $badge = 'Soon';

    protected static string $phaseLabel = 'Planned for Phase 7 of 9';
}
