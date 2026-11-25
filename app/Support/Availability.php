<?php

namespace App\Support;

use App\Enums\TimeSlot;
use App\Models\BlockedDay;
use App\Models\Booking;
use App\Models\Setting;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * Computes which days and slots can be booked (docs/features/booking.md).
 *
 * Bookable window: from today + "booking_lead_days" (admin setting) to the end of next month.
 * A slot is unavailable when the admin blocked the whole day or that slot, or the slot already has an active booking.
 */
final class Availability
{
    public const STATUS_AVAILABLE = 'available';

    public const STATUS_BOOKED = 'booked';

    public const STATUS_BLOCKED = 'blocked';

    public const STATUS_UNAVAILABLE = 'unavailable';

    private CarbonImmutable $today;

    /** @var array<string, array<string, bool>>|null date => blocked parts ("day", "morning", "afternoon") */
    private ?array $blockedDays = null;

    /** @var array<string, bool>|null */
    private ?array $bookedSlots = null;

    public function __construct(?CarbonInterface $today = null)
    {
        $this->today = CarbonImmutable::parse($today ?? today())->startOfDay();
    }

    public function firstBookableDay(): CarbonImmutable
    {
        return $this->today->addDays(max(0, (int) Setting::get('booking_lead_days')));
    }

    public function lastBookableDay(): CarbonImmutable
    {
        return $this->today->addMonthNoOverflow()->endOfMonth()->startOfDay();
    }

    public function slotStatus(CarbonInterface $date, TimeSlot $slot): string
    {
        $date = CarbonImmutable::parse($date)->startOfDay();

        if ($date->lt($this->firstBookableDay()) || $date->gt($this->lastBookableDay())) {
            return self::STATUS_UNAVAILABLE;
        }

        $blocked = $this->blockedDays()[$date->toDateString()] ?? [];

        if (isset($blocked[BlockedDay::WHOLE_DAY]) || isset($blocked[$slot->value])) {
            return self::STATUS_BLOCKED;
        }

        if (isset($this->bookedSlots()[Booking::slotKey($date->toDateString(), $slot)])) {
            return self::STATUS_BOOKED;
        }

        return self::STATUS_AVAILABLE;
    }

    public function isBookable(CarbonInterface $date, TimeSlot $slot): bool
    {
        return $this->slotStatus($date, $slot) === self::STATUS_AVAILABLE;
    }

    /**
     * The current and the next month, ready for rendering the calendar.
     *
     * @return list<array{key: string, label: string, weeks: list<list<array{date: string, day: int, in_month: bool, status: string, slots: array<string, string>}>>}>
     */
    public function months(): array
    {
        return [
            $this->month($this->today->startOfMonth()),
            $this->month($this->today->addMonthNoOverflow()->startOfMonth()),
        ];
    }

    /**
     * @return array{key: string, label: string, weeks: list<list<array{date: string, day: int, in_month: bool, status: string, slots: array<string, string>}>>}
     */
    private function month(CarbonImmutable $month): array
    {
        $start = $month->startOfWeek(CarbonInterface::MONDAY);
        $end = $month->endOfMonth()->endOfWeek(CarbonInterface::SUNDAY);
        $weeks = [];
        $week = [];

        for ($day = $start; $day->lte($end); $day = $day->addDay()) {
            $inMonth = $day->month === $month->month;
            $slots = [];

            foreach (TimeSlot::cases() as $slot) {
                $slots[$slot->value] = $inMonth ? $this->slotStatus($day, $slot) : self::STATUS_UNAVAILABLE;
            }

            $week[] = [
                'date' => $day->toDateString(),
                'day' => $day->day,
                'in_month' => $inMonth,
                'status' => $this->dayStatus($slots),
                'slots' => $slots,
            ];

            if (count($week) === 7) {
                $weeks[] = $week;
                $week = [];
            }
        }

        return [
            'key' => $month->format('Y-m'),
            'label' => ucfirst($month->locale(app()->getLocale())->translatedFormat('F Y')),
            'weeks' => $weeks,
        ];
    }

    /**
     * @param  array<string, string>  $slots
     */
    private function dayStatus(array $slots): string
    {
        if (in_array(self::STATUS_AVAILABLE, $slots, true)) {
            return self::STATUS_AVAILABLE;
        }

        foreach ([self::STATUS_BLOCKED, self::STATUS_BOOKED] as $status) {
            if (in_array($status, $slots, true)) {
                return $status;
            }
        }

        return self::STATUS_UNAVAILABLE;
    }

    /**
     * @return array<string, array<string, bool>>
     */
    private function blockedDays(): array
    {
        if ($this->blockedDays === null) {
            $this->blockedDays = [];

            BlockedDay::query()
                ->whereBetween('date', [$this->today->toDateString(), $this->lastBookableDay()->toDateString()])
                ->get(['date', 'slot'])
                ->each(function (BlockedDay $block): void {
                    $this->blockedDays[$block->date->toDateString()][$block->slot] = true;
                });
        }

        return $this->blockedDays;
    }

    /**
     * @return array<string, bool>
     */
    private function bookedSlots(): array
    {
        return $this->bookedSlots ??= Booking::query()
            ->whereNotNull('active_slot')
            ->whereBetween('date', [$this->today->toDateString(), $this->lastBookableDay()->toDateString()])
            ->pluck('active_slot')
            ->mapWithKeys(fn (string $key): array => [$key => true])
            ->all();
    }
}
