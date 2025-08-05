<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => '+55' . fake()->numerify('###########'),
            'email' => fake()->email(),
            'cpf' => fake()->numerify('###.###.###-##'),
            'social_name' => fake()->optional()->name(),
            'birthdate' => fake()->optional()->date(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'gender' => fake()->randomElement(['male', 'female', 'other']),
            'age' => fake()->numberBetween(18, 80),
            'tags' => json_encode(fake()->words(3)),
        ];
    }
}
