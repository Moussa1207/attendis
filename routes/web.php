<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
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
        | GESTION DES UTILISATEURS
        |--------------------------------------------------------------------------
        */
        
        // Liste des utilisateurs 
        Route::get('/user/users-list', [DashboardController::class, 'usersList'])
            ->name('user.users-list');
        
        // Création d'utilisateurs (UserManagementController)
        Route::get('/admin/users/create', [UserManagementController::class, 'create'])
            ->name('admin.users.create');
        Route::post('/admin/users/store', [UserManagementController::class, 'store'])
            ->name('admin.users.store');
        
        // Mes utilisateurs créés
        Route::get('/admin/users/my-created', [UserManagementController::class, 'myCreatedUsers'])
            ->name('admin.users.my-created');
        
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