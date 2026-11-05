<?php

namespace App\Filament\Pages;

use BackedEnum;
use UnitEnum;

class Enrollments extends ModulePlaceholder
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string|UnitEnum|null $navigationGroup = 'Credentialing';

    protected static ?string $navigationLabel = 'Enrollments';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Enrollments';

    protected static ?string $badge = 'Soon';

    protected static string $phaseLabel = 'Planned for Phase 7 of 9';
}
