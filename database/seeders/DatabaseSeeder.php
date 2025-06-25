<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seeder principal pour initialiser la base de données
     * Ordre d'exécution important : Types, Statuts, puis Paramètres
     */
    public function run(): void
    {
        $this->command->info('🚀 Initialisation complète de la base de données...');
        $this->command->info('');
       
        $this->call([
            UserTypeSeeder::class,  // 1. D'abord les types (Admin, Ecran, Accueil, Conseiller)
            StatusSeeder::class,    // 2. Puis les statuts (Inactif, Actif, Suspendu)
            SettingSeeder::class,   // 3. Enfin les paramètres système
        ]);
       
        $this->command->info('');
        $this->command->info('✅ Base de données initialisée avec succès !');
        $this->command->info('📋 Résumé complet :');
        $this->command->info('   • 4 types d\'utilisateurs créés');
        $this->command->info('   • 3 statuts créés');
        $this->command->info('   • Paramètres système configurés');
        $this->command->info('');
        $this->command->info('🎯 Système prêt pour la création d\'utilisateurs et la configuration !');
        $this->command->info('🔧 Accédez aux paramètres via le menu Admin > Paramètres');
    }
}