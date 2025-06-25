<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
        'meta',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'meta' => 'array',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ===============================================
    // CONSTANTES POUR LES CLÉS DE PARAMÈTRES
    // ===============================================
    
    const AUTO_DETECT_ADVISORS = 'auto_detect_available_advisors';
    const AUTO_ASSIGN_SERVICES = 'auto_assign_all_services_to_advisors';
    const ENABLE_SESSION_CLOSURE = 'enable_auto_session_closure';
    const SESSION_CLOSURE_TIME = 'auto_session_closure_time';
    const MAX_CONCURRENT_SESSIONS = 'max_concurrent_sessions';
    const SESSION_TIMEOUT_MINUTES = 'session_timeout_minutes';
    const MAX_LOGIN_ATTEMPTS = 'max_login_attempts';
    const LOCKOUT_DURATION_MINUTES = 'lockout_duration_minutes';

    // ===============================================
    // MÉTHODES STATIQUES PRINCIPALES
    // ===============================================

    /**
     * Obtenir une valeur de paramètre avec cache
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = "setting_{$key}";
        
        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->where('is_active', true)->first();
            
            if (!$setting) {
                return $default;
            }
            
            return self::castValue($setting->value, $setting->type);
        });
    }

    /**
     * Définir une valeur de paramètre et vider le cache
     */
    public static function set(string $key, mixed $value, string $type = 'string', string $group = 'general'): bool
    {
        try {
            $setting = self::updateOrCreate(
                ['key' => $key],
                [
                    'value' => self::prepareValue($value, $type),
                    'type' => $type,
                    'group' => $group,
                    'is_active' => true
                ]
            );

            // Vider le cache pour ce paramètre
            Cache::forget("setting_{$key}");
            
            return true;
        } catch (\Exception $e) {
            \Log::error("Error setting parameter {$key}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtenir plusieurs paramètres d'un groupe avec formatage pour les vues
     */
    public static function getGroupFormatted(string $group): array
    {
        $cacheKey = "settings_group_{$group}";
        
        return Cache::remember($cacheKey, 3600, function () use ($group) {
            $settings = self::where('group', $group)
                          ->where('is_active', true)
                          ->orderBy('sort_order')
                          ->get();

            $formatted = [];
            foreach ($settings as $setting) {
                $formatted[$setting->key] = (object) [
                    'key' => $setting->key,
                    'value' => $setting->value,
                    'formatted_value' => self::castValue($setting->value, $setting->type),
                    'type' => $setting->type,
                    'label' => $setting->label,
                    'description' => $setting->description,
                    'meta' => $setting->meta
                ];
            }
            
            return $formatted;
        });
    }

    // ===============================================
    // MÉTHODES SPÉCIFIQUES POUR VOTRE SYSTÈME
    // ===============================================

    /**
     * Vérifier si la détection automatique des conseillers est activée
     */
    public static function isAutoDetectAdvisorsEnabled(): bool
    {
        return (bool) self::get(self::AUTO_DETECT_ADVISORS, true);
    }

    /**
     * Vérifier si l'attribution automatique des services est activée
     */
    public static function isAutoAssignServicesEnabled(): bool
    {
        return (bool) self::get(self::AUTO_ASSIGN_SERVICES, true);
    }

    /**
     * Vérifier si la fermeture automatique des sessions est activée
     */
    public static function isAutoSessionClosureEnabled(): bool
    {
        return (bool) self::get(self::ENABLE_SESSION_CLOSURE, false);
    }

    /**
     * Obtenir l'heure de fermeture automatique des sessions
     */
    public static function getSessionClosureTime(): ?string
    {
        return self::get(self::SESSION_CLOSURE_TIME, '18:00');
    }

    /**
     * Vérifier si les sessions doivent être fermées maintenant
     * UTILISÉ dans LoginController
     */
    public static function shouldCloseSessionsNow(): bool
    {
        if (!self::isAutoSessionClosureEnabled()) {
            return false;
        }

        $closureTime = self::getSessionClosureTime();
        if (!$closureTime) {
            return false;
        }

        try {
            $now = Carbon::now();
            $closureDateTime = Carbon::createFromFormat('H:i', $closureTime);
            
            // Si l'heure actuelle dépasse l'heure de fermeture
            return $now->format('H:i') >= $closureDateTime->format('H:i');
            
        } catch (\Exception $e) {
            \Log::error("Error checking session closure time: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtenir le nombre maximum de sessions simultanées
     * UTILISÉ dans LoginController
     */
    public static function getMaxConcurrentSessions(): int
    {
        return (int) self::get(self::MAX_CONCURRENT_SESSIONS, 3);
    }

    /**
     * Obtenir le timeout des sessions en minutes
     * UTILISÉ dans LoginController  
     */
    public static function getSessionTimeoutMinutes(): int
    {
        return (int) self::get(self::SESSION_TIMEOUT_MINUTES, 120);
    }

    /**
     * Obtenir le nombre maximum de tentatives de connexion
     */
    public static function getMaxLoginAttempts(): int
    {
        return (int) self::get(self::MAX_LOGIN_ATTEMPTS, 5);
    }

    /**
     * Obtenir la durée de verrouillage en minutes
     */
    public static function getLockoutDurationMinutes(): int
    {
        return (int) self::get(self::LOCKOUT_DURATION_MINUTES, 30);
    }

    // ===============================================
    // MÉTHODES UTILITAIRES
    // ===============================================

    /**
     * Convertir une valeur selon son type
     */
    private static function castValue(mixed $value, string $type): mixed
    {
        return match($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'float' => (float) $value,
            'json' => is_string($value) ? json_decode($value, true) : $value,
            'array' => is_string($value) ? json_decode($value, true) : (array) $value,
            'time' => $value, // Format H:i
            default => (string) $value
        };
    }

    /**
     * Préparer une valeur pour le stockage
     */
    private static function prepareValue(mixed $value, string $type): string
    {
        return match($type) {
            'boolean' => $value ? '1' : '0',
            'json', 'array' => json_encode($value),
            default => (string) $value
        };
    }

    /**
     * Vider tout le cache des paramètres
     */
    public static function clearCache(): void
    {
        $keys = self::pluck('key');
        
        foreach ($keys as $key) {
            Cache::forget("setting_{$key}");
        }
        
        // Vider les caches de groupe
        $groups = self::distinct('group')->pluck('group');
        foreach ($groups as $group) {
            Cache::forget("settings_group_{$group}");
        }
    }

    /**
     * Réinitialiser tous les paramètres aux valeurs par défaut
     */
    public static function resetToDefaults(): bool
    {
        try {
            // Supprimer tous les paramètres existants
            self::truncate();
            
            // Vider le cache
            self::clearCache();
            
            // Recréer les paramètres par défaut via le seeder
            \Artisan::call('db:seed', ['--class' => 'SettingSeeder']);
            
            return true;
            
        } catch (\Exception $e) {
            \Log::error("Error resetting settings: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtenir tous les paramètres par groupe pour l'administration
     */
    public static function getAllByGroups(): array
    {
        $settings = self::where('is_active', true)
                       ->orderBy('group')
                       ->orderBy('sort_order')
                       ->get()
                       ->groupBy('group');

        $result = [];
        foreach ($settings as $group => $groupSettings) {
            $result[$group] = $groupSettings->map(function ($setting) {
                return [
                    'key' => $setting->key,
                    'value' => $setting->value,
                    'formatted_value' => self::castValue($setting->value, $setting->type),
                    'type' => $setting->type,
                    'label' => $setting->label,
                    'description' => $setting->description,
                    'meta' => $setting->meta
                ];
            });
        }
        
        return $result;
    }

    /**
     * Valider une valeur selon son type et ses métadonnées
     */
    public static function validateValue(string $key, mixed $value): array
    {
        $setting = self::where('key', $key)->first();
        
        if (!$setting) {
            return ['valid' => false, 'message' => 'Paramètre non trouvé'];
        }

        $meta = $setting->meta ?? [];
        
        // Validation de base selon le type
        switch ($setting->type) {
            case 'boolean':
                if (!is_bool($value) && !in_array($value, ['0', '1', 0, 1, true, false])) {
                    return ['valid' => false, 'message' => 'Valeur booléenne requise'];
                }
                break;
                
            case 'integer':
                if (!is_numeric($value)) {
                    return ['valid' => false, 'message' => 'Valeur numérique requise'];
                }
                
                $intValue = (int) $value;
                if (isset($meta['min']) && $intValue < $meta['min']) {
                    return ['valid' => false, 'message' => "Valeur minimum: {$meta['min']}"];
                }
                if (isset($meta['max']) && $intValue > $meta['max']) {
                    return ['valid' => false, 'message' => "Valeur maximum: {$meta['max']}"];
                }
                break;
                
            case 'time':
                if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $value)) {
                    return ['valid' => false, 'message' => 'Format heure invalide (HH:MM)'];
                }
                break;
        }

        return ['valid' => true, 'message' => 'Valeur valide'];
    }

    // ===============================================
    // SCOPES
    // ===============================================

    /**
     * Scope pour les paramètres actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour un groupe spécifique
     */
    public function scopeGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Scope pour l'ordre d'affichage
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('label');
    }
}