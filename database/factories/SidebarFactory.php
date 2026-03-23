<?php

namespace Database\Factories;

use App\Models\Sidebar;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sidebar>
 */
class SidebarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'team_id' => 1, // Will be overridden in tests
            'name' => $this->faker->words(2, true),
            'title' => $this->faker->sentence(3),
            'content' => $this->faker->paragraphs(2, true),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
