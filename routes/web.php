<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\AgencyController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SettingsController;
use App\Models\Setting;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\PasswordManagementController;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| ROUTES PUBLIQUES (Authentification)
|--------------------------------------------------------------------------
*/

// Page de connexion (page d'accueil)
Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');

// Inscription (RÉSERVÉE AUX ADMINS UNIQUEMENT)
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.post');

// Déconnexion
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| ROUTES MOT DE PASSE OUBLIÉ
|--------------------------------------------------------------------------
*/

// Formulaire "mot de passe oublié"
Route::get('/password/forgot', [PasswordManagementController::class, 'showForgotForm'])
    ->name('password.forgot');

// Traitement demande de récupération
Route::post('/password/email', [PasswordManagementController::class, 'sendResetEmail'])
    ->name('password.email');

// Affichage formulaire de réinitialisation avec token
Route::get('/password/reset/{token}/{user}', [PasswordManagementController::class, 'showResetForm'])
    ->name('password.reset');

// Traitement réinitialisation avec token
Route::post('/password/update', [PasswordManagementController::class, 'resetPassword'])
    ->name('password.update');

/*
|--------------------------------------------------------------------------
| ROUTES CHANGEMENT MOT DE PASSE OBLIGATOIRE
|--------------------------------------------------------------------------
*/

// Affichage formulaire changement obligatoire (utilisateur temporairement connecté)
Route::get('/password/mandatory-change', [LoginController::class, 'showMandatoryPasswordChange'])
    ->name('password.mandatory-change')
    ->middleware('web');

// Traitement changement obligatoire
Route::post('/password/mandatory-update', [LoginController::class, 'updateMandatoryPassword'])
    ->name('password.mandatory-update')
    ->middleware('web');

/*
|--------------------------------------------------------------------------
| ROUTES PROTÉGÉES (Utilisateurs connectés)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'check.user.status'])->group(function () {
    
    // Dashboard principal - Redirection intelligente selon le type d'utilisateur
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    /*
    |--------------------------------------------------------------------------
    | ROUTES COMMUNES (Admin + Users)
    |--------------------------------------------------------------------------
    */
    
    // Changement de mot de passe (utilisateur connecté)
    Route::post('/password/change', [PasswordManagementController::class, 'changePassword'])
        ->name('password.change');
    
    /*
    |--------------------------------------------------------------------------
    | 🆕 ROUTES UTILISATEURS AVEC DIFFÉRENCIATION AUTOMATIQUE
    |--------------------------------------------------------------------------
    */
    
    // Dashboard utilisateurs - LOGIQUE AUTOMATIQUE :
    // → Poste Ecran : Interface plein écran sans sidebar + grille services
    // → Accueil/Conseiller : Interface avec sidebar + guide métier
    Route::get('/layouts/app-users', [DashboardController::class, 'userDashboard'])
        ->name('layouts.app-users');

    // 🆕 API pour rafraîchir les services (interface Poste Ecran)
    Route::get('/api/user/services/refresh', [DashboardController::class, 'refreshUserServices'])
        ->name('api.user.services.refresh');

    // 🆕 API pour obtenir les informations utilisateur (AJAX)
    Route::get('/api/user/info', [DashboardController::class, 'getUserInfo'])
        ->name('api.user.info');

    // 🆕 API pour les guides métier par type
    Route::get('/api/user/type-guide/{type?}', [DashboardController::class, 'getTypeGuide'])
        ->name('api.user.type-guide');
    
    /*
    |--------------------------------------------------------------------------
    | ROUTES ADMINISTRATEURS UNIQUEMENT
    |--------------------------------------------------------------------------
    */
    
    Route::middleware('admin')->group(function () {
        
        // Dashboard admin principal (layouts/app)
        Route::get('/layouts/app', [DashboardController::class, 'adminDashboard'])
            ->name('layouts.app');

        /*
        |--------------------------------------------------------------------------
        | ROUTES PARAMÈTRES GÉNÉRAUX - VERSION COMPLÈTE
        |--------------------------------------------------------------------------
        */

        // Routes principales des paramètres
        Route::prefix('layouts/setting')->group(function () {
            
            // Page principale des paramètres généraux
            Route::get('/general', [SettingsController::class, 'index'])
                ->name('layouts.setting');
            
            // Mise à jour des paramètres
            Route::put('/general', [SettingsController::class, 'update'])
                ->name('layouts.setting.update');
            
            // Actions sur les paramètres
            Route::post('/reset', [SettingsController::class, 'reset'])
                ->name('layouts.setting.reset');
            
            Route::post('/clear-cache', [SettingsController::class, 'clearCache'])
                ->name('layouts.setting.clear-cache');
            
            // API pour les paramètres (AJAX)
            Route::get('/api/group/{group}', [SettingsController::class, 'getGroupSettings'])
                ->name('layouts.setting.api.group');
            
            Route::post('/api/update', [SettingsController::class, 'updateSetting'])
                ->name('layouts.setting.api.update');
            
            Route::get('/api/stats', [SettingsController::class, 'getStats'])
                ->name('layouts.setting.api.stats');
        });

        // Route alternative pour compatibility
        Route::get('/settings', function() {
            return redirect()->route('layouts.setting');
        });

        /*
        |--------------------------------------------------------------------------
        | GESTION DES AGENCES
        |--------------------------------------------------------------------------
        */
        
        Route::prefix('admin')->group(function () {
            
            // Routes principales des agences
            Route::get('/agencies', [AgencyController::class, 'index'])->name('agency.agence');
            Route::get('/agencies/create', [AgencyController::class, 'create'])->name('agency.agence-create');
            Route::post('/agencies', [AgencyController::class, 'store'])->name('agencies.store');
            Route::get('/agencies/{agency}', [AgencyController::class, 'show'])->name('agencies.show');
            Route::get('/agencies/{agency}/edit', [AgencyController::class, 'edit'])->name('agencies.edit');
            Route::put('/agencies/{agency}', [AgencyController::class, 'update'])->name('agencies.update');
            Route::delete('/agencies/{agency}', [AgencyController::class, 'destroy'])->name('agencies.destroy');
            
            // Routes pour les actions spécifiques des agences
            Route::post('/agencies/{agency}/activate', [AgencyController::class, 'activate'])->name('agencies.activate');
            Route::post('/agencies/{agency}/deactivate', [AgencyController::class, 'deactivate'])->name('agencies.deactivate');
            Route::get('/agencies/{agency}/details', [AgencyController::class, 'details'])->name('agencies.details');
            
            // Routes pour les actions en masse
            Route::post('/agencies/bulk-activate', [AgencyController::class, 'bulkActivate'])->name('agencies.bulk-activate');
            Route::post('/agencies/bulk-delete', [AgencyController::class, 'bulkDelete'])->name('agencies.bulk-delete');
            Route::get('/agencies/export', [AgencyController::class, 'export'])->name('agencies.export');
        });

        /*
        |--------------------------------------------------------------------------
        | GESTION DES SERVICES
        |--------------------------------------------------------------------------
        */

        Route::prefix('admin')->group(function () {
            
            // Routes principales des services
            Route::get('/services', [ServiceController::class, 'index'])->name('service.service-list');
            Route::get('/services/create', [ServiceController::class, 'create'])->name('service.service-create');
            Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
            Route::get('/services/{service}', [ServiceController::class, 'show'])->name('services.show');
            Route::get('/services/{service}/edit', [ServiceController::class, 'edit'])->name('services.edit');
            Route::put('/services/{service}', [ServiceController::class, 'update'])->name('services.update');
            Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');
            
            // Routes pour les actions spécifiques des services
            Route::post('/services/{service}/activate', [ServiceController::class, 'activate'])->name('services.activate');
            Route::post('/services/{service}/deactivate', [ServiceController::class, 'deactivate'])->name('services.deactivate');
            Route::get('/services/{service}/details', [ServiceController::class, 'details'])->name('services.details');
            
            // Routes pour les actions en masse
            Route::post('/services/bulk-activate', [ServiceController::class, 'bulkActivate'])->name('services.bulk-activate');
            Route::post('/services/bulk-delete', [ServiceController::class, 'bulkDelete'])->name('services.bulk-delete');
            Route::get('/services/export', [ServiceController::class, 'export'])->name('services.export');
            
            // API pour statistiques des services
            Route::get('/api/services/stats', [ServiceController::class, 'getStats'])->name('services.api.stats');
            
            // 🆕 API pour la recherche de services (AJAX)
            Route::get('/api/services/search', [ServiceController::class, 'searchServices'])->name('services.api.search');
        });

        /*
        |--------------------------------------------------------------------------
        | GESTION DES UTILISATEURS
        |--------------------------------------------------------------------------
        */
        
        // Liste des utilisateurs 
        Route::get('/user/users-list', [DashboardController::class, 'usersList'])
            ->name('user.users-list');
        
        // Création d'utilisateurs 
        Route::get('/admin/user/create', [UserManagementController::class, 'create'])
            ->name('User.user-create');
        Route::post('/admin/user/store', [UserManagementController::class, 'store'])
            ->name('User.user.store');
         
        // Mes utilisateurs créés
        Route::get('/admin/users/my-created', [UserManagementController::class, 'myCreatedUsers'])
            ->name('User.user.my-created');
            
        // Modification d'utilisateurs
        Route::get('/admin/user/{user}/edit', [UserManagementController::class, 'edit'])
            ->name('User.user-edit');
        Route::put('/admin/users/{user}', [UserManagementController::class, 'update'])
            ->name('User.user.update');
            
        // Renvoyer identifiants
        Route::post('/admin/users/{user}/resend-credentials', [UserManagementController::class, 'resendCredentials'])
            ->name('admin.users.resend-credentials');

        // 🆕 Changement de type d'utilisateur
        Route::post('/admin/users/{user}/change-type', [UserManagementController::class, 'changeUserType'])
            ->name('admin.users.change-type');
           
        /*
        |--------------------------------------------------------------------------
        | ACTIONS SUR LES UTILISATEURS (DashboardController)
        |--------------------------------------------------------------------------
        */
        
        // Activation/Suspension/Réactivation (PATCH)
        Route::patch('/admin/users/{user}/activate', [DashboardController::class, 'activateUser'])
            ->name('admin.users.activate');
        Route::patch('/admin/users/{user}/suspend', [DashboardController::class, 'suspendUser'])
            ->name('admin.users.suspend');
        Route::patch('/admin/users/{user}/reactivate', [DashboardController::class, 'reactivateUser'])
            ->name('admin.users.reactivate');
        
        // Routes POST alternatives pour le JavaScript (compatibilité)
        Route::post('/admin/users/{user}/activate', [DashboardController::class, 'activateUser'])
            ->name('admin.users.activate.post');
        Route::post('/admin/users/{user}/suspend', [DashboardController::class, 'suspendUser'])
            ->name('admin.users.suspend.post');
        Route::post('/admin/users/{user}/reactivate', [DashboardController::class, 'activateUser'])
            ->name('admin.users.reactivate.post');
        
        // Suppression
        Route::delete('/admin/users/{user}', [DashboardController::class, 'deleteUser'])
            ->name('admin.users.delete');
        
        // Réinitialisation mot de passe depuis modal détails
        Route::post('/admin/users/{user}/reset-password', [DashboardController::class, 'resetUserPassword'])
            ->name('admin.users.reset-password');
        
        // Actions en masse
        Route::post('/admin/users/bulk-activate', [DashboardController::class, 'bulkActivate'])
            ->name('admin.users.bulk-activate');
        Route::post('/admin/users/bulk-delete', [DashboardController::class, 'bulkDeleteUsers'])
            ->name('admin.users.bulk-delete');
        
        // Export
        Route::get('/admin/users/export', [DashboardController::class, 'exportUsers'])
            ->name('admin.users.export');
        
        /*
        |--------------------------------------------------------------------------
        | API AJAX POUR ADMINS
        |--------------------------------------------------------------------------
        */
        
        // Statistiques en temps réel
        Route::get('/admin/api/stats', [DashboardController::class, 'getStats'])
            ->name('admin.api.stats');
        Route::get('/admin/api/advanced-stats', [DashboardController::class, 'getAdvancedStats'])
            ->name('admin.api.advanced-stats');
        
        // Recherche d'utilisateurs
        Route::get('/admin/api/search-users', [DashboardController::class, 'searchUsers'])
            ->name('admin.api.search-users');
        
        // Détails utilisateur (AJAX)
        Route::get('/admin/api/users/{user}/details', [DashboardController::class, 'getUserDetails'])
            ->name('admin.api.user-details');
        Route::get('/admin/users/{user}/details', [DashboardController::class, 'getUserDetails'])
            ->name('admin.users.details');
        
        // Statistiques admin personnalisées (UserManagementController)
        Route::get('/admin/api/my-stats', [UserManagementController::class, 'getMyUserStats'])
            ->name('admin.api.my-stats');

        // 🆕 API pour obtenir les rôles disponibles
        Route::get('/admin/api/available-roles', [UserManagementController::class, 'getAvailableRolesApi'])
            ->name('admin.api.available-roles');
    });
});

/*
|--------------------------------------------------------------------------
| 🆕 API POUR LA VÉRIFICATION DES SESSIONS EN TEMPS RÉEL
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    
    // Route pour vérifier la fermeture automatique des sessions (AJAX)
    Route::get('/api/session/check-closure', [LoginController::class, 'checkSessionClosure'])
        ->name('api.session.check-closure');

    // Route pour obtenir les informations de session (AJAX)
    Route::get('/api/session/info', function(Request $request) {
        if (!Auth::check()) {
            return response()->json(['authenticated' => false]);
        }
        
        $user = Auth::user();
        $settings = Setting::validateSessionSettings();
        
        return response()->json([
            'authenticated' => true,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'type' => $user->getTypeName(),
                'type_role' => $user->getUserRole(),
                'is_admin' => $user->isAdmin(),
                'is_ecran' => $user->isEcranUser(),
                'is_accueil' => $user->isAccueilUser(),
                'is_conseiller' => $user->isConseillerUser(),
            ],
            'session_settings' => $settings,
            'security_info' => $user->getSecurityInfo(),
            'required_actions' => $user->getRequiredActions(),
            'server_time' => now()->format('H:i:s')
        ]);
    })->name('api.session.info');
});

/*
|--------------------------------------------------------------------------
| 🆕 ROUTES API POUR LES PARAMÈTRES (AJAX)
|--------------------------------------------------------------------------
*/

// API publique pour les paramètres (sans authentification)
Route::prefix('api/settings')->group(function () {
    
    // Obtenir les paramètres publics (comme le nom de l'app)
    Route::get('/public', function() {
        return response()->json([
            'app_name' => Setting::get('app_name', 'Attendis'),
            'app_version' => Setting::get('app_version', '1.0.0'),
            'maintenance_mode' => Setting::get('maintenance_mode', false),
            'auto_session_closure' => Setting::isAutoSessionClosureEnabled(),
            'closure_time' => Setting::getSessionClosureTime()
        ]);
    });
    
    // Vérifier si un paramètre spécifique est activé
    Route::get('/check/{key}', function($key) {
        $allowedKeys = [
            'auto_detect_available_advisors',
            'auto_assign_all_services_to_advisors', 
            'enable_auto_session_closure',
            'maintenance_mode'
        ];
        
        if (!in_array($key, $allowedKeys)) {
            return response()->json(['error' => 'Paramètre non autorisé'], 403);
        }
        
        return response()->json([
            'key' => $key,
            'value' => Setting::get($key),
            'active' => (bool) Setting::get($key)
        ]);
    });
});

/*
|--------------------------------------------------------------------------
| 🆕 ROUTES API UTILITAIRES POUR LES INTERFACES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'check.user.status'])->group(function () {
    
    // API pour rafraîchir les données selon le type d'utilisateur
    Route::get('/api/dashboard/refresh', function(Request $request) {
        $user = Auth::user();
        
        if ($user->isEcranUser()) {
            // Données pour interface Poste Ecran
            $creator = $user->getCreator();
            $services = $creator ? $creator->createdServices()->get() : collect();
            
            return response()->json([
                'success' => true,
                'type' => 'ecran',
                'data' => [
                    'services_count' => $services->count(),
                    'active_services' => $services->where('statut', 'actif')->count(),
                    'inactive_services' => $services->where('statut', 'inactif')->count(),
                    'recent_services' => $services->where('created_at', '>=', now()->subDays(7))->count(),
                    'last_update' => now()->format('H:i:s'),
                ]
            ]);
            
        } else {
            // Données pour interface Accueil/Conseiller
            return response()->json([
                'success' => true,
                'type' => $user->getUserRole(),
                'data' => [
                    'user_type' => $user->getTypeName(),
                    'days_active' => $user->created_at->diffInDays(now()),
                    'last_login' => $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Jamais',
                    'last_update' => now()->format('H:i:s'),
                ]
            ]);
        }
    })->name('api.dashboard.refresh');

    // API pour obtenir les conseils métier selon le type
    Route::get('/api/user/tips/{type?}', function($type = null) {
        $user = Auth::user();
        $userType = $type ?: $user->getUserRole();
        
        $tips = [
            'ecran' => [
                'Vérifiez régulièrement les nouveaux services',
                'Utilisez la recherche pour trouver rapidement un service',
                'L\'interface se met à jour automatiquement toutes les 5 minutes'
            ],
            'accueil' => [
                'Accueillez chaleureusement tous les visiteurs',
                'Orientez les visiteurs vers les bons services',
                'Tenez à jour les informations d\'accueil'
            ],
            'conseiller' => [
                'Écoutez attentivement les besoins clients',
                'Documentez toutes les interactions importantes',
                'Collaborez efficacement avec l\'équipe'
            ]
        ];
        
        return response()->json([
            'success' => true,
            'type' => $userType,
            'tips' => $tips[$userType] ?? []
        ]);
    })->name('api.user.tips');
});

/*
|--------------------------------------------------------------------------
| ROUTES DE COMPATIBILITÉ
|--------------------------------------------------------------------------
*/

// Redirection des anciennes routes vers la nouvelle logique
Route::middleware(['auth', 'check.user.status'])->group(function () {
    Route::get('/app', function () {
        return redirect()->route('dashboard');
    });
    
    // 🆕 Redirection spécifique pour les anciens liens directs
    Route::get('/app-ecran', function () {
        return redirect()->route('layouts.app-users');
    });
    
    Route::get('/app-accueil', function () {
        return redirect()->route('layouts.app-users');
    });
    
    Route::get('/app-conseiller', function () {
        return redirect()->route('layouts.app-users');
    });
});

/*
|--------------------------------------------------------------------------
| GESTION DES ERREURS
|--------------------------------------------------------------------------
*/

// Redirection page non trouvées avec logique améliorée
Route::fallback(function () {
    if (auth()->check()) {
        $user = auth()->user();
        
        if ($user->isAdmin()) {
            return redirect()->route('layouts.app')
                ->with('warning', 'Page non trouvée. Redirection vers le dashboard admin.');
        } else {
            return redirect()->route('layouts.app-users')
                ->with('warning', "Page non trouvée. Redirection vers votre espace {$user->getTypeName()}.");
        }
    }
    
    return redirect()->route('login')
        ->with('error', 'Page non trouvée. Veuillez vous connecter.');
});

/*
|--------------------------------------------------------------------------
| ROUTES DE DÉVELOPPEMENT (À supprimer en production)
|--------------------------------------------------------------------------
*/

if (app()->environment('local')) {
    
    // Test des relations
    Route::get('/dev/test-relations', function () {
        $users = \App\Models\User::with(['createdBy.administrator', 'createdUsers.user'])->get();
        return response()->json($users->map(function($user) {
            return [
                'id' => $user->id,
                'username' => $user->username,
                'type' => $user->getTypeName(),
                'type_role' => $user->getUserRole(),
                'created_by' => $user->getCreator() ? $user->getCreator()->username : null,
                'created_users_count' => $user->createdUsers()->count(),
            ];
        }));
    })->middleware('admin');
    
    // Test des statistiques
    Route::get('/dev/test-stats', function () {
        return response()->json([
            'total_users' => \App\Models\User::count(),
            'total_relations' => \App\Models\AdministratorUser::count(),
            'admins' => \App\Models\User::where('user_type_id', 1)->count(),
            'ecran_users' => \App\Models\User::where('user_type_id', 2)->count(),
            'accueil_users' => \App\Models\User::where('user_type_id', 3)->count(),
            'conseiller_users' => \App\Models\User::where('user_type_id', 4)->count(),
        ]);
    })->middleware('admin');

    // 🆕 Test de la différenciation des interfaces
    Route::get('/dev/test-interfaces', function () {
        if (!auth()->check()) {
            return response()->json(['error' => 'Non connecté']);
        }
        
        $user = auth()->user();
        
        return response()->json([
            'user_id' => $user->id,
            'username' => $user->username,
            'type_id' => $user->user_type_id,
            'type_name' => $user->getTypeName(),
            'type_role' => $user->getUserRole(),
            'is_admin' => $user->isAdmin(),
            'is_ecran' => $user->isEcranUser(),
            'is_accueil' => $user->isAccueilUser(),
            'is_conseiller' => $user->isConseillerUser(),
            'interface_destination' => $user->isAdmin() ? 'layouts.app' : 'layouts.app-users',
            'interface_type' => $user->isEcranUser() ? 'app-ecran.blade.php' : 'app-users.blade.php',
            'creator' => $user->getCreator() ? $user->getCreator()->username : null,
            'services_count' => $user->getCreator() ? $user->getCreator()->createdServices()->count() : 0,
        ]);
    })->middleware('auth');

    /*
    |--------------------------------------------------------------------------
    | ROUTES DE DÉVELOPPEMENT POUR LES PARAMÈTRES
    |--------------------------------------------------------------------------
    */

    Route::prefix('dev/settings')->middleware(['auth', 'admin'])->group(function () {
        
        // Tester tous les paramètres
        Route::get('/test-all', function() {
            return response()->json([
                'user_management' => Setting::getUserManagementSettings(),
                'security' => Setting::getSecuritySettings(),
                'all_settings' => Setting::getAllSettings(),
                'stats' => Setting::getStats(),
                'consistency_check' => Setting::checkConsistency()
            ]);
        });
        
        // Forcer une valeur pour test
        Route::post('/force/{key}', function(Request $request, $key) {
            $value = $request->input('value');
            $type = $request->input('type', 'string');
            
            $success = Setting::set($key, $value, $type);
            
            return response()->json([
                'success' => $success,
                'key' => $key,
                'new_value' => Setting::get($key),
                'message' => $success ? 'Paramètre forcé avec succès' : 'Erreur lors du forçage'
            ]);
        });
        
        // Simuler la fermeture automatique
        Route::post('/simulate-closure', function() {
            // Forcer la fermeture pour test
            Setting::set('enable_auto_session_closure', true, 'boolean');
            Setting::set('auto_session_closure_time', now()->format('H:i'), 'time');
            
            return response()->json([
                'message' => 'Fermeture automatique simulée',
                'closure_time' => Setting::getSessionClosureTime(),
                'should_close_now' => Setting::shouldCloseSessionsNow()
            ]);
        });
    });

}