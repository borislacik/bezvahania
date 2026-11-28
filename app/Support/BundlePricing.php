<?php

namespace App\Support;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Course;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Bundle of two workshops (KPR + civilná prvá pomoc) for a joint price (setting `bundle_price`).
 *
 * The first booking of a bundled course costs the standard price, the booking of the other bundled
 * course by the same contact (e-mail or phone) costs the rest of the bundle price. The order and the
 * time between the two bookings do not matter. Each booking can be part of one bundle only.
 */
final class BundlePricing
{
    /** @var Collection<int, Course>|null */
    private ?Collection $courses = null;

    /**
     * The bundled courses; the bundle only works with exactly two active ones.
     *
     * @return Collection<int, Course>
     */
    public function courses(): Collection
    {
        if ($this->courses === null) {
            $courses = Course::query()->active()->where('is_bundled', true)->get();
            $this->courses = $courses->count() === 2 ? $courses : new Collection;
        }

        return $this->courses;
    }

    public function isBundled(Course $course): bool
    {
        return $this->courses()->contains('id', $course->id);
    }

    public function partnerCourse(Course $course): ?Course
    {
        if (! $this->isBundled($course)) {
            return null;
        }

        return $this->courses()->first(fn (Course $other): bool => $other->id !== $course->id);
    }

    public function bundlePrice(): float
    {
        return (float) Setting::get('bundle_price');
    }

    /**
     * Price of the course when the other bundled course is already booked or attended.
     */
    public function discountedPrice(Course $course): ?float
    {
        $partner = $this->partnerCourse($course);

        return $partner === null ? null : max(0.0, $this->bundlePrice() - (float) $partner->price);
    }

    /**
     * A booking of the other bundled course by the same contact that is not yet part of a bundle.
     */
    public function findPartnerBooking(Course $course, string $email, string $phone, ?Booking $except = null): ?Booking
    {
        $partnerCourse = $this->partnerCourse($course);

        if ($partnerCourse === null) {
            return null;
        }

        $email = mb_strtolower(trim($email));
        $phone = self::normalizePhone($phone);
        $countingStatuses = [BookingStatus::Confirmed, BookingStatus::Completed];

        return Booking::query()
            ->where('course_id', $partnerCourse->id)
            ->whereIn('status', $countingStatuses)
            ->whereNull('anonymized_at')
            ->whereNull('bundle_booking_id')
            ->whereDoesntHave('bundledBookings', fn (Builder $query): Builder => $query->whereIn('status', $countingStatuses))
            ->when($except !== null, fn (Builder $query): Builder => $query->whereKeyNot($except?->id))
            ->orderBy('date')
            ->get()
            ->first(fn (Booking $booking): bool => ($email !== '' && mb_strtolower(trim($booking->email)) === $email)
                || ($phone !== '' && self::normalizePhone($booking->phone) === $phone));
    }

    /**
     * Compares Slovak numbers regardless of the format: "+421 915 918 386", "0915918386" and
     * "00421915918386" are the same number (the last nine digits).
     */
    public static function normalizePhone(string $phone): string
    {
        $digits = (string) preg_replace('/\D+/', '', $phone);

        return strlen($digits) > 9 ? substr($digits, -9) : $digits;
    }
}
