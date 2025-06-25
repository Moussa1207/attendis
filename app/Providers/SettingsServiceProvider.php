<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Setting;
use App\Helpers\SettingsHelper;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Enregistrer le helper comme singleton
        $this->app->singleton('settings', function () {
            return new SettingsHelper();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Partager les paramètres globaux avec toutes les vues
        $this->shareGlobalSettings();
        
        // Enregistrer les directives Blade personnalisées
        $this->registerBladeDirectives();
        
        // Enregistrer les commandes artisan
        $this->registerCommands();
    }

    /**
     * Partager les paramètres système avec toutes les vues
     */
    private function shareGlobalSettings(): void
    {
        try {
            View::composer('*', function ($view) {
                // Paramètres globaux disponibles dans toutes les vues
                $globalSettings = [
                    'app_name' => Setting::get('app_name', 'Attendis'),
                    'app_version' => Setting::get('app_version', '1.0.0'),
                    'maintenance_mode' => Setting::get('maintenance_mode', false),
                    'auto_session_closure' => Setting::isAutoSessionClosureEnabled(),
                    'closure_time' => Setting::getSessionClosureTime(),
                    'debug_mode' => Setting::get('debug_mode', false)
                ];
                
                $view->with('globalSettings', $globalSettings);
            });
            
            // Paramètres spécifiques pour les vues d'administration
            View::composer(['layouts.app', 'layouts.setting'], function ($view) {
                if (auth()->check() && auth()->user()->isAdmin()) {
                    $adminSettings = [
                        'auto_detect_advisors' => Setting::isAutoDetectAdvisorsEnabled(),
                        'auto_assign_services' => Setting::isAutoAssignServicesEnabled(),
                        'max_concurrent_sessions' => Setting::getMaxConcurrentSessions(),
                        'max_login_attempts' => Setting::getMaxLoginAttempts()
                    ];
                    
                    $view->with('adminSettings', $adminSettings);
                }
            });
            
        } catch (\Exception $e) {
            // En cas d'erreur (table pas encore créée, etc.), utiliser les valeurs par défaut
            \Log::warning('Settings not available yet, using defaults: ' . $e->getMessage());
        }
    }

    /**
     * Enregistrer les directives Blade personnalisées pour les paramètres
     */
    private function registerBladeDirectives(): void
    {
        // Directive pour vérifier un paramètre
        Blade::if('setting', function ($key, $expectedValue = true) {
            try {
                $actualValue = Setting::get($key);
                return $actualValue == $expectedValue;
            } catch (\Exception $e) {
                return false;
            }
        });

        // Directive pour les admins avec paramètres
        Blade::if('adminWithSettings', function () {
            return auth()->check() && auth()->user()->isAdmin() && Setting::count() > 0;
        });

        // Directive pour la fermeture automatique
        Blade::if('sessionClosureActive', function () {
            try {
                return Setting::isAutoSessionClosureEnabled() && 
                       !auth()->user()?->isAdmin();
            } catch (\Exception $e) {
                return false;
            }
        });

        // Directive pour les conseillers auto-détectés
        Blade::if('autoDetectAdvisors', function () {
            try {
                return auth()->check() && 
                       auth()->user()->isConseillerUser() && 
                       Setting::isAutoDetectAdvisorsEnabled();
            } catch (\Exception $e) {
                return false;
            }
        });
    }

    /**
     * Enregistrer les commandes Artisan personnalisées
     */
    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\SettingsCommand::class,
            ]);
        }
    }
}