<?php

namespace App\Listeners;

use App\Enums\LoginEventStatus;
use App\Models\LoginEvent;
use App\Models\User;
use Illuminate\Auth\Events\Failed;

/**
 * Persist a failed authentication attempt (requirement.md §12.3).
 */
class RecordFailedLogin
{
    public function handle(Failed $event): void
    {
        $email = $event->credentials['email'] ?? null;

        $user = $email
            ? User::query()->where('email', $email)->first()
            : null;

        LoginEvent::query()->create([
            'user_id' => $user?->getKey(),
            'ip' => (string) request()->ip(),
            'user_agent' => request()->userAgent(),
            'status' => LoginEventStatus::Failed,
        ]);
    }
}
