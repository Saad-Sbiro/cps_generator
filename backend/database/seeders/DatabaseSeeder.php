<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PrixCatalogueSeeder::class,
            ArticleSeeder::class,
            ProjetSeeder::class,
        ]);
    }
}
