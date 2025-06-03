<?php
// routes/web.php - VERSION COMPLÈTE

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminUserController;
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
        
        // Liste des utilisateurs avec filtres
        Route::get('/User/users-list', [DashboardController::class, 'usersList'])
            ->name('user.users-list');
        
        // Création d'utilisateurs
        Route::get('/admin/users/create', [AdminUserController::class, 'create'])
            ->name('admin.users.create');
        Route::post('/admin/users/store', [AdminUserController::class, 'store'])
            ->name('admin.users.store');
        
        // Détails d'un utilisateur
        Route::get('/admin/users/{user}', [AdminUserController::class, 'show'])
            ->name('admin.users.show');
        
        /*
        |--------------------------------------------------------------------------
        | ACTIONS SUR LES UTILISATEURS
        |--------------------------------------------------------------------------
        */
        
        // Activation/Suspension d'utilisateurs
        Route::patch('/admin/users/{user}/activate', [DashboardController::class, 'activateUser'])
            ->name('admin.users.activate');
        Route::patch('/admin/users/{user}/suspend', [DashboardController::class, 'suspendUser'])
            ->name('admin.users.suspend');
        
        // Activation en masse
        Route::post('/admin/users/bulk-activate', [DashboardController::class, 'bulkActivate'])
            ->name('admin.users.bulk-activate');
        
        /*
        |--------------------------------------------------------------------------
        | GESTION DES MOTS DE PASSE (ADMIN)
        |--------------------------------------------------------------------------
        */
        
        // Générer un lien de réinitialisation
        Route::post('/admin/users/{user}/generate-reset-link', [AdminUserController::class, 'generatePasswordResetLink'])
            ->name('admin.users.generate-reset-link');
        
        
        /*
        |--------------------------------------------------------------------------
        | API AJAX POUR ADMINS
        |--------------------------------------------------------------------------
        */
        
        // Statistiques en temps réel
        Route::get('/admin/api/stats', [DashboardController::class, 'getStats'])
            ->name('admin.api.stats');
        
        // Recherche d'utilisateurs
        Route::get('/admin/api/search-users', [DashboardController::class, 'searchUsers'])
            ->name('admin.api.search-users');
        
        // Détails utilisateur (AJAX)
        Route::get('/admin/api/users/{user}/details', [DashboardController::class, 'getUserDetails'])
            ->name('admin.api.user-details');
        
        // Statistiques admin personnalisées
        Route::get('/admin/api/my-stats', [AdminUserController::class, 'getAdminStats'])
            ->name('admin.api.my-stats');
    });
});

/*
|--------------------------------------------------------------------------
| ROUTES DE COMPATIBILITÉ (Maintien de l'existant)
|--------------------------------------------------------------------------
*/

// Redirection de l'ancienne route /app vers la nouvelle logique
Route::middleware(['auth', 'check.user.status'])->group(function () {
    Route::get('/app', function () {
        return redirect()->route('dashboard');
    });
});

/*
|--------------------------------------------------------------------------
| ROUTES DE DÉVELOPPEMENT (À supprimer en production)
|--------------------------------------------------------------------------
*/

if (app()->environment('local')) {
    // Route de test pour vérifier les relations
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
    
    // Route de test pour les statistiques
    Route::get('/dev/test-stats', function () {
        return response()->json([
            'total_users' => \App\Models\User::count(),
            'total_relations' => \App\Models\AdministratorUser::count(),
            'admins' => \App\Models\User::where('user_type_id', 1)->count(),
            'users' => \App\Models\User::where('user_type_id', 2)->count(),
        ]);
    })->middleware('admin');
}

/*
|--------------------------------------------------------------------------
| GESTION DES ERREURS
|--------------------------------------------------------------------------
*/

// Route pour les pages non trouvées
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