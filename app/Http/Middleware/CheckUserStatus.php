<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserStatus
{
    public function handle(Request $request, Closure $next)
    {
        // ✅ ÉVITER LES BOUCLES : Ne pas appliquer sur les routes de login/logout
        if ($request->routeIs('login', 'home', 'logout', 'password.*')) {
            return $next($request);
        }

        // ✅ ÉVITER LES BOUCLES : Ne pas appliquer sur les routes API publiques
        if ($request->is('api/settings/public') || $request->is('api/settings/check/*')) {
            return $next($request);
        }

        if (Auth::check()) {
            $user = Auth::user();
            
            try {
                // Vérifier le statut utilisateur
                if (!$user->isActive()) {
                    // Déconnexion propre
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    
                    // ✅ REDIRECTION SÉCURISÉE : Une seule redirection vers login
                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Votre compte a été désactivé. Contactez un administrateur.',
                            'redirect' => route('login')
                        ], 401);
                    }
                    
                    return redirect()->route('login')
                        ->with('error', 'Votre compte a été désactivé. Contactez un administrateur.');
                }

                // Vérifier si l'utilisateur est suspendu
                if ($user->isSuspended()) {
                    // Déconnexion propre
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    
                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Votre compte a été suspendu. Contactez un administrateur.',
                            'redirect' => route('login')
                        ], 401);
                    }
                    
                    return redirect()->route('login')
                        ->with('error', 'Votre compte a été suspendu. Contactez un administrateur.');
                }

            } catch (\Exception $e) {
                // En cas d'erreur de vérification du statut, déconnecter par sécurité
                \Log::error('Erreur vérification statut utilisateur', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
                
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Erreur de vérification du compte. Veuillez vous reconnecter.',
                        'redirect' => route('login')
                    ], 500);
                }
                
                return redirect()->route('login')
                    ->with('error', 'Erreur de vérification du compte. Veuillez vous reconnecter.');
            }
        }
        
        return $next($request);
    }
}