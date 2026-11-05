<?php

namespace App\Enums;

/**
 * Stored as a string column; Eloquent casts it back to the enum automatically
 * (see Notice::casts()).
 */
enum NoticeCategory: string
{
    case Events = 'events';
    case Roadworks = 'roadworks';
    case Services = 'services';
    case Emergency = 'emergency';

    public function label(): string
    {
        return match ($this) {
            self::Events => 'Events',
            self::Roadworks => 'Roadworks',
            self::Services => 'Public services',
            self::Emergency => 'Emergency',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Events => 'primary',
            self::Roadworks => 'warning',
            self::Services => 'success',
            self::Emergency => 'danger',
        };
    }
}
