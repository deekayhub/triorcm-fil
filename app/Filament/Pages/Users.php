<?php

namespace App\Filament\Pages;

use BackedEnum;
use UnitEnum;

class Users extends ModulePlaceholder
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static string|UnitEnum|null $navigationGroup = 'Security';

    protected static ?string $navigationLabel = 'Users & Roles';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Users & Roles';

    protected static string $statusMessage = 'This module is under construction.';

    protected static string $phaseLabel = 'Ships in Phase 5 of 9';
}
