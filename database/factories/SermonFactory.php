<?php

namespace Database\Factories;

use App\Models\Church;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sermon>
 */
class SermonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $titles = [
            'La grâce de Dieu',
            'L\'amour sans condition',
            'La foi qui déplace les montagnes',
            'La puissance de la prière',
            'Vivre selon l\'Esprit',
            'L\'espérance en Christ',
            'La paix de Dieu',
            'Marcher dans la lumière',
            'Le pardon des péchés',
            'La joie du salut'
        ];

        $preachers = [
            'Pasteur Jean MUKENDI',
            'Pasteur Sarah MBUYI',
            'Évangéliste Paul KASONGA',
            'Pasteur Marie KALALA',
            'Pasteur David TSHIAMUA',
            'Pasteur Grace NGOY',
            'Pasteur Pierre MWAMBA',
            'Pasteur Ruth KABONGO'
        ];

        return [
            'title' => $this->faker->randomElement($titles),
            'preacher_name' => $this->faker->randomElement($preachers),
            'description' => $this->faker->optional()->paragraph(),
            'audio_url' => $this->faker->lexify('sermons/audio/???????????_sermon.mp3'),
            'cover_url' => $this->faker->optional()->lexify('sermons/covers/???????????_cover.jpg'),
            'duration' => $this->faker->numberBetween(600, 3600), // Entre 10 minutes et 1 heure
            'church_id' => \App\Models\Church::factory(),
        ];
    }
}
