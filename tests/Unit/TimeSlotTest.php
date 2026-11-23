<?php

use App\Enums\BookingStatus;
use App\Enums\TimeSlot;

it('defines the morning and afternoon slots', function () {
    expect(TimeSlot::Morning->startsAt())->toBe('09:00')
        ->and(TimeSlot::Morning->endsAt())->toBe('13:00')
        ->and(TimeSlot::Afternoon->startsAt())->toBe('15:00')
        ->and(TimeSlot::Afternoon->endsAt())->toBe('19:00')
        ->and(TimeSlot::Morning->range())->toBe('9:00 – 13:00')
        ->and(TimeSlot::Afternoon->range())->toBe('15:00 – 19:00');
});

it('knows which booking statuses occupy a slot', function (BookingStatus $status, bool $occupies) {
    expect($status->occupiesSlot())->toBe($occupies);
})->with([
    [BookingStatus::Confirmed, true],
    [BookingStatus::Completed, true],
    [BookingStatus::NoShow, true],
    [BookingStatus::Cancelled, false],
]);

it('maps booking statuses to Filament colors', function () {
    expect(BookingStatus::Confirmed->getColor())->toBe('success')
        ->and(BookingStatus::Cancelled->getColor())->toBe('danger')
        ->and(BookingStatus::Completed->getColor())->toBe('info')
        ->and(BookingStatus::NoShow->getColor())->toBe('warning');
});
