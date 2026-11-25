<?php

use App\Enums\BookingStatus;
use App\Enums\TimeSlot;
use App\Mail\BookingConfirmedMail;
use App\Models\Booking;
use App\Models\Course;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-11-23 10:00'));
});

it('lets a visitor pick a day, slot and workshop and book it', function () {
    Mail::fake();
    $course = Course::query()->where('slug', 'takticka-prva-pomoc')->sole();

    visit('/rezervacia')
        ->click('[data-cookie-reject]')
        ->click('label[data-date="2026-11-27"]')
        ->click('label[data-slot="afternoon"]')
        ->click($course->name)
        ->type('contact_name', 'Eva Testovacia')
        ->type('phone', '+421 900 555 666')
        ->type('email', 'eva@example.com')
        ->type('address', 'Testovacia 12, Bratislava')
        ->assertMissing('textarea[name="billing_address"]')
        ->check('billing_different')
        ->assertVisible('textarea[name="billing_address"]')
        ->type('billing_address', 'Firma s.r.o., IČO 12345678')
        ->click('3 osoby')
        ->check('consent')
        ->assertSee($course->name)
        ->press(__('booking.submit'))
        ->assertSee(__('booking.confirmation.title'))
        ->assertSee('Testovacia 12, Bratislava')
        ->assertNoJavaScriptErrors();

    $booking = Booking::query()->sole();
    expect($booking->course_id)->toBe($course->id)
        ->and($booking->date->toDateString())->toBe('2026-11-27')
        ->and($booking->time_slot)->toBe(TimeSlot::Afternoon)
        ->and($booking->participants)->toBe(3)
        ->and($booking->billing_address)->toBe('Firma s.r.o., IČO 12345678')
        ->and($booking->status)->toBe(BookingStatus::Confirmed);

    Mail::assertQueued(BookingConfirmedMail::class, fn ($mail) => $mail->hasTo('eva@example.com'));
});

it('does not offer a fully booked day', function () {
    foreach (TimeSlot::cases() as $slot) {
        Booking::factory()->create(['date' => '2026-11-27', 'time_slot' => $slot]);
    }
    Booking::factory()->create(['date' => '2026-11-28', 'time_slot' => TimeSlot::Morning]);

    visit('/rezervacia')
        ->assertPresent('[data-date="2026-11-27"][data-status="booked"]')
        ->assertMissing('input[name="date"][value="2026-11-27"]')
        ->click('[data-cookie-reject]')
        ->click('label[data-date="2026-11-28"]')
        ->assertAttribute('label[data-slot="morning"] input', 'disabled', 'disabled')
        ->assertNoJavaScriptErrors();
});

it('switches to the next month', function () {
    visit('/rezervacia')
        ->click('[data-cookie-reject]')
        ->assertSee('November 2026')
        ->click('[data-month="2026-11"] [data-testid="next-month"]')
        ->assertVisible('[data-month="2026-12"]')
        ->assertPresent('label[data-date="2026-12-15"]');
});
