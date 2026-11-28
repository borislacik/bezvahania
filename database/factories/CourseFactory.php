<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'Workshop '.fake()->unique()->words(2, true);

        return [
            'slug' => Str::slug($name),
            'name' => $name,
            'badge' => null,
            'short_description' => fake()->sentence(16),
            'description' => '<p>'.fake()->paragraph().'</p>',
            'duration_label' => 'cca 3 hodiny',
            'equipment' => 'Figuríny, cvičný AED',
            'price' => 200,
            'image' => null,
            'is_featured' => false,
            'is_bundled' => false,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 10),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function bundled(): static
    {
        return $this->state(fn () => ['is_bundled' => true]);
    }
}
