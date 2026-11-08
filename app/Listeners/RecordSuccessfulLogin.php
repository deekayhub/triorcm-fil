<?php

namespace App\Listeners;

use App\Enums\LoginEventStatus;
use App\Models\LoginEvent;
use App\Models\User;
use Illuminate\Auth\Events\Login;

/**
 * Persist a successful authentication attempt (requirement.md §12.3).
 */
class RecordSuccessfulLogin
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        LoginEvent::query()->create([
            'user_id' => $user instanceof User ? $user->getKey() : null,
            'ip' => (string) request()->ip(),
            'user_agent' => request()->userAgent(),
            'status' => LoginEventStatus::Success,
        ]);

        if ($user instanceof User) {
            $user->forceFill(['last_login_at' => now()])->saveQuietly();
        }
    }
}
