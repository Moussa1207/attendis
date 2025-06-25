<?php

namespace App\Helpers;

use App\Models\Setting;

/**
 * Classe helper pour simplifier l'accès aux paramètres
 * Utilisation : SettingsHelper::autoDetectAdvisors()
 */
class SettingsHelper
{
    // ===============================================
    // MÉTHODES RACCOURCIES POUR LES PARAMÈTRES FRÉQUENTS
    // ===============================================

    /**
     * Vérifier si la détection automatique des conseillers est activée
     */
    public static function autoDetectAdvisors(): bool
    {
        return Setting::isAutoDetectAdvisorsEnabled();
    }

    /**
     * Vérifier si l'attribution automatique des services est activée
     */
    public static function autoAssignServices(): bool
    {
        return Setting::isAutoAssignServicesEnabled();
    }

    /**
     * Vérifier si la fermeture automatique des sessions est activée
     */
    public static function autoSessionClosure(): bool
    {
        return Setting::isAutoSessionClosureEnabled();
    }

    /**
     * Obtenir l'heure de fermeture des sessions
     */
    public static function sessionClosureTime(): ?string
    {
        return Setting::getSessionClosureTime();
    }

    /**
     * Vérifier si les sessions doivent être fermées maintenant
     */
    public static function shouldCloseSessions(): bool
    {
        return Setting::shouldCloseSessionsNow();
    }

    /**
     * Obtenir le nombre maximum de sessions simultanées
     */
    public static function maxConcurrentSessions(): int
    {
        return Setting::getMaxConcurrentSessions();
    }

    /**
     * Obtenir le timeout des sessions en minutes
     */
    public static function sessionTimeout(): int
    {
        return Setting::getSessionTimeoutMinutes();
    }

    /**
     * Obtenir le nombre maximum de tentatives de connexion
     */
    public static function maxLoginAttempts(): int
    {
        return Setting::getMaxLoginAttempts();
    }

    /**
     * Obtenir la durée de verrouillage en minutes
     */
    public static function lockoutDuration(): int
    {
        return Setting::getLockoutDurationMinutes();
    }

    // ===============================================
    // MÉTHODES POUR LES PARAMÈTRES GÉNÉRAUX
    // ===============================================

    /**
     * Obtenir le nom de l'application
     */
    public static function appName(): string
    {
        return Setting::get('app_name', 'Attendis');
    }

    /**
     * Obtenir la version de l'application
     */
    public static function appVersion(): string
    {
        return Setting::get('app_version', '1.0.0');
    }

    /**
     * Vérifier si le mode maintenance est activé
     */
    public static function isMaintenanceMode(): bool
    {
        return (bool) Setting::get('maintenance_mode', false);
    }

    /**
     * Vérifier si le mode debug est activé
     */
    public static function isDebugMode(): bool
    {
        return (bool) Setting::get('debug_mode', false);
    }

    /**
     * Vérifier si les notifications email sont activées
     */
    public static function emailNotificationsEnabled(): bool
    {
        return (bool) Setting::get('email_notifications', true);
    }

    /**
     * Obtenir l'email administrateur
     */
    public static function adminEmail(): string
    {
        return Setting::get('admin_email', 'admin@attendis.com');
    }

    // ===============================================
    // MÉTHODES UTILITAIRES
    // ===============================================

    /**
     * Obtenir tous les paramètres d'un groupe
     */
    public static function getGroup(string $group): array
    {
        return Setting::getGroupFormatted($group);
    }

    /**
     * Mettre à jour rapidement un paramètre
     */
    public static function set(string $key, mixed $value, string $type = 'string'): bool
    {
        return Setting::set($key, $value, $type);
    }

    /**
     * Obtenir un paramètre avec valeur par défaut
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }

    /**
     * Vérifier si un paramètre existe
     */
    public static function has(string $key): bool
    {
        return Setting::where('key', $key)->where('is_active', true)->exists();
    }

    /**
     * Obtenir les paramètres pour les vues avec mise en cache
     */
    public static function forView(string $group = 'user_management'): array
    {
        return Setting::getGroupFormatted($group);
    }

    // ===============================================
    // MÉTHODES POUR LES CONTRÔLEURS
    // ===============================================

    /**
     * Valider les paramètres de session pour LoginController
     */
    public static function validateSessionSettings(): array
    {
        return [
            'auto_closure_enabled' => self::autoSessionClosure(),
            'closure_time' => self::sessionClosureTime(),
            'should_close_now' => self::shouldCloseSessions(),
            'max_sessions' => self::maxConcurrentSessions(),
            'timeout_minutes' => self::sessionTimeout()
        ];
    }

    /**
     * Obtenir les paramètres de sécurité pour les connexions
     */
    public static function getSecuritySettings(): array
    {
        return [
            'max_login_attempts' => self::maxLoginAttempts(),
            'lockout_duration' => self::lockoutDuration(),
            'session_timeout' => self::sessionTimeout(),
            'max_concurrent_sessions' => self::maxConcurrentSessions()
        ];
    }

    /**
     * Obtenir les paramètres utilisateur pour UserManagementController
     */
    public static function getUserManagementSettings(): array
    {
        return [
            'auto_detect_advisors' => self::autoDetectAdvisors(),
            'auto_assign_services' => self::autoAssignServices(),
            'session_closure' => self::autoSessionClosure(),
            'closure_time' => self::sessionClosureTime()
        ];
    }

    // ===============================================
    // MÉTHODES DE DÉBOGAGE ET DÉVELOPPEMENT
    // ===============================================

    /**
     * Obtenir tous les paramètres pour le débogage
     */
    public static function getAllSettings(): array
    {
        return Setting::all()->mapWithKeys(function ($setting) {
            return [$setting->key => [
                'value' => $setting->value,
                'formatted_value' => Setting::get($setting->key),
                'type' => $setting->type,
                'group' => $setting->group
            ]];
        })->toArray();
    }

    /**
     * Obtenir les statistiques des paramètres
     */
    public static function getStats(): array
    {
        return [
            'total_settings' => Setting::count(),
            'active_settings' => Setting::active()->count(),
            'groups' => Setting::distinct('group')->pluck('group')->toArray(),
            'types' => Setting::distinct('type')->pluck('type')->toArray()
        ];
    }

    /**
     * Vérifier la cohérence des paramètres
     */
    public static function checkConsistency(): array
    {
        $issues = [];

        // Vérifier les paramètres de session
        if (self::autoSessionClosure() && !self::sessionClosureTime()) {
            $issues[] = 'Fermeture automatique activée mais aucune heure définie';
        }

        // Vérifier les paramètres de sécurité
        if (self::maxLoginAttempts() <= 0) {
            $issues[] = 'Nombre maximum de tentatives de connexion invalide';
        }

        if (self::lockoutDuration() <= 0) {
            $issues[] = 'Durée de verrouillage invalide';
        }

        if (self::maxConcurrentSessions() <= 0) {
            $issues[] = 'Nombre maximum de sessions simultanées invalide';
        }

        return [
            'consistent' => empty($issues),
            'issues' => $issues
        ];
    }
}