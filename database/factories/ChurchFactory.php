<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Church>
 */
class ChurchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company() . ' Church',
            'abbreviation' => strtoupper($this->faker->lexify('???')),
            'description' => $this->faker->paragraph(),
            'logo_url' => $this->faker->imageUrl(200, 200, 'business', true),
            'storage_limit' => \App\Models\Church::DEFAULT_STORAGE_LIMIT,
            // created_by sera défini explicitement dans les tests
        ];
    }
}
