<?php

namespace Database\Factories;

use App\Models\BlockedDay;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BlockedDay>
 */
class BlockedDayFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => fake()->unique()->dateTimeBetween('+3 days', '+50 days')->format('Y-m-d'),
            'reason' => 'Dovolenka',
        ];
    }
}
