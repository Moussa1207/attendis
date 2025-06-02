<?php
// VERSION FINALE CORRIGÉE

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\UserManagementController;

// Routes publiques
Route::get('/', function () {
    return redirect()->route('login');
});

// Routes d'authentification
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Routes d'inscription (réservé aux futurs admins)
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.post');

// Routes protégées par authentification
Route::middleware(['auth', 'check.user.status'])->group(function () {
    
    // Dashboard Admin
    Route::get('/admin/dashboard', [DashboardController::class, 'adminDashboard'])
        ->middleware('admin')
        ->name('layouts.app');
    
    // Dashboard Utilisateur
    Route::get('/user/dashboard', [UserDashboardController::class, 'userDashboard'])
        ->name('layouts.app-users');
    
    // Routes communes (profil)
    Route::get('/profile', [UserDashboardController::class, 'profile'])->name('user.profile');
    Route::patch('/profile', [UserDashboardController::class, 'updateProfile'])->name('user.profile.update');
    
    // Routes admin uniquement
    Route::middleware('admin')->group(function () {
        // Gestion des utilisateurs
        Route::get('/admin/users', [DashboardController::class, 'usersList'])->name('user.users-list');
        Route::get('/admin/users/create', [UserManagementController::class, 'create'])->name('admin.users.create');
        Route::post('/admin/users', [UserManagementController::class, 'store'])->name('admin.users.store');
        
        // Actions sur les utilisateurs
        Route::patch('/admin/users/{user}/activate', [DashboardController::class, 'activateUser'])->name('admin.users.activate');
        Route::patch('/admin/users/{user}/suspend', [DashboardController::class, 'suspendUser'])->name('admin.users.suspend');
        
        // API pour les fonctionnalités AJAX
        Route::prefix('api/admin')->group(function () {
            Route::get('/stats', [DashboardController::class, 'getStats'])->name('api.stats');
            Route::get('/search-users', [DashboardController::class, 'searchUsers'])->name('api.search.users');
            Route::post('/bulk-activate', [DashboardController::class, 'bulkActivate'])->name('api.bulk.activate');
            Route::get('/my-users', [UserManagementController::class, 'myCreatedUsers'])->name('api.admin.my-users');
            Route::get('/my-stats', [UserManagementController::class, 'getMyUserStats'])->name('api.admin.my-stats');
        });
    });
    
    // API pour les utilisateurs
    Route::prefix('api/user')->group(function () {
        Route::get('/info', [UserDashboardController::class, 'getUserInfo'])->name('api.user.info');
    });
});