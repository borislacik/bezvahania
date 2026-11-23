<?php

namespace Database\Factories;

use App\Models\TeamMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeamMember>
 */
class TeamMemberFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'role' => 'Záchranár a lektor',
            'quote' => fake()->sentence(12),
            'bio' => fake()->paragraph(),
            'photo' => null,
            'is_active' => true,
            'sort_order' => 1,
        ];
    }
}
