<?php

namespace App\Filament\Pages;

use App\Enums\TimeSlot;
use App\Models\BlockedDay;
use App\Models\Booking;
use App\Support\Availability as AvailabilityCalculator;
use BackedEnum;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

/**
 * Month calendar where the admin blocks whole days or single slots (greyed out on the public calendar).
 */
class Availability extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.availability';

    public string $month;

    public function mount(): void
    {
        $this->month = today()->format('Y-m');
    }

    public static function getNavigationGroup(): string
    {
        return __('admin.groups.bookings');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.availability.navigation');
    }

    public function getTitle(): string|Htmlable
    {
        return __('admin.availability.title');
    }

    public function previousMonth(): void
    {
        $this->month = $this->monthStart()->subMonth()->format('Y-m');
    }

    public function nextMonth(): void
    {
        $this->month = $this->monthStart()->addMonth()->format('Y-m');
    }

    public function currentMonth(): void
    {
        $this->month = today()->format('Y-m');
    }

    /**
     * Blocks or unblocks the whole day. A day with an active booking cannot be blocked.
     */
    public function toggleDay(string $date): void
    {
        $day = $this->editableDay($date);
        if (! $day) {
            return;
        }

        $label = $day->format('j. n. Y');
        $blocks = BlockedDay::query()->whereDate('date', $day)->pluck('slot')->all();

        if ($this->coversWholeDay($blocks)) {
            BlockedDay::query()->whereDate('date', $day)->delete();
            Notification::make()->success()->title(__('admin.availability.day_unblocked', ['date' => $label]))->send();

            return;
        }

        if (Booking::query()->whereDate('date', $day)->whereNotNull('active_slot')->exists()) {
            Notification::make()->warning()->title(__('admin.availability.day_has_bookings', ['date' => $label]))->send();

            return;
        }

        BlockedDay::query()->whereDate('date', $day)->delete();
        BlockedDay::query()->create(['date' => $day, 'slot' => BlockedDay::WHOLE_DAY]);
        Notification::make()->success()->title(__('admin.availability.day_blocked', ['date' => $label]))->send();
    }

    /**
     * Blocks or unblocks one slot. Unblocking a slot of a fully blocked day keeps the other slot blocked.
     */
    public function toggleSlot(string $date, string $slot): void
    {
        $day = $this->editableDay($date);
        $timeSlot = TimeSlot::tryFrom($slot);
        if (! $day || ! $timeSlot) {
            return;
        }

        $replace = ['date' => $day->format('j. n. Y'), 'slot' => mb_strtolower($timeSlot->label())];
        $blocks = BlockedDay::query()->whereDate('date', $day)->get()->keyBy('slot');

        if ($blocks->has(BlockedDay::WHOLE_DAY)) {
            $blocks->get(BlockedDay::WHOLE_DAY)?->delete();
            $other = $timeSlot === TimeSlot::Morning ? TimeSlot::Afternoon : TimeSlot::Morning;
            BlockedDay::query()->create(['date' => $day, 'slot' => $other->value]);
            Notification::make()->success()->title(__('admin.availability.slot_unblocked', $replace))->send();

            return;
        }

        if ($blocks->has($timeSlot->value)) {
            $blocks->get($timeSlot->value)?->delete();
            Notification::make()->success()->title(__('admin.availability.slot_unblocked', $replace))->send();

            return;
        }

        if (Booking::query()->where('active_slot', Booking::slotKey($day->toDateString(), $timeSlot))->exists()) {
            Notification::make()->warning()->title(__('admin.availability.slot_has_booking', $replace))->send();

            return;
        }

        // Both slots blocked = the whole day: keep a single whole-day block, so "Celý deň" is highlighted too.
        if ($blocks->isNotEmpty()) {
            BlockedDay::query()->whereDate('date', $day)->delete();
            BlockedDay::query()->create(['date' => $day, 'slot' => BlockedDay::WHOLE_DAY]);
            Notification::make()->success()->title(__('admin.availability.day_blocked', ['date' => $replace['date']]))->send();

            return;
        }

        BlockedDay::query()->create(['date' => $day, 'slot' => $timeSlot->value]);
        Notification::make()->success()->title(__('admin.availability.slot_blocked', $replace))->send();
    }

    /**
     * @param  list<string>  $blocks  blocked parts of one day
     */
    private function coversWholeDay(array $blocks): bool
    {
        return in_array(BlockedDay::WHOLE_DAY, $blocks, true)
            || array_diff(array_map(fn (TimeSlot $slot): string => $slot->value, TimeSlot::cases()), $blocks) === [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $start = $this->monthStart();
        $gridStart = $start->startOfWeek(CarbonInterface::MONDAY);
        $gridEnd = $start->endOfMonth()->endOfWeek(CarbonInterface::SUNDAY);

        $blocked = [];
        BlockedDay::query()
            ->whereBetween('date', [$gridStart->toDateString(), $gridEnd->toDateString()])
            ->get(['date', 'slot'])
            ->each(function (BlockedDay $block) use (&$blocked): void {
                $blocked[$block->date->toDateString()][$block->slot] = true;
            });

        $bookings = Booking::query()
            ->with('course')
            ->whereNotNull('active_slot')
            ->whereBetween('date', [$gridStart->toDateString(), $gridEnd->toDateString()])
            ->get()
            ->groupBy(fn (Booking $booking): string => $booking->date->toDateString());

        $days = [];
        for ($day = $gridStart; $day->lte($gridEnd); $day = $day->addDay()) {
            $key = $day->toDateString();
            $dayBookings = $bookings->get($key, collect());
            $wholeDay = $this->coversWholeDay(array_keys($blocked[$key] ?? []));
            $slots = [];
            foreach (TimeSlot::cases() as $slot) {
                $slots[$slot->value] = [
                    'range' => $slot->range(),
                    'blocked' => $wholeDay || isset($blocked[$key][$slot->value]),
                    'booking' => $dayBookings->first(fn (Booking $booking): bool => $booking->time_slot === $slot),
                ];
            }
            $days[] = [
                'date' => $key,
                'day' => $day->day,
                'in_month' => $day->month === $start->month,
                'past' => $day->lt(today()),
                'today' => $day->isToday(),
                'blocked' => $wholeDay,
                'has_bookings' => $dayBookings->isNotEmpty(),
                'slots' => $slots,
            ];
        }

        $window = new AvailabilityCalculator;

        return [
            'label' => ucfirst($start->locale(app()->getLocale())->translatedFormat('F Y')),
            'days' => $days,
            'weekdays' => __('booking.calendar.weekdays'),
            'windowText' => __('admin.availability.window', [
                'from' => $window->firstBookableDay()->format('j. n. Y'),
                'until' => $window->lastBookableDay()->format('j. n. Y'),
            ]),
        ];
    }

    /**
     * Today or a future day; past days cannot be changed.
     */
    private function editableDay(string $date): ?CarbonImmutable
    {
        $day = CarbonImmutable::parse($date)->startOfDay();

        return $day->isPast() && ! $day->isToday() ? null : $day;
    }

    private function monthStart(): CarbonImmutable
    {
        return CarbonImmutable::createFromFormat('Y-m-d', $this->month.'-01')?->startOfDay() ?? CarbonImmutable::today()->startOfMonth();
    }
}
