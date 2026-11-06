<?php

namespace Database\Seeders;

use App\Enums\DocumentType;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Seed default settings (requirement.md §13).
     */
    public function run(): void
    {
        Setting::set('general.company_name', 'TrioRCM');
        Setting::set('general.support_email', 'support@triorcm.test');
        Setting::set('general.timezone', 'America/New_York');

        Setting::set('attestation.cycle_days', 120);
        Setting::set('attestation.reminder_days', [30, 14, 7]);

        Setting::set('documents.expiry_warning_days', 60);
        Setting::set('documents.required_types', array_column(DocumentType::cases(), 'value'));

        Setting::set('notifications.email_sender', 'noreply@triorcm.test');
        Setting::set('notifications.attestation_enabled', true);
        Setting::set('notifications.expiry_enabled', true);
        Setting::set('notifications.task_enabled', true);

        Setting::set('security.session_timeout', 120);
        Setting::set('security.enforce_2fa', false);
        Setting::set('security.password_policy', 'strong');
    }
}
