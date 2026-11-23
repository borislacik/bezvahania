<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Enums\TimeSlot;
use App\Models\Booking;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'date' => fake()->dateTimeBetween('+3 days', '+40 days')->format('Y-m-d'),
            'time_slot' => fake()->randomElement(TimeSlot::cases()),
            'status' => BookingStatus::Confirmed,
            'contact_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '+421 9'.fake()->numerify('## ### ###'),
            'address' => fake()->streetAddress().', '.fake()->city(),
            'participants' => fake()->numberBetween(1, 4),
            'note' => null,
            'price' => 200,
            'consent_at' => now(),
            'consent_version' => '2026-11-23',
        ];
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status' => BookingStatus::Cancelled,
            'cancelled_at' => now(),
            'cancellation_reason' => 'Choroba',
        ]);
    }
}
