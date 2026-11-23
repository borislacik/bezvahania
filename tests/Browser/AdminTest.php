<?php

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-11-23 10:00'));
});

it('lets the admin log in on the admin domain and cancel a booking', function () {
    User::factory()->create(['email' => 'admin@example.com', 'password' => 'tajne-heslo-123']);
    $booking = Booking::factory()->create(['date' => '2026-12-01', 'contact_name' => 'Zrušená Zákazníčka']);

    visit('/login')
        ->withHost('admin.bezvahania.localhost')
        ->type('[type="email"]', 'admin@example.com')
        ->type('[type="password"]', 'tajne-heslo-123')
        ->press(__('filament-panels::auth/pages/login.form.actions.authenticate.label'))
        ->assertSee(__('admin.dashboard.upcoming'));

    // A new visit keeps the session cookie of the browser context.
    visit('/bookings/'.$booking->reference)
        ->withHost('admin.bezvahania.localhost')
        ->assertSee('Zrušená Zákazníčka')
        ->click(__('admin.actions.cancel'))
        ->type('[id$="reason"]', 'Choroba lektora')
        ->press(__('filament-actions::modal.actions.confirm.label'))
        ->assertSee(__('admin.actions.cancelled'))
        ->assertNoJavaScriptErrors();

    expect($booking->refresh()->status)->toBe(BookingStatus::Cancelled)
        ->and($booking->cancellation_reason)->toBe('Choroba lektora');
});
