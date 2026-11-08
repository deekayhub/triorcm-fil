<?php

namespace App\Listeners;

use App\Enums\LoginEventStatus;
use App\Models\LoginEvent;
use App\Models\User;
use Illuminate\Auth\Events\Logout;

/**
 * Persist a logout so the session timeline is complete (requirement.md §12.3).
 */
class RecordLogout
{
    public function handle(Logout $event): void
    {
        LoginEvent::query()->create([
            'user_id' => $event->user instanceof User ? $event->user->getKey() : null,
            'ip' => (string) request()->ip(),
            'user_agent' => request()->userAgent(),
            'status' => LoginEventStatus::Logout,
        ]);
    }
}
