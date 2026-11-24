<?php

use App\Actions\Bookings\CancelBooking;
use App\Actions\Bookings\CreateBooking;
use App\Enums\BookingStatus;
use App\Enums\TimeSlot;
use App\Exceptions\SlotUnavailableException;
use App\Mail\BookingCancelledMail;
use App\Mail\BookingConfirmedMail;
use App\Mail\NewBookingAdminMail;
use App\Models\BlockedDay;
use App\Models\Booking;
use App\Models\Course;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-11-23 10:00'));
    Mail::fake();
    $this->course = Course::factory()->create(['price' => 200]);
});

function bookingPayload(array $overrides = []): array
{
    return array_merge([
        'course_id' => test()->course->id,
        'date' => '2026-12-02',
        'time_slot' => 'morning',
        'contact_name' => 'Peter Novák',
        'email' => 'Peter@Example.com',
        'phone' => '+421 900 123 456',
        'address' => 'Hlavná 1, Bratislava',
        'participants' => 3,
        'note' => 'Máme malé dieťa.',
        'consent' => '1',
    ], $overrides);
}

it('shows the booking form with the calendar', function () {
    $this->get(route('booking.create'))
        ->assertOk()
        ->assertSee(__('booking.title'))
        ->assertSee('November 2026')
        ->assertSee('December 2026')
        ->assertSee($this->course->name)
        ->assertSee('data-date="2026-12-02"', escape: false)
        ->assertSee('data-status="available"', escape: false);
});

it('preselects the workshop from the query string', function () {
    $other = Course::factory()->create();

    $html = (string) $this->get(route('booking.create', ['workshop' => $other->slug]))->assertOk()->getContent();

    expect($html)->toMatch('/name="course_id"\s+value="'.$other->id.'"[^>]*checked/')
        ->not->toMatch('/name="course_id"\s+value="'.$this->course->id.'"[^>]*checked/');
});

it('creates a confirmed booking and queues both e-mails', function () {
    $response = $this->post(route('booking.store'), bookingPayload());

    $booking = Booking::query()->sole();

    expect($booking->status)->toBe(BookingStatus::Confirmed)
        ->and($booking->time_slot)->toBe(TimeSlot::Morning)
        ->and($booking->date->toDateString())->toBe('2026-12-02')
        ->and($booking->email)->toBe('peter@example.com')
        ->and($booking->participants)->toBe(3)
        ->and((float) $booking->price)->toBe(200.0)
        ->and($booking->consent_at)->not->toBeNull()
        ->and($booking->consent_version)->toBe(CreateBooking::CONSENT_VERSION)
        ->and($booking->active_slot)->toBe('2026-12-02:morning')
        ->and($booking->reference)->toStartWith('BV-');

    $response->assertRedirect();
    expect($response->headers->get('Location'))->toContain('/rezervacia/'.$booking->reference)->toContain('signature=');

    Mail::assertQueued(BookingConfirmedMail::class, fn ($mail) => $mail->hasTo('peter@example.com') && $mail->hasReplyTo('info@bezvahania.sk'));
    Mail::assertQueued(NewBookingAdminMail::class, fn ($mail) => $mail->hasTo('info@bezvahania.sk'));
});

it('shows the confirmation page only with a valid signature', function () {
    $this->post(route('booking.store'), bookingPayload());
    $booking = Booking::query()->sole();

    $this->get(route('booking.show', $booking))->assertForbidden();

    $this->followingRedirects()
        ->post(route('booking.store'), bookingPayload(['date' => '2026-12-03']))
        ->assertOk()
        ->assertSee(__('booking.confirmation.title'))
        ->assertSee('Hlavná 1, Bratislava');
});

it('rejects a slot that is already booked', function () {
    Booking::factory()->create(['date' => '2026-12-02', 'time_slot' => TimeSlot::Morning]);

    $this->from(route('booking.create'))
        ->post(route('booking.store'), bookingPayload())
        ->assertRedirect(route('booking.create'))
        ->assertSessionHasErrors(['date' => __('booking.errors.slot_unavailable')]);

    expect(Booking::query()->count())->toBe(1);
    Mail::assertNothingQueued();
});

it('allows booking the other slot of the same day and a cancelled slot', function () {
    Booking::factory()->create(['date' => '2026-12-02', 'time_slot' => TimeSlot::Morning]);
    Booking::factory()->cancelled()->create(['date' => '2026-12-02', 'time_slot' => TimeSlot::Afternoon]);

    $this->post(route('booking.store'), bookingPayload(['time_slot' => 'afternoon']))->assertSessionHasNoErrors();

    expect(Booking::query()->whereNotNull('active_slot')->count())->toBe(2);
});

it('rejects blocked days, past days, days inside the lead time and days after next month', function (string $date) {
    BlockedDay::factory()->create(['date' => '2026-12-05']);

    $this->post(route('booking.store'), bookingPayload(['date' => $date]))
        ->assertSessionHasErrors('date');

    expect(Booking::query()->count())->toBe(0);
})->with(['blocked' => '2026-12-05', 'past' => '2026-11-20', 'today' => '2026-11-23', 'lead time' => '2026-11-24', 'too far' => '2027-01-04']);

it('never lets two bookings occupy the same slot', function () {
    Booking::factory()->create(['date' => '2026-12-02', 'time_slot' => TimeSlot::Morning]);

    // Simulates a race: the availability check is skipped, the unique index must still stop it.
    expect(fn () => app(CreateBooking::class)->handle([
        'course_id' => $this->course->id,
        'date' => '2026-12-02',
        'time_slot' => 'morning',
        'contact_name' => 'Druhý',
        'email' => 'druhy@example.com',
        'phone' => '0900 000 000',
        'address' => 'Ulica 2',
        'participants' => 2,
    ], checkWindow: false))->toThrow(SlotUnavailableException::class);

    expect(Booking::query()->count())->toBe(1);
});

it('validates the booking form', function (array $overrides, string $field) {
    $this->post(route('booking.store'), bookingPayload($overrides))->assertSessionHasErrors($field);
})->with([
    'missing name' => [['contact_name' => ''], 'contact_name'],
    'invalid email' => [['email' => 'nie-je-email'], 'email'],
    'invalid phone' => [['phone' => 'abc'], 'phone'],
    'missing address' => [['address' => ''], 'address'],
    'too many people' => [['participants' => 5], 'participants'],
    'no people' => [['participants' => 0], 'participants'],
    'unknown slot' => [['time_slot' => 'evening'], 'time_slot'],
    'bad date' => [['date' => '2. 12. 2026'], 'date'],
    'no consent' => [['consent' => null], 'consent'],
    'honeypot' => [['website' => 'spam'], 'website'],
]);

it('rejects inactive workshops', function () {
    $inactive = Course::factory()->inactive()->create();

    $this->post(route('booking.store'), bookingPayload(['course_id' => $inactive->id]))->assertSessionHasErrors('course_id');
});

it('rate limits the booking form', function () {
    foreach (range(1, 5) as $i) {
        $this->post(route('booking.store'), bookingPayload(['contact_name' => '']));
    }

    $this->post(route('booking.store'), bookingPayload())->assertTooManyRequests();
});

it('cancels a booking, frees the slot and informs the customer', function () {
    $booking = Booking::factory()->create(['date' => '2026-12-02', 'time_slot' => TimeSlot::Morning]);

    app(CancelBooking::class)->handle($booking, 'Choroba lektora');

    $booking->refresh();
    expect($booking->status)->toBe(BookingStatus::Cancelled)
        ->and($booking->active_slot)->toBeNull()
        ->and($booking->cancellation_reason)->toBe('Choroba lektora')
        ->and($booking->cancelled_at)->not->toBeNull();

    Mail::assertQueued(BookingCancelledMail::class, fn ($mail) => $mail->hasTo($booking->email));

    expect(fn () => app(CancelBooking::class)->handle($booking))->toThrow(InvalidArgumentException::class);
});

it('renders the booking e-mails in Slovak', function () {
    $booking = Booking::factory()->for($this->course)->create([
        'date' => '2026-12-02',
        'time_slot' => TimeSlot::Afternoon,
        'cancellation_reason' => 'Choroba',
    ]);

    (new BookingConfirmedMail($booking))->assertSeeInHtml($booking->reference)
        ->assertSeeInHtml('15:00 – 19:00')
        ->assertSeeInHtml(__('mail.booking_confirmed.heading'))
        ->assertHasSubject(__('mail.booking_confirmed.subject', ['reference' => $booking->reference]));

    (new NewBookingAdminMail($booking))->assertSeeInHtml($booking->phone)->assertSeeInHtml(e($booking->contact_name));

    $booking->update(['status' => BookingStatus::Cancelled]);
    (new BookingCancelledMail($booking))->assertSeeInHtml('Choroba')->assertSeeInHtml(__('mail.booking_cancelled.button'));
});

it('sends automatic e-mails from the booking address and takes replies on the contact address', function () {
    $booking = Booking::factory()->for($this->course)->create();

    expect(config('mail.from.address'))->toBe('rezervacie@bezvahania.sk');
    (new BookingConfirmedMail($booking))->assertHasReplyTo('info@bezvahania.sk')->assertSeeInHtml('+421 915 918 386')->assertSeeInHtml('info@bezvahania.sk');
    (new BookingCancelledMail($booking))->assertHasReplyTo('info@bezvahania.sk');
});

it('generates unique booking references', function () {
    $references = collect(range(1, 20))->map(fn () => Booking::newReference());

    expect($references->unique())->toHaveCount(20)
        ->and($references->first())->toMatch('/^BV-[A-Z0-9]{6}$/');
});
