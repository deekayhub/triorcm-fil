<?php

namespace App\Filament\Pages;

use BackedEnum;
use UnitEnum;

class Notifications extends \Filament\Pages\Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-bell';

    protected static string|UnitEnum|null $navigationGroup = 'Insights';

    protected static ?string $navigationLabel = 'Notifications';

    protected static ?int $navigationSort = 10;

    protected static ?string $title = 'Notifications';

    protected string $view = 'filament.pages.notifications';

    public function getNotifications()
    {
        return auth()->user()?->notifications()->latest()->limit(50)->get() ?? collect();
    }
}
