<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserType;

class UserTypeSeeder extends Seeder
{
    public function run()
    {
        // ID=1: Administrateur
        UserType::create([
            'name' => 'Administrateur', 
            'description' => 'Accès complet au système avec tous les privilèges'
        ]);
        
        // ID=2: Utilisateur normal
        UserType::create([
            'name' => 'Utilisateur', 
            'description' => 'Accès limité aux fonctionnalités de base'
        ]);
    }
}