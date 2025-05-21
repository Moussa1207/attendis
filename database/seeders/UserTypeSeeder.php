<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserType;

class UserTypeSeeder extends Seeder
{
    public function run()
    {
        UserType::create(['name' => 'Administrateur', 'description' => 'Accès complet au système']);
        UserType::create(['name' => 'Utilisateur', 'description' => 'Accès limité au système']);
    }
}