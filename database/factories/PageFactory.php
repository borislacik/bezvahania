<?php

namespace Database\Factories;

use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->randomElement(array_keys(Page::KEYS)),
            'title' => fake()->sentence(3),
            'seo_title' => fake()->sentence(5),
            'seo_description' => fake()->sentence(15),
            'og_image' => null,
            'content' => null,
        ];
    }
}
