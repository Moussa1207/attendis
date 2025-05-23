<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Auth;


Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.post');

// Routes protégées
Route::middleware(['auth', 'check.user.status'])->group(function () {
    // Dashboard utilisateur
    Route::get('/app', [DashboardController::class, 'userDashboard'])->name('layouts.app');
    
    // Routes admin
    Route::middleware('admin')->prefix('admin')->name('layouts.')->group(function () {
        Route::get('/app', [DashboardController::class, 'adminDashboard'])->name('app');
        Route::get('/users', [DashboardController::class, 'manageUsers'])->name('layouts.app');
        Route::patch('/users/{user}/activate', [DashboardController::class, 'activateUser'])->name('users.activate');
        Route::patch('/users/{user}/suspend', [DashboardController::class, 'suspendUser'])->name('users.suspend');
    });
});