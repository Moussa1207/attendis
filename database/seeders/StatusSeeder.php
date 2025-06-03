<?php
// database/seeders/StatusSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Status;

class StatusSeeder extends Seeder
{
    public function run()
    {
        // ID=1: Inactif
        Status::create([
            'name' => 'Inactif', 
            'description' => 'Compte utilisateur inactif en attente d\'activation'
        ]);
        
        // ID=2: Actif
        Status::create([
            'name' => 'Actif', 
            'description' => 'Compte utilisateur actif et opérationnel'
        ]);
        
        // ID=3: Suspendu
        Status::create([
            'name' => 'Suspendu', 
            'description' => 'Compte utilisateur suspendu temporairement'
        ]);
    }
}