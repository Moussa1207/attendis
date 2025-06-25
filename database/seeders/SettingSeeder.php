<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    /**
     * Seed the settings table with default values
     */
    public function run(): void
    {
        $this->command->info(' Initialisation des paramètres système...');

        // Désactiver les contraintes de clés étrangères temporairement
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Vider la table pour repartir à zéro
        Setting::truncate();

        // Paramètres de gestion des utilisateurs
        $userManagementSettings = [
            [
                'key' => Setting::AUTO_DETECT_ADVISORS,
                'value' => '1',
                'type' => 'boolean',
                'group' => 'user_management',
                'label' => 'Détection automatique des conseillers',
                'description' => 'Lorsqu\'activée, cette option permet de détecter automatiquement les conseillers disponibles dès leur connexion à la plateforme. Cela facilite une meilleure répartition des services.',
                'meta' => json_encode([
                    'default' => true,
                    'requires_restart' => false
                ]),
                'sort_order' => 1,
                'is_active' => true
            ],
            [
                'key' => Setting::AUTO_ASSIGN_SERVICES,
                'value' => '1',
                'type' => 'boolean',
                'group' => 'user_management',
                'label' => 'Attribution automatique de tous les services à tous les conseillers',
                'description' => 'Si cette option est activée, chaque conseiller aura accès à tous les services par défaut. Sinon, les services devront être attribués manuellement.',
                'meta' => json_encode([
                    'default' => true,
                    'requires_restart' => false
                ]),
                'sort_order' => 2,
                'is_active' => true
            ],
            [
                'key' => Setting::ENABLE_SESSION_CLOSURE,
                'value' => '0',
                'type' => 'boolean',
                'group' => 'user_management',
                'label' => 'Fermeture automatique des sessions',
                'description' => 'Permet d\'activer la fermeture automatique des sessions utilisateurs après une certaine heure.',
                'meta' => json_encode([
                    'default' => false,
                    'requires_restart' => false,
                    'depends_on' => 'auto_session_closure_time'
                ]),
                'sort_order' => 3,
                'is_active' => true
            ],
            [
                'key' => Setting::SESSION_CLOSURE_TIME,
                'value' => '18:00',
                'type' => 'time',
                'group' => 'user_management',
                'label' => 'Heure de fermeture automatique',
                'description' => 'Heure à laquelle toutes les sessions utilisateurs seront automatiquement fermées (format 24h).',
                'meta' => json_encode([
                    'default' => '18:00',
                    'format' => 'HH:mm',
                    'validation' => '^([01]?[0-9]|2[0-3]):[0-5][0-9]$'
                ]),
                'sort_order' => 4,
                'is_active' => true
            ]
        ];

        // Paramètres de sécurité
        $securitySettings = [
            [
                'key' => Setting::MAX_CONCURRENT_SESSIONS,
                'value' => '3',
                'type' => 'integer',
                'group' => 'security',
                'label' => 'Sessions simultanées maximales',
                'description' => 'Nombre maximum de sessions simultanées autorisées par utilisateur.',
                'meta' => json_encode([
                    'default' => 3,
                    'min' => 1,
                    'max' => 10
                ]),
                'sort_order' => 1,
                'is_active' => true
            ],
            [
                'key' => Setting::SESSION_TIMEOUT_MINUTES,
                'value' => '120',
                'type' => 'integer',
                'group' => 'security',
                'label' => 'Timeout des sessions (minutes)',
                'description' => 'Durée d\'inactivité après laquelle une session expire automatiquement.',
                'meta' => json_encode([
                    'default' => 120,
                    'min' => 30,
                    'max' => 1440
                ]),
                'sort_order' => 2,
                'is_active' => true
            ],
            [
                'key' => Setting::MAX_LOGIN_ATTEMPTS,
                'value' => '5',
                'type' => 'integer',
                'group' => 'security',
                'label' => 'Tentatives de connexion maximales',
                'description' => 'Nombre maximum de tentatives de connexion échouées avant verrouillage du compte.',
                'meta' => json_encode([
                    'default' => 5,
                    'min' => 3,
                    'max' => 10
                ]),
                'sort_order' => 3,
                'is_active' => true
            ],
            [
                'key' => Setting::LOCKOUT_DURATION_MINUTES,
                'value' => '30',
                'type' => 'integer',
                'group' => 'security',
                'label' => 'Durée de verrouillage (minutes)',
                'description' => 'Durée pendant laquelle un compte reste verrouillé après trop de tentatives échouées.',
                'meta' => json_encode([
                    'default' => 30,
                    'min' => 5,
                    'max' => 1440
                ]),
                'sort_order' => 4,
                'is_active' => true
            ]
        ];

        // Paramètres généraux du système
        $generalSettings = [
            [
                'key' => 'app_name',
                'value' => 'Attendis',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Nom de l\'application',
                'description' => 'Nom affiché dans l\'interface utilisateur.',
                'meta' => json_encode([
                    'default' => 'Attendis',
                    'max_length' => 50
                ]),
                'sort_order' => 1,
                'is_active' => true
            ],
            [
                'key' => 'app_version',
                'value' => '1.0.0',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Version de l\'application',
                'description' => 'Version actuelle du système.',
                'meta' => json_encode([
                    'default' => '1.0.0',
                    'readonly' => true
                ]),
                'sort_order' => 2,
                'is_active' => true
            ],
            [
                'key' => 'maintenance_mode',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'general',
                'label' => 'Mode maintenance',
                'description' => 'Activer le mode maintenance pour bloquer l\'accès aux utilisateurs normaux.',
                'meta' => json_encode([
                    'default' => false,
                    'requires_restart' => true
                ]),
                'sort_order' => 3,
                'is_active' => true
            ],
            [
                'key' => 'debug_mode',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'general',
                'label' => 'Mode debug',
                'description' => 'Activer le mode debug pour afficher des informations détaillées d\'erreur.',
                'meta' => json_encode([
                    'default' => false,
                    'requires_restart' => true,
                    'warning' => 'Ne jamais activer en production'
                ]),
                'sort_order' => 4,
                'is_active' => true
            ]
        ];

        // Paramètres de notification
        $notificationSettings = [
            [
                'key' => 'email_notifications',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'notifications',
                'label' => 'Notifications par email',
                'description' => 'Activer l\'envoi de notifications par email.',
                'meta' => json_encode([
                    'default' => true
                ]),
                'sort_order' => 1,
                'is_active' => true
            ],
            [
                'key' => 'admin_email',
                'value' => 'admin@attendis.com',
                'type' => 'string',
                'group' => 'notifications',
                'label' => 'Email administrateur',
                'description' => 'Adresse email pour recevoir les notifications administrateur.',
                'meta' => json_encode([
                    'default' => 'admin@attendis.com',
                    'validation' => 'email'
                ]),
                'sort_order' => 2,
                'is_active' => true
            ]
        ];

        // Combiner tous les paramètres
        $allSettings = array_merge(
            $userManagementSettings,
            $securitySettings,
            $generalSettings,
            $notificationSettings
        );

        // Insérer tous les paramètres
        foreach ($allSettings as $setting) {
            Setting::create($setting);
        }

        // Réactiver les contraintes
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Messages de confirmation
        $this->command->info('    Paramètres système créés avec succès :');
        $this->command->info('    Gestion utilisateurs : ' . count($userManagementSettings) . ' paramètres');
        $this->command->info('    Sécurité : ' . count($securitySettings) . ' paramètres');
        $this->command->info('    Général : ' . count($generalSettings) . ' paramètres');
        $this->command->info('    Notifications : ' . count($notificationSettings) . ' paramètres');

        // Vérification
        $totalCount = Setting::count();
        $this->command->info(" Total: {$totalCount} paramètres créés");

        // Afficher les groupes créés
        $groups = Setting::distinct('group')->pluck('group')->toArray();
        $this->command->info('  Groupes : ' . implode(', ', $groups));
    }
}