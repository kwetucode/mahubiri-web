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
            ['name' => 'Edification'],
            ['name' => 'Etude Biblique'],
            ['name' => 'Mariage'],
            ['name' => 'Sanctification'],
            ['name' => 'Meditation'],
            ['name' => 'Autres'],
        ];
        CategorySermon::insert($categories);
    }
}
