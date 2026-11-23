<?php

namespace Database\Factories;

use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Testimonial>
 */
class TestimonialFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'author' => fake()->name(),
            'text' => fake()->sentence(20),
            'context' => 'Workshop KPR a AED • Bratislava',
            'rating' => 5,
            'is_active' => true,
            'sort_order' => 1,
        ];
    }
}
