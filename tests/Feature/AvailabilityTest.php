<?php

use App\Enums\TimeSlot;
use App\Models\BlockedDay;
use App\Models\Booking;
use App\Models\Setting;
use App\Support\Availability;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-11-23 10:00'));
});

it('opens the window from the lead time to the end of next month', function () {
    $availability = new Availability;

    expect($availability->firstBookableDay()->toDateString())->toBe('2026-11-25')
        ->and($availability->lastBookableDay()->toDateString())->toBe('2026-12-31');

    Setting::put(['booking_lead_days' => 0]);

    expect((new Availability)->firstBookableDay()->toDateString())->toBe('2026-11-23');
});

it('reports the status of a slot', function () {
    BlockedDay::factory()->create(['date' => '2026-11-27']);
    Booking::factory()->create(['date' => '2026-11-28', 'time_slot' => TimeSlot::Morning]);
    Booking::factory()->cancelled()->create(['date' => '2026-11-29', 'time_slot' => TimeSlot::Morning]);

    $availability = new Availability;

    expect($availability->slotStatus(Carbon::parse('2026-11-24'), TimeSlot::Morning))->toBe(Availability::STATUS_UNAVAILABLE)
        ->and($availability->slotStatus(Carbon::parse('2027-01-01'), TimeSlot::Morning))->toBe(Availability::STATUS_UNAVAILABLE)
        ->and($availability->slotStatus(Carbon::parse('2026-11-27'), TimeSlot::Afternoon))->toBe(Availability::STATUS_BLOCKED)
        ->and($availability->slotStatus(Carbon::parse('2026-11-28'), TimeSlot::Morning))->toBe(Availability::STATUS_BOOKED)
        ->and($availability->slotStatus(Carbon::parse('2026-11-28'), TimeSlot::Afternoon))->toBe(Availability::STATUS_AVAILABLE)
        ->and($availability->isBookable(Carbon::parse('2026-11-29'), TimeSlot::Morning))->toBeTrue();
});

it('builds the current and next month for the calendar', function () {
    Booking::factory()->create(['date' => '2026-12-02', 'time_slot' => TimeSlot::Morning]);
    Booking::factory()->create(['date' => '2026-12-02', 'time_slot' => TimeSlot::Afternoon]);
    BlockedDay::factory()->create(['date' => '2026-12-03']);

    $months = (new Availability)->months();

    expect($months)->toHaveCount(2)
        ->and($months[0]['key'])->toBe('2026-11')
        ->and($months[0]['label'])->toBe('November 2026')
        ->and($months[1]['key'])->toBe('2026-12');

    $days = collect($months[1]['weeks'])->flatten(1)->keyBy('date');

    expect($days['2026-12-02']['status'])->toBe(Availability::STATUS_BOOKED)
        ->and($days['2026-12-03']['status'])->toBe(Availability::STATUS_BLOCKED)
        ->and($days['2026-12-04']['status'])->toBe(Availability::STATUS_AVAILABLE)
        ->and($days['2026-11-30']['in_month'])->toBeFalse();

    foreach ($months as $month) {
        foreach ($month['weeks'] as $week) {
            expect($week)->toHaveCount(7);
        }
    }
});
