<?php

namespace Database\Seeders;

use App\Models\CategorySermon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySermonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Doctrine',
            'Foi',
            'Espérance',
            'Amour',
            'Prière',
            'Louange',
            'Saint-Esprit',
            'Guérison',
            'Famille',
            'Jeunesse',
            'Finances',
            'Leadership',
            'Évangélisation',
            'Discipulat',
            'Mariage',
            'Sainteté',
            'Mission',
            'Bénédiction',
            'Paix',
            'Courage',
        ];

        foreach ($categories as $name) {
            CategorySermon::firstOrCreate(['name' => $name]);
        }
    }
}
