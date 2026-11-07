<?php

namespace App\Filament\Resources\Providers\Pages;

use App\Filament\Resources\Providers\Actions\MarkAttestedAction;
use App\Filament\Resources\Providers\ProviderResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\ViewRecord;

class ViewProvider extends ViewRecord
{
    protected static string $resource = ProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            MarkAttestedAction::make(),
            DeleteAction::make()
                ->visible(fn (): bool => ! $this->getRecord()->trashed()),
            RestoreAction::make()
                ->visible(fn (): bool => $this->getRecord()->trashed()),
        ];
    }
}
