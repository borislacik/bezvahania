<?php

namespace App\Actions\Bookings;

use App\Enums\BookingStatus;
use App\Mail\BookingCancelledMail;
use App\Models\Booking;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;

/**
 * Cancels a booking, frees its slot and informs the customer.
 */
final class CancelBooking
{
    public function handle(Booking $booking, ?string $reason = null, bool $notify = true): Booking
    {
        if (! $booking->isCancellable()) {
            throw new InvalidArgumentException('Only confirmed bookings can be cancelled.');
        }

        $booking->update([
            'status' => BookingStatus::Cancelled,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);

        if ($notify) {
            Mail::to($booking->email)->queue(new BookingCancelledMail($booking));
        }

        return $booking;
    }
}
