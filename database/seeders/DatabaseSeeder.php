<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    
    public function run(): void
    {
       
        $this->call([
            UserTypeSeeder::class,  // 1. D'abord les types (Admin, User)
            StatusSeeder::class,    // 2. Puis les statuts (Inactif, Actif, Suspendu)
            
        ]);
    }
}