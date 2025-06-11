<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Status;
use Illuminate\Support\Facades\DB;

class StatusSeeder extends Seeder
{
    /**
     * Créer les 3 statuts utilisateurs avec IDs forcés
     */
    public function run()
    {
        // Désactiver les contraintes de clés étrangères temporairement
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Vider la table pour repartir à zéro
        Status::truncate();
        
        // Créer les 3 statuts avec IDs forcés
        $statuses = [
            [
                'id' => 1,
                'name' => 'Inactif',
                'description' => 'Compte utilisateur inactif en attente d\'activation par un administrateur'
            ],
            [
                'id' => 2,
                'name' => 'Actif',
                'description' => 'Compte utilisateur actif et opérationnel avec accès complet'
            ],
            [
                'id' => 3,
                'name' => 'Suspendu',
                'description' => 'Compte utilisateur suspendu temporairement par un administrateur'
            ]
        ];

        // Insérer les statuts en forçant les IDs
        foreach ($statuses as $status) {
            Status::create($status);
        }

        // Réactiver les contraintes
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Messages de confirmation
        $this->command->info('✅ Statuts utilisateurs créés avec succès :');
        $this->command->info('   1 - Inactif (en attente d\'activation)');
        $this->command->info('   2 - Actif (opérationnel)');
        $this->command->info('   3 - Suspendu (temporairement bloqué)');
        
        // Vérification
        $count = Status::count();
        $this->command->info("📊 Total: {$count} statuts");
    }
}