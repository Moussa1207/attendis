<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Ordre important: d'abord les types et statuts, puis les utilisateurs
        $this->call([
            StatusSeeder::class,
            UserTypeSeeder::class,
        ]);
    }
}