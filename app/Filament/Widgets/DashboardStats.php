<?php

namespace App\Filament\Widgets;

use App\Models\LoginEvent;
use App\Models\Provider;
use App\Models\Document;
use App\Models\Enrollment;
use App\Models\User;
use App\Models\Payer;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Providers', Provider::count()),
            Stat::make('Total Users', User::count()),
            Stat::make('Documents', Document::count()),
            Stat::make('Enrollments', Enrollment::count()),
            Stat::make('Payers', Payer::count()),
            Stat::make('Login Events (7d)', LoginEvent::where('created_at', '>=', now()->subDays(7))->count()),
        ];
    }
}
