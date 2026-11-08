<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use UnitEnum;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Settings';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Settings';

    protected string $view = 'filament.pages.settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'general' => Setting::get('general', []),
            'attestation' => Setting::get('attestation', ['cycle' => 120, 'reminders' => [30, 14, 7]]),
            'documents' => Setting::get('documents', ['expiry_window' => 60]),
            'notifications' => Setting::get('notifications', ['sender' => 'noreply@triorcm.test']),
            'security' => Setting::get('security', ['session_timeout' => 30, 'enforce_2fa' => false]),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Tabs::make('Settings')
                    ->tabs([
                        \Filament\Schemas\Components\Tabs\Tab::make('General')
                            ->schema([
                                Forms\Components\TextInput::make('general.company_name')->label('Company name'),
                                Forms\Components\TextInput::make('general.support_email')->label('Support email'),
                                Forms\Components\TextInput::make('general.timezone')->label('Timezone'),
                            ]),
                        \Filament\Schemas\Components\Tabs\Tab::make('Attestation')
                            ->schema([
                                Forms\Components\TextInput::make('attestation.cycle')->numeric()->label('Cycle (days)'),
                            ]),
                        \Filament\Schemas\Components\Tabs\Tab::make('Documents')
                            ->schema([
                                Forms\Components\TextInput::make('documents.expiry_window')->numeric()->label('Expiry warning window (days)'),
                            ]),
                        \Filament\Schemas\Components\Tabs\Tab::make('Notifications')
                            ->schema([
                                Forms\Components\TextInput::make('notifications.sender')->label('Email sender'),
                            ]),
                        \Filament\Schemas\Components\Tabs\Tab::make('Security')
                            ->schema([
                                Forms\Components\TextInput::make('security.session_timeout')->numeric()->label('Session timeout (min)'),
                                Forms\Components\Toggle::make('security.enforce_2fa')->label('Enforce 2FA'),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();
        foreach ($state as $key => $value) {
            Setting::set($key, $value);
        }
    }
}
