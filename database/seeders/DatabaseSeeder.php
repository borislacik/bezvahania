<?php

namespace Database\Seeders;

use App\Enums\TimeSlot;
use App\Models\BlockedDay;
use App\Models\Booking;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Local development data. Production gets only ContentSeeder (php artisan db:seed --class=ContentSeeder).
     */
    public function run(): void
    {
        $this->call(ContentSeeder::class);

        if (! app()->isLocal()) {
            return;
        }

        // Local test admin, see README.md ("Lokálny admin").
        User::query()->updateOrCreate(['email' => 'admin@bezvahania.localhost'], [
            'name' => 'Admin',
            'password' => 'password',
        ]);

        // A few blocked days and bookings so the calendar shows every state.
        foreach ([3, 4, 12] as $offset) {
            BlockedDay::query()->firstOrCreate(['date' => today()->addDays($offset)], ['reason' => 'Dovolenka']);
        }

        if (Booking::query()->doesntExist()) {
            $courses = Course::query()->get();

            foreach ([[5, TimeSlot::Morning], [6, TimeSlot::Afternoon], [8, TimeSlot::Morning], [8, TimeSlot::Afternoon]] as $i => [$offset, $slot]) {
                Booking::factory()->for($courses[$i % $courses->count()])->create([
                    'date' => today()->addDays($offset),
                    'time_slot' => $slot,
                ]);
            }
        }
    }
}
