<?php

use App\Enums\TimeSlot;
use App\Filament\Pages\Availability;
use App\Filament\Pages\Settings;
use App\Filament\Widgets\BookingStats;
use App\Filament\Widgets\UpcomingBookings;
use App\Models\BlockedDay;
use App\Models\Booking;
use App\Models\Setting;
use Filament\Pages\Dashboard;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-11-23 10:00'));
    actingAsAdmin();
});

it('renders the dashboard with stats and upcoming bookings', function () {
    $booking = Booking::factory()->create(['date' => '2026-11-28', 'time_slot' => TimeSlot::Morning]);

    Livewire::test(Dashboard::class)->assertOk();
    Livewire::test(BookingStats::class)->assertOk()->assertSee(__('admin.dashboard.upcoming_count'));
    Livewire::test(UpcomingBookings::class)->assertOk()->assertCanSeeTableRecords([$booking]);
});

it('blocks and unblocks days in the availability calendar', function () {
    Livewire::test(Availability::class)
        ->assertOk()
        ->assertSee('November 2026')
        ->call('toggleDay', '2026-11-30')
        ->assertNotified(__('admin.availability.day_blocked', ['date' => '30. 11. 2026']));

    expect(BlockedDay::query()->whereDate('date', '2026-11-30')->exists())->toBeTrue();

    Livewire::test(Availability::class)->call('toggleDay', '2026-11-30');

    expect(BlockedDay::query()->count())->toBe(0);
});

it('does not block days with bookings or days in the past', function () {
    Booking::factory()->create(['date' => '2026-12-02']);

    Livewire::test(Availability::class)
        ->call('toggleDay', '2026-12-02')
        ->assertNotified(__('admin.availability.day_has_bookings', ['date' => '2. 12. 2026']))
        ->call('toggleDay', '2026-11-01');

    expect(BlockedDay::query()->count())->toBe(0);
});

it('navigates between months', function () {
    Livewire::test(Availability::class)
        ->call('nextMonth')
        ->assertSet('month', '2026-12')
        ->assertSee('December 2026')
        ->call('previousMonth')
        ->call('previousMonth')
        ->assertSet('month', '2026-10')
        ->call('currentMonth')
        ->assertSet('month', '2026-11');
});

it('saves contact and operator settings', function () {
    Livewire::test(Settings::class)
        ->assertSchemaStateSet(['contact_email' => 'info@bezvahania.sk', 'contact_phone' => '+421 915 918 386'])
        ->fillForm([
            'contact_phone' => '+421 905 000 000',
            'contact_email' => 'info@bezvahania.sk',
            'notification_email' => 'majitel@bezvahania.sk',
            'operator_name' => 'Ján Živnostník',
            'operator_ico' => '12345678',
            'operator_dic' => '1234567890',
            'operator_vat_note' => 'Nie som platiteľ DPH',
            'operator_address' => 'Bratislava',
            'booking_lead_days' => 3,
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified(__('admin.settings.saved'));

    expect(Setting::get('contact_phone'))->toBe('+421 905 000 000')
        ->and(Setting::get('booking_lead_days'))->toBe('3');

    $this->get(route('home'))->assertSee('Ján Živnostník')->assertSee('12345678')
        ->assertSee('DIČ: 1234567890')->assertSee('Nie som platiteľ DPH');
});
