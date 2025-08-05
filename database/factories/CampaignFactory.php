<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Campaign>
 */
class CampaignFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'message_text' => fake()->paragraph() . ' {nome}, ' . fake()->sentence(),
            'image_url' => fake()->optional()->imageUrl(),
            'attachment_url' => fake()->optional()->url(),
            'link_url' => fake()->optional()->url(),
            'status' => fake()->randomElement(['draft', 'active', 'paused', 'completed']),
            'scheduled_at' => fake()->optional()->dateTimeBetween('now', '+1 month'),
            'created_by' => 1,
            'total_contacts' => fake()->numberBetween(10, 1000),
            'sent_count' => fake()->numberBetween(0, 100),
            'failed_count' => fake()->numberBetween(0, 10),
        ];
    }
}
