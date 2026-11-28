<?php

use App\Actions\Bookings\CancelBooking;
use App\Actions\Bookings\CreateBooking;
use App\Enums\BookingStatus;
use App\Mail\BookingConfirmedMail;
use App\Mail\NewBookingAdminMail;
use App\Models\Booking;
use App\Models\Course;
use App\Models\Setting;
use App\Support\BundlePricing;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Number;

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-11-23 10:00'));
    Mail::fake();
    $this->cpr = Course::factory()->bundled()->create(['name' => 'KPR a AED', 'price' => 200, 'sort_order' => 1]);
    $this->civil = Course::factory()->bundled()->create(['name' => 'Civilná prvá pomoc', 'price' => 200, 'sort_order' => 2]);
    $this->tactical = Course::factory()->create(['name' => 'Taktická prvá pomoc', 'price' => 200, 'sort_order' => 3]);
});

function bundleEur(int $amount): string
{
    return (string) Number::currency($amount, 'EUR', 'sk', 0);
}

/**
 * @param  array<string, mixed>  $overrides
 */
function bundleBooking(Course $course, array $overrides = []): Booking
{
    static $day = 0;
    $day++;

    return app(CreateBooking::class)->handle(array_merge([
        'course_id' => $course->id,
        'date' => Carbon::parse('2026-12-01')->addDays($day)->toDateString(),
        'time_slot' => 'morning',
        'contact_name' => 'Jana Malá',
        'email' => 'jana@example.com',
        'phone' => '+421 905 111 222',
        'address' => 'Hlavná 1, Bratislava',
        'participants' => 3,
    ], $overrides), checkWindow: false);
}

it('charges the second bundled workshop the rest of the bundle price, in either order', function (string $first, string $second) {
    $firstBooking = bundleBooking($this->{$first});
    $secondBooking = bundleBooking($this->{$second});

    expect((float) $firstBooking->price)->toBe(200.0)
        ->and($firstBooking->bundle_booking_id)->toBeNull()
        ->and((float) $secondBooking->price)->toBe(100.0)
        ->and($secondBooking->bundle_booking_id)->toBe($firstBooking->id)
        ->and($firstBooking->fresh()?->bundleCounterpart()?->is($secondBooking))->toBeTrue()
        ->and($secondBooking->bundleCounterpart()?->is($firstBooking))->toBeTrue();
})->with([
    'civil first' => ['civil', 'cpr'],
    'KPR first' => ['cpr', 'civil'],
]);

it('recognises the customer by e-mail or by phone in any format', function (array $contact) {
    bundleBooking($this->civil);

    $booking = bundleBooking($this->cpr, $contact);

    expect((float) $booking->price)->toBe(100.0);
})->with([
    'same e-mail, other phone' => [['email' => 'JANA@example.com ', 'phone' => '0911 000 000']],
    'same phone, other e-mail' => [['email' => 'iny@example.com', 'phone' => '0905111222']],
    'phone with 00421' => [['email' => 'iny@example.com', 'phone' => '00421 905 111 222']],
]);

it('counts a workshop that already took place, with no time limit', function () {
    $past = Booking::factory()->for($this->civil)->create([
        'date' => '2024-05-10', 'status' => BookingStatus::Completed, 'email' => 'jana@example.com',
    ]);

    $booking = bundleBooking($this->cpr);

    expect((float) $booking->price)->toBe(100.0)->and($booking->bundle_booking_id)->toBe($past->id);
});

it('keeps the standard price without a matching booking of the other bundled workshop', function () {
    bundleBooking($this->tactical);
    bundleBooking($this->cpr, ['email' => 'iny@example.com', 'phone' => '0911 000 000']);
    Booking::factory()->for($this->civil)->cancelled()->create(['email' => 'jana@example.com']);
    Booking::factory()->for($this->civil)->create(['email' => 'jana@example.com', 'status' => BookingStatus::NoShow]);

    expect((float) bundleBooking($this->cpr)->price)->toBe(200.0)
        ->and((float) bundleBooking($this->tactical)->price)->toBe(200.0);
});

it('uses each booking for one bundle only', function () {
    bundleBooking($this->civil);
    bundleBooking($this->cpr);

    $third = bundleBooking($this->cpr);
    $fourth = bundleBooking($this->civil);

    expect((float) $third->price)->toBe(200.0)
        ->and((float) $fourth->price)->toBe(100.0)
        ->and($fourth->bundle_booking_id)->toBe($third->id);
});

it('follows the bundle price from the settings and needs exactly two bundled workshops', function () {
    Setting::put(['bundle_price' => 320]);
    bundleBooking($this->civil);
    expect((float) bundleBooking($this->cpr)->price)->toBe(120.0);

    $this->tactical->update(['is_bundled' => true]);
    app()->forgetScopedInstances();
    expect(app(BundlePricing::class)->courses())->toBeEmpty()
        ->and((float) bundleBooking($this->cpr)->price)->toBe(200.0);
});

it('returns the second workshop to the standard price when the first one is cancelled', function () {
    $first = bundleBooking($this->civil);
    $second = bundleBooking($this->cpr);

    app(CancelBooking::class)->handle($first, notify: false);

    expect((float) $second->fresh()?->price)->toBe(200.0)
        ->and($second->fresh()?->bundle_booking_id)->toBeNull();
});

it('pairs the second workshop with another booking of the same customer when the first one is cancelled', function () {
    $first = bundleBooking($this->civil);
    $second = bundleBooking($this->cpr);
    $another = Booking::factory()->for($this->civil)->create(['phone' => '0905 111 222', 'date' => '2027-01-15']);

    app(CancelBooking::class)->handle($first, notify: false);

    expect((float) $second->fresh()?->price)->toBe(100.0)
        ->and($second->fresh()?->bundle_booking_id)->toBe($another->id);
});

it('keeps the first workshop price when the discounted one is cancelled', function () {
    $first = bundleBooking($this->civil);
    $second = bundleBooking($this->cpr);

    app(CancelBooking::class)->handle($second, notify: false);

    expect((float) $first->fresh()?->price)->toBe(200.0)
        ->and($first->fresh()?->bundleCounterpart())->toBeNull();
});

it('normalises Slovak phone numbers to the last nine digits', function (string $phone) {
    expect(BundlePricing::normalizePhone($phone))->toBe('905111222');
})->with(['+421 905 111 222', '0905 111 222', '00421905111222', '905-111-222']);

it('shows the bundle price on the confirmation page and in the e-mails', function () {
    bundleBooking($this->civil);
    Mail::fake();

    $this->post(route('booking.store'), [
        'course_id' => $this->cpr->id,
        'date' => '2026-12-21',
        'time_slot' => 'afternoon',
        'contact_name' => 'Jana Malá',
        'email' => 'jana@example.com',
        'phone' => '+421 905 111 222',
        'address' => 'Hlavná 1, Bratislava',
        'participants' => 2,
        'consent' => '1',
    ])->assertRedirect();

    $booking = Booking::query()->where('course_id', $this->cpr->id)->sole();

    $this->get(URL::signedRoute('booking.show', $booking))
        ->assertOk()
        ->assertSee(bundleEur(100))
        ->assertSeeInOrder([__('booking.summary.price'), __('booking.summary.bundle_price')])
        ->assertDontSee('data-testid="bundle-offer"', escape: false);

    Mail::assertQueued(BookingConfirmedMail::class, fn (BookingConfirmedMail $mail): bool => str_contains($mail->render(), __('booking.summary.bundle_price'))
        && ! str_contains($mail->render(), 'Tip:'));
    Mail::assertQueued(NewBookingAdminMail::class, fn (NewBookingAdminMail $mail): bool => str_contains(
        $mail->render(),
        __('mail.new_booking_admin.bundle', ['reference' => $booking->bundlePartner?->reference]),
    ));
});

it('offers the other bundled workshop after booking the first one', function () {
    $booking = bundleBooking($this->civil);

    $this->get(URL::signedRoute('booking.show', $booking))
        ->assertOk()
        ->assertSee('data-testid="bundle-offer"', escape: false)
        ->assertSee(__('booking.confirmation.bundle_offer_title', ['course' => 'KPR a AED', 'price' => bundleEur(100)]))
        ->assertSee(route('booking.create', ['workshop' => $this->cpr->slug]), escape: false);

    Mail::assertQueued(BookingConfirmedMail::class, fn (BookingConfirmedMail $mail): bool => str_contains($mail->render(), 'Tip:')
        && str_contains($mail->render(), route('booking.create', ['workshop' => $this->cpr->slug])));

    $tactical = bundleBooking($this->tactical);
    $this->get(URL::signedRoute('booking.show', $tactical))->assertDontSee('data-testid="bundle-offer"', escape: false);
});

it('presents the bundle on the home page, course cards, workshop detail and booking form', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('data-testid="bundle-offer"', escape: false)
        ->assertSee(__('site.bundle.title', ['price' => bundleEur(300)]))
        ->assertSee('<s>'.bundleEur(400).'</s>', escape: false)
        ->assertSee('data-testid="course-bundle-note"', escape: false);

    $this->get(route('workshops.show', $this->civil))
        ->assertOk()
        ->assertSee(__('site.bundle.detail_value', ['course' => 'KPR a AED', 'price' => bundleEur(300), 'second_price' => bundleEur(100)]));

    $this->get(route('workshops.show', $this->tactical))
        ->assertOk()
        ->assertDontSee(__('site.bundle.detail_label'));

    $this->get(route('booking.create', ['workshop' => $this->cpr->slug]))
        ->assertOk()
        ->assertSee(__('booking.summary.bundle_note', ['course' => 'Civilná prvá pomoc', 'price' => bundleEur(100)]));
});

it('hides the bundle when it is not set up', function () {
    $this->civil->update(['is_bundled' => false]);

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('data-testid="bundle-offer"', escape: false)
        ->assertDontSee('data-testid="course-bundle-note"', escape: false);
});
