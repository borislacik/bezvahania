<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Bookings\BookingResource;
use App\Models\Booking;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class UpcomingBookings extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('admin.dashboard.upcoming'))
            ->query(fn () => Booking::query()->upcoming()->with('course'))
            ->paginated([10])
            ->emptyStateHeading(__('admin.dashboard.no_upcoming'))
            ->recordUrl(fn (Booking $record): string => BookingResource::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('date')->label(__('admin.fields.date'))->date('D j. n. Y'),
                TextColumn::make('time_slot')->label(__('admin.fields.time_slot')),
                TextColumn::make('course.name')->label(__('admin.fields.course')),
                TextColumn::make('contact_name')->label(__('admin.fields.contact_name')),
                TextColumn::make('phone')->label(__('admin.fields.phone')),
                TextColumn::make('address')->label(__('admin.fields.address'))->wrap(),
                TextColumn::make('participants')->label(__('admin.fields.participants'))->alignCenter(),
            ]);
    }
}
