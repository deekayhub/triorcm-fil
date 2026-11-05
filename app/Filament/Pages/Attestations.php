<?php

namespace App\Filament\Pages;

use BackedEnum;
use UnitEnum;

class Attestations extends ModulePlaceholder
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-check-badge';

    protected static string|UnitEnum|null $navigationGroup = 'Credentialing';

    protected static ?string $navigationLabel = 'Attestations';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Attestations';

    protected static ?string $badge = 'Soon';

    protected static string $phaseLabel = 'Planned for Phase 7 of 9';
}
