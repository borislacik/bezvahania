<?php

namespace App\Filament\Pages;

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
 * Month calendar where the admin blocks days (greyed out on the public calendar).
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

    public function toggleDay(string $date): void
    {
        $day = CarbonImmutable::parse($date)->startOfDay();
        $label = $day->format('j. n. Y');

        if ($day->isPast() && ! $day->isToday()) {
            return;
        }

        $blocked = BlockedDay::query()->whereDate('date', $day)->first();

        if ($blocked) {
            $blocked->delete();
            Notification::make()->success()->title(__('admin.availability.day_unblocked', ['date' => $label]))->send();

            return;
        }

        if (Booking::query()->whereDate('date', $day)->whereNotNull('active_slot')->exists()) {
            Notification::make()->warning()->title(__('admin.availability.day_has_bookings', ['date' => $label]))->send();

            return;
        }

        BlockedDay::query()->create(['date' => $day]);
        Notification::make()->success()->title(__('admin.availability.day_blocked', ['date' => $label]))->send();
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $start = $this->monthStart();
        $gridStart = $start->startOfWeek(CarbonInterface::MONDAY);
        $gridEnd = $start->endOfMonth()->endOfWeek(CarbonInterface::SUNDAY);

        $blocked = BlockedDay::query()
            ->whereBetween('date', [$gridStart->toDateString(), $gridEnd->toDateString()])
            ->pluck('date')
            ->map(fn (CarbonInterface $date): string => $date->toDateString())
            ->flip();

        $bookings = Booking::query()
            ->with('course')
            ->whereNotNull('active_slot')
            ->whereBetween('date', [$gridStart->toDateString(), $gridEnd->toDateString()])
            ->get()
            ->groupBy(fn (Booking $booking): string => $booking->date->toDateString());

        $days = [];
        for ($day = $gridStart; $day->lte($gridEnd); $day = $day->addDay()) {
            $key = $day->toDateString();
            $days[] = [
                'date' => $key,
                'day' => $day->day,
                'in_month' => $day->month === $start->month,
                'past' => $day->lt(today()),
                'today' => $day->isToday(),
                'blocked' => isset($blocked[$key]),
                'bookings' => $bookings->get($key, collect()),
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

    private function monthStart(): CarbonImmutable
    {
        return CarbonImmutable::createFromFormat('Y-m-d', $this->month.'-01')?->startOfDay() ?? CarbonImmutable::today()->startOfMonth();
    }
}
