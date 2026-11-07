<?php

namespace App\Filament\Resources\Providers\Actions;

use App\Filament\Resources\Providers\Csv\ProviderCsv;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;

class ImportProvidersCsvAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'importCsv')
            ->label('Import CSV')
            ->icon(Heroicon::OutlinedArrowUpTray)
            ->color('gray')
            ->form([
                FileUpload::make('file')
                    ->label('CSV file')
                    ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
                    ->disk('local')
                    ->directory('imports')
                    ->helperText('Required columns: '.implode(', ', ProviderCsv::REQUIRED_COLUMNS).'. Optional: '.implode(', ', ProviderCsv::OPTIONAL_COLUMNS).'.')
                    ->required(),
            ])
            ->action(function (array $data): void {
                $path = Storage::disk('local')->path($data['file']);

                $result = ProviderCsv::import($path);

                Storage::disk('local')->delete($data['file']);

                $notification = $result['errors'] === []
                    ? Notification::make()->title('Import complete')
                    : Notification::make()->title('Import finished with errors')->warning();

                $notification
                    ->body(sprintf(
                        '%d provider(s) created. %d row(s) skipped.',
                        $result['created'],
                        count($result['errors']),
                    ))
                    ->send();

                if ($result['errors'] !== []) {
                    Notification::make()
                        ->title('Skipped rows')
                        ->body(implode("\n", array_slice($result['errors'], 0, 10))
                            .(count($result['errors']) > 10 ? "\n…" : ''))
                        ->warning()
                        ->send();
                }
            });
    }
}
