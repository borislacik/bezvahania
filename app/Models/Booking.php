<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\TimeSlot;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $reference
 * @property int $course_id
 * @property Carbon $date
 * @property TimeSlot $time_slot
 * @property BookingStatus $status
 * @property string|null $active_slot
 * @property string $contact_name
 * @property string $email
 * @property string $phone
 * @property string $address
 * @property int $participants
 * @property string|null $note
 * @property string $price
 * @property Carbon|null $consent_at
 * @property string|null $consent_version
 * @property Carbon|null $cancelled_at
 * @property string|null $cancellation_reason
 * @property Carbon|null $anonymized_at
 * @property Carbon $created_at
 * @property-read Course $course
 */
class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    protected $fillable = [
        'reference', 'course_id', 'date', 'time_slot', 'status', 'contact_name', 'email', 'phone', 'address',
        'participants', 'note', 'price', 'consent_at', 'consent_version', 'cancelled_at', 'cancellation_reason',
        'anonymized_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'time_slot' => TimeSlot::class,
            'status' => BookingStatus::class,
            'participants' => 'integer',
            'price' => 'decimal:2',
            'consent_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'anonymized_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Booking $booking): void {
            $booking->reference ??= self::newReference();
        });

        // Keep the unique slot key in sync with the status (see the bookings migration).
        static::saving(function (Booking $booking): void {
            $booking->active_slot = $booking->status->occupiesSlot()
                ? self::slotKey($booking->date, $booking->time_slot)
                : null;
        });
    }

    public static function slotKey(Carbon|string $date, TimeSlot $slot): string
    {
        return Carbon::parse($date)->toDateString().':'.$slot->value;
    }

    public static function newReference(): string
    {
        do {
            $reference = 'BV-'.Str::upper(Str::random(6));
        } while (self::query()->where('reference', $reference)->exists());

        return $reference;
    }

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    /**
     * @return BelongsTo<Course, $this>
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeUpcoming(Builder $query): void
    {
        $query->where('status', BookingStatus::Confirmed)
            ->whereDate('date', '>=', today())
            ->orderBy('date')
            ->orderBy('time_slot');
    }

    public function startsAt(): Carbon
    {
        return $this->date->copy()->setTimeFromTimeString($this->time_slot->startsAt());
    }

    public function isCancellable(): bool
    {
        return $this->status === BookingStatus::Confirmed;
    }
}
