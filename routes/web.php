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
    | ROUTES UTILISATEURS NORMAUX
    |--------------------------------------------------------------------------
    */
    
    // Dashboard utilisateurs normaux (app-users)
    Route::get('/layouts/app-users', [DashboardController::class, 'userDashboard'])
        ->name('layouts.app-users');
    
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

// Routes principales des paramètres (dans la section ROUTES ADMINISTRATEURS UNIQUEMENT)
Route::prefix('layouts/setting')->middleware(['auth', 'admin'])->group(function () {
    
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

// Route alternative pour compatibility (si vous aviez d'autres références)
Route::get('/settings', function() {
    return redirect()->route('layouts.setting');
})->middleware(['auth', 'admin']);
            /*
        |--------------------------------------------------------------------------
        | GESTION DES AGENCES
        |--------------------------------------------------------------------------
        */
        
        // Routes pour la gestion des agences
Route::prefix('admin')->middleware(['auth'])->group(function () {
    
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
| GESTION DES SERVICES (À ajouter dans la section ROUTES ADMINISTRATEURS)
|--------------------------------------------------------------------------
*/

// Routes principales des services
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    
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
            ->name('admin.users.my-created');
         Route::get('/admin/users/{user}/edit', [UserManagementController::class, 'edit'])
    ->name('admin.users.edit');
    Route::put('/admin/users/{user}', [UserManagementController::class, 'update'])
    ->name('admin.users.update');
            
        // Renvoyer identifiants
        Route::post('/admin/users/{user}/resend-credentials', [UserManagementController::class, 'resendCredentials'])
            ->name('admin.users.resend-credentials');
           
        /*
        |--------------------------------------------------------------------------
        | ACTIONS SUR LES UTILISATEURS (DashboardController)
        |--------------------------------------------------------------------------
        */
        
        // Activation/Suspension/Réactivation
        Route::patch('/admin/users/{user}/activate', [DashboardController::class, 'activateUser'])
            ->name('admin.users.activate');
        Route::patch('/admin/users/{user}/suspend', [DashboardController::class, 'suspendUser'])
            ->name('admin.users.suspend');
        Route::patch('/admin/users/{user}/reactivate', [DashboardController::class, 'reactivateUser'])
            ->name('admin.users.reactivate');
        
        // Suppression
        Route::delete('/admin/users/{user}', [DashboardController::class, 'deleteUser'])
            ->name('admin.users.delete');
        
        // 🆕 NOUVELLE ROUTE : Réinitialisation mot de passe depuis modal détails
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
        
        // Statistiques admin personnalisées (UserManagementController)
        Route::get('/admin/api/my-stats', [UserManagementController::class, 'getMyUserStats'])
            ->name('admin.api.my-stats');
    });
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
});

/*
|--------------------------------------------------------------------------
| GESTION DES ERREURS
|--------------------------------------------------------------------------
*/

// Redirection page non trouvées
Route::fallback(function () {
    if (auth()->check()) {
        if (auth()->user()->isAdmin()) {
            return redirect()->route('layouts.app')
                ->with('warning', 'Page non trouvée. Redirection vers le dashboard admin.');
        } else {
            return redirect()->route('layouts.app-users')
                ->with('warning', 'Page non trouvée. Redirection vers votre dashboard.');
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
            'users' => \App\Models\User::where('user_type_id', 2)->count(),
        ]);
    })->middleware('admin');
}

/*|--------------------------------------------------------------------------
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
| ROUTES MOT DE PASSE OUBLIÉ
|--------------------------------------------------------------------------
*/

// Formulaire "mot de passe oublié"
Route::get('/password/forgot', [PasswordManagementController::class, 'showForgotForm'])
    ->name('password.forgot');

// Traitement demande de récupération
Route::post('/password/email', [PasswordManagementController::class, 'sendResetEmail'])
    ->name('password.email');

// Affichage formulaire de réinitialisation avec token (EXISTANT)
Route::get('/password/reset/{token}/{user}', [PasswordManagementController::class, 'showResetForm'])
    ->name('password.reset');

// Traitement réinitialisation avec token (EXISTANT )
Route::post('/password/update', [PasswordManagementController::class, 'resetPassword'])
    ->name('password.update');



    /*
|--------------------------------------------------------------------------
| ROUTES ADDITIONNELLES POUR LE JAVASCRIPT
|--------------------------------------------------------------------------
*/

// Route pour les détails utilisateur (compatible avec le JS)
Route::get('/admin/users/{user}/details', [DashboardController::class, 'getUserDetails'])
    ->name('admin.users.details');

// Routes POST alternatives pour le JavaScript (en plus des PATCH existantes)
Route::post('/admin/users/{user}/activate', [DashboardController::class, 'activateUser'])
    ->name('admin.users.activate.post');
Route::post('/admin/users/{user}/suspend', [DashboardController::class, 'suspendUser'])
    ->name('admin.users.suspend.post');

// Alias pour la réactivation (même endpoint que activate)
Route::post('/admin/users/{user}/reactivate', [DashboardController::class, 'activateUser'])
    ->name('admin.users.reactivate.post');

    // À ajouter dans la section ROUTES ADMINISTRATEURS UNIQUEMENT de web.php

/*
|--------------------------------------------------------------------------
| API POUR LA VÉRIFICATION DES SESSIONS EN TEMPS RÉEL
|--------------------------------------------------------------------------
*/

// Route pour vérifier la fermeture automatique des sessions (AJAX)
Route::get('/api/session/check-closure', [LoginController::class, 'checkSessionClosure'])
    ->name('api.session.check-closure')
    ->middleware(['auth']);

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
            'is_admin' => $user->isAdmin()
        ],
        'session_settings' => $settings,
        'security_info' => $user->getSecurityInfo(),
        'required_actions' => $user->getRequiredActions(),
        'server_time' => now()->format('H:i:s')
    ]);
})->middleware(['auth']);

/*
|--------------------------------------------------------------------------
| ROUTES API POUR LES PARAMÈTRES (AJAX)
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
| ROUTES DE DÉVELOPPEMENT POUR LES PARAMÈTRES
|--------------------------------------------------------------------------
*/

if (app()->environment('local')) {
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