<?php

namespace App\Enums;

enum LoginEventStatus: string
{
    case Success = 'success';

    case Failed = 'failed';

    case Logout = 'logout';

    /**
     * Human readable label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Success => 'Success',
            self::Failed => 'Failed',
            self::Logout => 'Logout',
        };
    }

    /**
     * Filament badge colour for the status.
     */
    public function color(): string
    {
        return match ($this) {
            self::Success => 'success',
            self::Failed => 'danger',
            self::Logout => 'gray',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
