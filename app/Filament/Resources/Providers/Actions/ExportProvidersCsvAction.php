<?php

namespace App\Filament\Resources\Providers\Actions;

use App\Filament\Resources\Providers\Csv\ProviderCsv;
use App\Filament\Resources\Providers\Pages\ListProviders;
use App\Models\Provider;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportProvidersCsvAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'exportCsv')
            ->label('Export CSV')
            ->icon(Heroicon::OutlinedArrowDownTray)
            ->color('gray')
            ->tooltip('Export the currently filtered providers')
            ->action(function (ListProviders $livewire): StreamedResponse {
                $query = $livewire->getFilteredTableQuery() ?? Provider::query();

                activity()
                    ->causedBy(auth()->user())
                    ->log('exported providers CSV ('.$query->count().' rows)');

                return ProviderCsv::download($query->get());
            });
    }
}
