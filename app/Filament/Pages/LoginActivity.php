<?php

namespace App\Filament\Pages;

use BackedEnum;
use UnitEnum;

class LoginActivity extends ModulePlaceholder
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-key';

    protected static string|UnitEnum|null $navigationGroup = 'Security';

    protected static ?string $navigationLabel = 'Login Activity';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Login Activity';

    protected static string $statusMessage = 'This module is under construction.';

    protected static string $phaseLabel = 'Ships in Phase 5 of 9';
}
