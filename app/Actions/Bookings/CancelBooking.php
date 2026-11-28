<?php

namespace App\Actions\Bookings;

use App\Enums\BookingStatus;
use App\Mail\BookingCancelledMail;
use App\Models\Booking;
use App\Support\BundlePricing;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;

/**
 * Cancels a booking, frees its slot and informs the customer.
 *
 * Upcoming bookings that had the bundle price thanks to the cancelled one are paired with another
 * booking of the same contact if there is one, otherwise they go back to the standard price.
 */
final class CancelBooking
{
    public function __construct(private readonly BundlePricing $bundlePricing) {}

    public function handle(Booking $booking, ?string $reason = null, bool $notify = true): Booking
    {
        if (! $booking->isCancellable()) {
            throw new InvalidArgumentException('Only confirmed bookings can be cancelled.');
        }

        DB::transaction(function () use ($booking, $reason): void {
            $booking->update([
                'status' => BookingStatus::Cancelled,
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
            ]);

            $this->repriceBundledBookings($booking);
        });

        if ($notify) {
            Mail::to($booking->email)->queue(new BookingCancelledMail($booking));
        }

        return $booking;
    }

    /**
     * @return Collection<int, Booking> the upcoming bookings whose bundle price depended on this one
     */
    public function dependentBookings(Booking $booking): Collection
    {
        return $booking->bundledBookings()->where('status', BookingStatus::Confirmed)->with('course')->get();
    }

    private function repriceBundledBookings(Booking $booking): void
    {
        foreach ($this->dependentBookings($booking) as $dependent) {
            $partner = $this->bundlePricing->findPartnerBooking($dependent->course, $dependent->email, $dependent->phone, except: $dependent);

            $dependent->update([
                'bundle_booking_id' => $partner?->id,
                'price' => $partner !== null ? $this->bundlePricing->discountedPrice($dependent->course) : $dependent->course->price,
            ]);
        }
    }
}
