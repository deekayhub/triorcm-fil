<?php

namespace App\Filament\Resources\Payers\Pages;

use App\Filament\Resources\Payers\PayerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPayer extends EditRecord
{
    protected static string $resource = PayerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
