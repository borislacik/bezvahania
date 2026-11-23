<?php

namespace App\Actions\Bookings;

use App\Models\Booking;

/**
 * GDPR: removes personal data from bookings older than the retention period.
 * The booking itself stays (date, course, price, status) for statistics.
 */
final class AnonymizeOldBookings
{
    public const RETENTION_YEARS = 3;

    public function handle(): int
    {
        return Booking::query()
            ->whereNull('anonymized_at')
            ->whereDate('date', '<', now()->subYears(self::RETENTION_YEARS))
            ->update([
                'contact_name' => '—',
                'email' => '—',
                'phone' => '—',
                'address' => '—',
                'note' => null,
                'cancellation_reason' => null,
                'anonymized_at' => now(),
            ]);
    }
}
