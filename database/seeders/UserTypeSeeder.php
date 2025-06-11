<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserType;
use Illuminate\Support\Facades\DB;

class UserTypeSeeder extends Seeder
{
    /**
     * Créer les 4 types d'utilisateurs métier
     */
    public function run()
    {
        // Désactiver les contraintes de clés étrangères temporairement
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Vider la table pour repartir à zéro
        UserType::truncate();
        
        // Créer les 4 types métier avec IDs forcés
        $userTypes = [
            [
                'id' => 1,
                'name' => 'Administrateur',
                'description' => 'Accès complet au système avec tous les privilèges administrateur'
            ],
            [
                'id' => 2,
                'name' => 'Poste Ecran',
                'description' => 'Interface utilisateur pour affichage et consultation des données'
            ],
            [
                'id' => 3,
                'name' => 'Poste Accueil',
                'description' => 'Poste de réception et orientation des visiteurs'
            ],
            [
                'id' => 4,
                'name' => 'Poste Conseiller',
                'description' => 'Poste de support et assistance client'
            ]
        ];

        // Insérer les types en forçant les IDs
        foreach ($userTypes as $type) {
            UserType::create($type);
        }

        // Réactiver les contraintes
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Messages de confirmation
        $this->command->info('✅ Types d\'utilisateurs créés avec succès :');
        $this->command->info('   1 - Administrateur');
        $this->command->info('   2 - Poste Ecran');
        $this->command->info('   3 - Poste Accueil');
        $this->command->info('   4 - Poste Conseiller');
        
        // Vérification
        $count = UserType::count();
        $this->command->info("📊 Total: {$count} types d'utilisateurs");
    }
}