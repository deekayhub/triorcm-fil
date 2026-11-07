<?php

namespace App\Filament\Resources\Providers\Actions;

use App\Models\Attestation;
use App\Models\Provider;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class MarkAttestedAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'markAttested')
            ->label('Mark attested')
            ->icon(Heroicon::OutlinedCheckBadge)
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Mark provider as attested')
            ->modalDescription('Sets attested_at to today and schedules the next attestation after the configured cycle (default 120 days).')
            ->visible(fn (Provider $record): bool => auth()->user()?->can('update', $record) ?? false)
            ->action(function (Provider $record): void {
                $cycleDays = (int) Setting::get('attestation.cycle_days', 120);
                $dueAt = today()->addDays($cycleDays);

                $record->update([
                    'attested_at' => today()->toDateString(),
                    'attestation_due_at' => $dueAt->toDateString(),
                ]);

                Attestation::create([
                    'provider_id' => $record->id,
                    'attested_at' => today()->toDateString(),
                    'due_at' => $dueAt->toDateString(),
                    'attested_by' => auth()->id(),
                ]);

                Notification::make()
                    ->title('Provider marked as attested')
                    ->body("Next attestation due {$dueAt->format('M d, Y')}.")
                    ->success()
                    ->send();
            });
    }
}
