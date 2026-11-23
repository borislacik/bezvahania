<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * The two bookable parts of a day (docs/decisions.md, question 4).
 */
enum TimeSlot: string implements HasLabel
{
    case Morning = 'morning';
    case Afternoon = 'afternoon';

    public function label(): string
    {
        return __("enums.time_slot.{$this->value}");
    }

    public function getLabel(): string
    {
        return $this->label();
    }

    public function startsAt(): string
    {
        return match ($this) {
            self::Morning => '09:00',
            self::Afternoon => '15:00',
        };
    }

    public function endsAt(): string
    {
        return match ($this) {
            self::Morning => '13:00',
            self::Afternoon => '19:00',
        };
    }

    /**
     * Human readable range, e.g. "9:00 – 13:00".
     */
    public function range(): string
    {
        return ltrim($this->startsAt(), '0').' – '.ltrim($this->endsAt(), '0');
    }
}
