<?php

use App\Enums\BookingStatus;
use App\Enums\TimeSlot;
use App\Filament\Resources\Bookings\Pages\CreateBooking;
use App\Filament\Resources\Bookings\Pages\EditBooking;
use App\Filament\Resources\Bookings\Pages\ListBookings;
use App\Filament\Resources\Bookings\Pages\ViewBooking;
use App\Mail\BookingCancelledMail;
use App\Mail\BookingConfirmedMail;
use App\Models\Booking;
use App\Models\Course;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-11-23 10:00'));
    Mail::fake();
    actingAsAdmin();
});

it('lists upcoming bookings by default', function () {
    $upcoming = Booking::factory()->create(['date' => '2026-12-01']);
    $past = Booking::factory()->create(['date' => '2026-11-10']);

    Livewire::test(ListBookings::class)
        ->assertOk()
        ->assertCanSeeTableRecords([$upcoming])
        ->assertCanNotSeeTableRecords([$past]);
});

it('filters bookings by status and course', function () {
    $course = Course::factory()->create();
    $confirmed = Booking::factory()->for($course)->create(['date' => '2026-12-01']);
    $cancelled = Booking::factory()->cancelled()->create(['date' => '2026-12-02']);

    Livewire::test(ListBookings::class)
        ->filterTable('status', BookingStatus::Cancelled->value)
        ->assertCanSeeTableRecords([$cancelled])
        ->assertCanNotSeeTableRecords([$confirmed])
        ->resetTableFilters()
        ->filterTable('course_id', $course->id)
        ->assertCanSeeTableRecords([$confirmed])
        ->assertCanNotSeeTableRecords([$cancelled]);
});

it('cancels a booking with a reason from the table', function () {
    $booking = Booking::factory()->create(['date' => '2026-12-01']);

    Livewire::test(ListBookings::class)
        ->callTableAction('cancel', $booking, data: ['reason' => 'Choroba lektora'])
        ->assertNotified(__('admin.actions.cancelled'));

    expect($booking->refresh()->status)->toBe(BookingStatus::Cancelled)
        ->and($booking->cancellation_reason)->toBe('Choroba lektora')
        ->and($booking->active_slot)->toBeNull();

    Mail::assertQueued(BookingCancelledMail::class);
});

it('marks past bookings as completed or no-show on the detail page', function () {
    $completed = Booking::factory()->create(['date' => '2026-11-20']);
    $noShow = Booking::factory()->create(['date' => '2026-11-21']);
    $future = Booking::factory()->create(['date' => '2026-12-05']);

    Livewire::test(ViewBooking::class, ['record' => $completed->getRouteKey()])
        ->assertOk()
        ->assertSee($completed->contact_name)
        ->callAction('complete');
    Livewire::test(ViewBooking::class, ['record' => $noShow->getRouteKey()])->callAction('no_show');
    Livewire::test(ViewBooking::class, ['record' => $future->getRouteKey()])
        ->assertActionHidden('complete')
        ->assertActionVisible('cancel');

    expect($completed->refresh()->status)->toBe(BookingStatus::Completed)
        ->and($noShow->refresh()->status)->toBe(BookingStatus::NoShow);
});

it('creates a phone booking and refuses an occupied slot', function () {
    $course = Course::factory()->create();

    Livewire::test(CreateBooking::class)
        ->fillForm([
            'course_id' => $course->id,
            'date' => '2026-12-10',
            'time_slot' => TimeSlot::Afternoon->value,
            'participants' => 2,
            'contact_name' => 'Telefonická Zákazníčka',
            'phone' => '0900 333 444',
            'email' => 'telefon@example.com',
            'address' => 'Dlhá 5, Pezinok',
            'billing_address' => 'Firma s.r.o., IČO 12345678',
            'notify' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $booking = Booking::query()->sole();
    expect($booking->time_slot)->toBe(TimeSlot::Afternoon)
        ->and($booking->status)->toBe(BookingStatus::Confirmed)
        ->and($booking->billing_address)->toBe('Firma s.r.o., IČO 12345678');
    Mail::assertQueued(BookingConfirmedMail::class);

    Livewire::test(CreateBooking::class)
        ->fillForm([
            'course_id' => $course->id,
            'date' => '2026-12-10',
            'time_slot' => TimeSlot::Afternoon->value,
            'participants' => 2,
            'contact_name' => 'Druhý',
            'phone' => '0900 333 445',
            'email' => 'druhy@example.com',
            'address' => 'Krátka 1',
        ])
        ->call('create')
        ->assertNotified(__('admin.actions.slot_unavailable'));

    expect(Booking::query()->count())->toBe(1);
});

it('edits only contact details of a booking', function () {
    $booking = Booking::factory()->create(['date' => '2026-12-01', 'time_slot' => TimeSlot::Morning]);

    Livewire::test(EditBooking::class, ['record' => $booking->getRouteKey()])
        ->fillForm(['phone' => '0911 000 111', 'billing_address' => 'Iná firma', 'participants' => 2, 'date' => '2026-12-24'])
        ->call('save')
        ->assertHasNoFormErrors();

    $booking->refresh();
    expect($booking->phone)->toBe('0911 000 111')
        ->and($booking->billing_address)->toBe('Iná firma')
        ->and($booking->participants)->toBe(2)
        ->and($booking->date->toDateString())->toBe('2026-12-01');
});

it('exports the filtered bookings as CSV', function () {
    $booking = Booking::factory()->create(['date' => '2026-12-01', 'contact_name' => 'Exportovaná Osoba']);

    $response = Livewire::test(ListBookings::class)->instance()->exportCsv();

    ob_start();
    $response->sendContent();
    $csv = (string) ob_get_clean();

    expect($csv)->toStartWith("\xEF\xBB\xBF")
        ->toContain('Exportovaná Osoba')
        ->toContain($booking->reference)
        ->toContain(';');
});
