<?php

namespace App\Filament\Pages;

use BackedEnum;
use UnitEnum;

class AuditLogs extends ModulePlaceholder
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static string|UnitEnum|null $navigationGroup = 'Security';

    protected static ?string $navigationLabel = 'Audit Logs';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Audit Logs';

    protected static string $statusMessage = 'This module is under construction.';

    protected static string $phaseLabel = 'Ships in Phase 5 of 9';
}
