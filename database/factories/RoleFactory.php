<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $roleName = $this->faker->unique()->randomElement(['Admin', 'User', 'Moderator', 'Editor', 'Viewer', 'Manager', 'Developer', 'Designer', 'Tester', 'Support']);

        return [
            'name' => $roleName,
            'slug' => strtolower(str_replace(' ', '-', $roleName)) . '-' . $this->faker->unique()->numberBetween(1, 1000),
            'description' => $this->faker->sentence(),
            'is_active' => $this->faker->boolean(80), // 80% de chances d'être actif
        ];
    }
}
