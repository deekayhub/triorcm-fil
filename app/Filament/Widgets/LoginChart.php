<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class LoginChart extends ChartWidget
{
    protected ?string $heading = 'Logins (last 7 days)';

    protected function getData(): array
    {
        $days = collect(range(6, 0))->map(fn ($d) => now()->subDays($d)->format('M d'));

        return [
            'datasets' => [
                [
                    'label' => 'Events',
                    'data' => $days->map(fn () => rand(1, 10))->toArray(),
                ],
            ],
            'labels' => $days->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
