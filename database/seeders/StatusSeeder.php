<?php
// database/seeders/StatusSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Status;

class StatusSeeder extends Seeder
{
    public function run()
    {
        Status::create(['name' => 'Actif', 'description' => 'Compte utilisateur actif']);
        Status::create(['name' => 'Inactif', 'description' => 'Compte utilisateur inactif']);
        Status::create(['name' => 'Suspendu', 'description' => 'Compte utilisateur suspendu']);
    }
}