<?php

namespace App\Filament\Pages;

use BackedEnum;
use UnitEnum;

class Documents extends ModulePlaceholder
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|UnitEnum|null $navigationGroup = 'Credentialing';

    protected static ?string $navigationLabel = 'Documents';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Documents';

    protected static string $statusMessage = 'This module is under construction.';

    protected static string $phaseLabel = 'Ships in Phase 4 of 9';
}
