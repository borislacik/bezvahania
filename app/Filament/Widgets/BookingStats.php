<?php

namespace App\Filament\Widgets;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\ContactMessage;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BookingStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make(__('admin.dashboard.upcoming_count'), (string) Booking::query()->upcoming()->count())
                ->icon('heroicon-o-calendar-days'),
            Stat::make(__('admin.dashboard.this_month'), (string) Booking::query()
                ->whereIn('status', [BookingStatus::Confirmed, BookingStatus::Completed])
                ->whereBetween('date', [today()->startOfMonth()->toDateString(), today()->endOfMonth()->toDateString()])
                ->count())
                ->icon('heroicon-o-academic-cap'),
            Stat::make(__('admin.dashboard.unhandled_messages'), (string) ContactMessage::query()->whereNull('handled_at')->count())
                ->icon('heroicon-o-envelope'),
        ];
    }
}
