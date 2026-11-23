<?php

use App\Enums\BookingStatus;
use App\Enums\TimeSlot;

it('translates every enum label to Slovak', function () {
    foreach ([...TimeSlot::cases(), ...BookingStatus::cases()] as $case) {
        expect($case->getLabel())->not->toStartWith('enums.');
    }

    expect(TimeSlot::Morning->label())->toBe('Doobeda (9:00 – 13:00)')
        ->and(BookingStatus::Confirmed->label())->toBe('Potvrdená');
});
