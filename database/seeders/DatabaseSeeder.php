<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seeder principal pour initialiser la base de données
     * Ordre d'exécution important : Types puis Statuts
     */
    public function run(): void
    {
        $this->command->info('🚀 Initialisation de la base de données...');
        $this->command->info('');
        
        $this->call([
            UserTypeSeeder::class,  // 1. D'abord les types (Admin, Ecran, Accueil, Conseiller)
            StatusSeeder::class,    // 2. Puis les statuts (Inactif, Actif, Suspendu)
        ]);
        
        $this->command->info('');
        $this->command->info('✅ Base de données initialisée avec succès !');
        $this->command->info('📋 Résumé :');
        $this->command->info('   • 4 types d\'utilisateurs créés');
        $this->command->info('   • 3 statuts créés');
        $this->command->info('');
        $this->command->info('🎯 Prêt pour la création d\'utilisateurs !');
    }
}