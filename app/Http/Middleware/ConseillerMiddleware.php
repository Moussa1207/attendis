<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * 👨‍💼 MIDDLEWARE CONSEILLER
 * Sécurise l'accès aux routes conseiller
 */
class ConseillerMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier l'authentification
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Non authentifié'
                ], 401);
            }
            
            return redirect()->route('login')
                ->with('error', 'Veuillez vous connecter pour accéder à l\'interface conseiller.');
        }

        $user = Auth::user();

        // Vérifier le statut du compte
        if ($user->isInactive()) {
            Auth::logout();
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Compte inactif'
                ], 403);
            }
            
            return redirect()->route('login')
                ->with('error', 'Votre compte n\'est pas activé.');
        }

        if ($user->isSuspended()) {
            Auth::logout();
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Compte suspendu'
                ], 403);
            }
            
            return redirect()->route('login')
                ->with('error', 'Votre compte a été suspendu.');
        }

        // 🎯 VÉRIFICATION PRINCIPALE : Seuls les conseillers peuvent accéder
        if (!$user->isConseillerUser()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès réservé aux conseillers'
                ], 403);
            }

            // Rediriger selon le type d'utilisateur
            if ($user->isAdmin()) {
                return redirect()->route('layouts.app')
                    ->with('warning', 'Interface conseiller non accessible aux administrateurs.');
            } else {
                return redirect()->route('layouts.app-users')
                    ->with('warning', 'Accès réservé aux conseillers.');
            }
        }

        // Vérifier que l'utilisateur a un créateur (admin parent)
        $creator = $user->getCreator();
        if (!$creator) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Configuration manquante'
                ], 500);
            }
            
            return redirect()->route('layouts.app-users')
                ->with('error', 'Configuration manquante : administrateur créateur introuvable.');
        }

        // Vérifier que l'admin créateur a des services
        $hasServices = $creator->createdServices()->exists();
        if (!$hasServices) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun service disponible'
                ], 400);
            }
            
            return redirect()->route('layouts.app-users')
                ->with('warning', 'Votre administrateur n\'a pas encore créé de services.');
        }

        // ✅ Tout est OK, continuer
        return $next($request);
    }
}