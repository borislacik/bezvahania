<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * A booking is confirmed immediately (no admin approval, docs/decisions.md question 5).
 */
enum BookingStatus: string implements HasColor, HasLabel
{
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';
    case Completed = 'completed';
    case NoShow = 'no_show';

    public function label(): string
    {
        return __("enums.booking_status.{$this->value}");
    }

    public function getLabel(): string
    {
        return $this->label();
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Confirmed => 'success',
            self::Cancelled => 'danger',
            self::Completed => 'info',
            self::NoShow => 'warning',
        };
    }

    /**
     * Whether the booking still occupies its slot.
     */
    public function occupiesSlot(): bool
    {
        return $this !== self::Cancelled;
    }
}
