<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

/**
 * Base class for sidebar modules that are not implemented yet.
 *
 * Roadmap modules (outside the MVP) show a "Soon" badge and inherit the
 * roadmap status message. MVP modules override the message with an
 * "under construction" notice until their phase lands.
 */
abstract class ModulePlaceholder extends Page
{
    protected string $view = 'filament.pages.module-placeholder';

    protected static ?string $badge = null;

    protected static string $statusMessage = 'This module is in the roadmap.';

    protected static string $phaseLabel = 'Planned for the build plan';

    public static function getNavigationBadge(): ?string
    {
        return static::$badge;
    }

    /**
     * @return string | array<string> | null
     */
    public static function getNavigationBadgeColor(): string|array|null
    {
        return filled(static::$badge) ? 'warning' : null;
    }

    public function getStatusMessage(): string
    {
        return static::$statusMessage;
    }

    public function getPhaseLabel(): string
    {
        return static::$phaseLabel;
    }
}
