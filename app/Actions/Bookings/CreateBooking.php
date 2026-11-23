<?php

namespace App\Actions\Bookings;

use App\Enums\BookingStatus;
use App\Enums\TimeSlot;
use App\Exceptions\SlotUnavailableException;
use App\Mail\BookingConfirmedMail;
use App\Mail\NewBookingAdminMail;
use App\Models\Booking;
use App\Models\Course;
use App\Models\Setting;
use App\Support\Availability;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Creates a confirmed booking for a whole slot. Used by the public booking form and by the admin.
 *
 * Double booking is prevented by the unique `active_slot` index; the availability check before
 * the insert only produces a friendly error in the common case.
 */
final class CreateBooking
{
    public const CONSENT_VERSION = '2026-11-23';

    /**
     * @param  array{course_id: int, date: string, time_slot: string, contact_name: string, email: string, phone: string, address: string, participants: int, note?: string|null}  $data
     *
     * @throws SlotUnavailableException
     */
    public function handle(array $data, bool $checkWindow = true, bool $notify = true): Booking
    {
        $course = Course::query()->findOrFail($data['course_id']);
        $date = Carbon::parse($data['date'])->startOfDay();
        $slot = TimeSlot::from($data['time_slot']);

        if ($checkWindow && ! (new Availability)->isBookable($date, $slot)) {
            throw new SlotUnavailableException;
        }

        try {
            $booking = DB::transaction(fn (): Booking => Booking::query()->create([
                'course_id' => $course->id,
                'date' => $date,
                'time_slot' => $slot,
                'status' => BookingStatus::Confirmed,
                'contact_name' => $data['contact_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'address' => $data['address'],
                'participants' => $data['participants'],
                'note' => $data['note'] ?? null,
                'price' => $course->price,
                'consent_at' => now(),
                'consent_version' => self::CONSENT_VERSION,
            ]));
        } catch (UniqueConstraintViolationException) {
            throw new SlotUnavailableException;
        }

        if ($notify) {
            Mail::to($booking->email)->queue(new BookingConfirmedMail($booking));
            Mail::to(Setting::get('notification_email'))->queue(new NewBookingAdminMail($booking));
        }

        return $booking;
    }
}
