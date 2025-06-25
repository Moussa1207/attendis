<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Setting;
use App\Helpers\SettingsHelper;

class SettingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'settings:manage 
                            {action : Action to perform (list, get, set, reset, clear-cache, check)}
                            {key? : Setting key (for get/set actions)}
                            {value? : Setting value (for set action)}
                            {--type=string : Data type for set action (string, boolean, integer, time)}
                            {--group=general : Group for set action}';

    /**
     * The console command description.
     */
    protected $description = 'Manage system settings from command line';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');
        
        switch ($action) {
            case 'list':
                return $this->listSettings();
            case 'get':
                return $this->getSetting();
            case 'set':
                return $this->setSetting();
            case 'reset':
                return $this->resetSettings();
            case 'clear-cache':
                return $this->clearCache();
            case 'check':
                return $this->checkSettings();
            default:
                $this->error("Action inconnue: {$action}");
                $this->info('Actions disponibles: list, get, set, reset, clear-cache, check');
                return 1;
        }
    }

    /**
     * Lister tous les paramètres
     */
    private function listSettings()
    {
        $this->info('📋 Liste des paramètres système');
        $this->line('');

        $settings = Setting::all()->groupBy('group');

        foreach ($settings as $group => $groupSettings) {
            $this->info("🏷️  Groupe: {$group}");
            
            $headers = ['Clé', 'Valeur', 'Type', 'Description'];
            $rows = [];
            
            foreach ($groupSettings as $setting) {
                $rows[] = [
                    $setting->key,
                    $this->formatValue($setting->value, $setting->type),
                    $setting->type,
                    str_limit($setting->description ?? '', 50)
                ];
            }
            
            $this->table($headers, $rows);
            $this->line('');
        }

        $this->info('Total: ' . Setting::count() . ' paramètres');
        
        return 0;
    }

    /**
     * Obtenir un paramètre spécifique
     */
    private function getSetting()
    {
        $key = $this->argument('key');
        
        if (!$key) {
            $this->error('Veuillez spécifier une clé de paramètre');
            return 1;
        }

        $setting = Setting::where('key', $key)->first();
        
        if (!$setting) {
            $this->error("Paramètre '{$key}' non trouvé");
            return 1;
        }

        $this->info("📄 Paramètre: {$key}");
        $this->line('');
        $this->info("Valeur: {$this->formatValue($setting->value, $setting->type)}");
        $this->info("Type: {$setting->type}");
        $this->info("Groupe: {$setting->group}");
        $this->info("Actif: " . ($setting->is_active ? 'Oui' : 'Non'));
        
        if ($setting->description) {
            $this->info("Description: {$setting->description}");
        }
        
        return 0;
    }

    /**
     * Définir un paramètre
     */
    private function setSetting()
    {
        $key = $this->argument('key');
        $value = $this->argument('value');
        $type = $this->option('type');
        $group = $this->option('group');
        
        if (!$key || $value === null) {
            $this->error('Veuillez spécifier une clé et une valeur');
            return 1;
        }

        // Validation selon le type
        if (!$this->validateValue($value, $type)) {
            return 1;
        }

        try {
            $success = Setting::set($key, $value, $type, $group);
            
            if ($success) {
                $this->info("✅ Paramètre '{$key}' mis à jour avec succès");
                $this->info("Valeur: {$this->formatValue($value, $type)}");
                $this->info("Type: {$type}");
                $this->info("Groupe: {$group}");
            } else {
                $this->error("❌ Erreur lors de la mise à jour du paramètre");
                return 1;
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Erreur: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }

    /**
     * Réinitialiser tous les paramètres
     */
    private function resetSettings()
    {
        if (!$this->confirm('⚠️  Voulez-vous vraiment réinitialiser TOUS les paramètres aux valeurs par défaut ?')) {
            $this->info('Opération annulée');
            return 0;
        }

        $this->info('🔄 Réinitialisation des paramètres...');
        
        try {
            $success = Setting::resetToDefaults();
            
            if ($success) {
                $this->info('✅ Tous les paramètres ont été réinitialisés');
                $this->info('📊 Total: ' . Setting::count() . ' paramètres recréés');
            } else {
                $this->error('❌ Erreur lors de la réinitialisation');
                return 1;
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Erreur: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }

    /**
     * Vider le cache des paramètres
     */
    private function clearCache()
    {
        $this->info('🗑️  Vidage du cache des paramètres...');
        
        try {
            Setting::clearCache();
            $this->info('✅ Cache des paramètres vidé avec succès');
            
        } catch (\Exception $e) {
            $this->error("❌ Erreur: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }

    /**
     * Vérifier la cohérence des paramètres
     */
    private function checkSettings()
    {
        $this->info('🔍 Vérification de la cohérence des paramètres...');
        $this->line('');

        // Utiliser le helper pour vérifier la cohérence
        $check = SettingsHelper::checkConsistency();
        
        if ($check['consistent']) {
            $this->info('✅ Tous les paramètres sont cohérents');
        } else {
            $this->error('❌ Problèmes détectés:');
            foreach ($check['issues'] as $issue) {
                $this->error("  • {$issue}");
            }
        }

        $this->line('');

        // Afficher les statistiques
        $stats = SettingsHelper::getStats();
        $this->info('📊 Statistiques:');
        $this->info("  • Total: {$stats['total_settings']} paramètres");
        $this->info("  • Actifs: {$stats['active_settings']} paramètres");
        $this->info("  • Groupes: " . implode(', ', $stats['groups']));
        $this->info("  • Types: " . implode(', ', $stats['types']));

        return $check['consistent'] ? 0 : 1;
    }

    /**
     * Formater une valeur pour l'affichage
     */
    private function formatValue($value, $type)
    {
        switch ($type) {
            case 'boolean':
                return $value ? 'true' : 'false';
            case 'json':
            case 'array':
                return json_encode($value, JSON_PRETTY_PRINT);
            default:
                return $value;
        }
    }

    /**
     * Valider une valeur selon son type
     */
    private function validateValue($value, $type): bool
    {
        switch ($type) {
            case 'boolean':
                if (!in_array(strtolower($value), ['true', 'false', '1', '0', 'yes', 'no'])) {
                    $this->error("Valeur booléenne invalide. Utilisez: true, false, 1, 0, yes, no");
                    return false;
                }
                break;
                
            case 'integer':
                if (!is_numeric($value)) {
                    $this->error("Valeur numérique requise pour le type integer");
                    return false;
                }
                break;
                
            case 'time':
                if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $value)) {
                    $this->error("Format heure invalide. Utilisez le format HH:MM (24h)");
                    return false;
                }
                break;
                
            case 'json':
                if (!json_decode($value)) {
                    $this->error("JSON invalide");
                    return false;
                }
                break;
        }
        
        return true;
    }
}