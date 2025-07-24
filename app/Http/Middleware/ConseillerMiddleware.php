<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * 👨‍💼 MIDDLEWARE CONSEILLER - VERSION CORRIGÉE
 * Sécurise l'accès aux routes conseiller sans créer de boucles
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

            // ✅ ÉVITER LES BOUCLES : Utiliser les vues directement au lieu de redirections
            if ($user->isAdmin()) {
                // Créer une réponse de vue admin avec un message d'erreur
                return response()->view('layouts.app', [
                    'error' => 'Interface conseiller non accessible aux administrateurs.',
                    'stats' => [],
                    'personalStats' => [],
                    'recentActivity' => collect(),
                    'pendingUsers' => collect(),
                    'recentTickets' => collect()
                ])->setStatusCode(403);
            } else {
                // Créer une réponse de vue utilisateur avec un message d'erreur
                return response()->view('layouts.app-users', [
                    'error' => 'Accès réservé aux conseillers.',
                    'userInfo' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'type_info' => $user->getTypeInfo(),
                        'status_info' => $user->getStatusInfo()
                    ]
                ])->setStatusCode(403);
            }
        }

        // Vérifier que l'utilisateur a un créateur (admin parent)
        try {
            $creator = $user->getCreator();
            if (!$creator) {
                \Log::warning('Conseiller sans créateur détecté', [
                    'user_id' => $user->id,
                    'username' => $user->username
                ]);

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Configuration manquante'
                    ], 500);
                }
               
                // ✅ RETOURNER UNE VUE AVEC ERREUR au lieu de redirection
                return response()->view('layouts.app-conseiller', [
                    'error' => 'Configuration manquante : administrateur créateur introuvable.',
                    'userInfo' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email
                    ],
                    'defaultWaitTime' => 15 // Valeur par défaut
                ])->setStatusCode(500);
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
               
                // ✅ RETOURNER UNE VUE AVEC WARNING au lieu de redirection
                return response()->view('layouts.app-conseiller', [
                    'warning' => 'Votre administrateur n\'a pas encore créé de services.',
                    'userInfo' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email
                    ],
                    'defaultWaitTime' => 15, // Valeur par défaut
                    'fileStats' => [
                        'tickets_en_attente' => 0,
                        'tickets_en_cours' => 0,
                        'tickets_termines' => 0,
                        'temps_attente_moyen' => 15
                    ],
                    'conseillerStats' => [
                        'tickets_traites_aujourd_hui' => 0,
                        'temps_moyen_traitement' => 0,
                        'ticket_en_cours' => null,
                        'premier_ticket_du_jour' => null,
                        'is_en_pause' => false
                    ],
                    'creatorInfo' => [
                        'username' => $creator->username,
                        'company' => $creator->company,
                        'services_count' => 0
                    ]
                ])->setStatusCode(400);
            }

            // ✅ Tout est OK, continuer
            return $next($request);

        } catch (\Exception $e) {
            \Log::error('Erreur dans ConseillerMiddleware', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de configuration'
                ], 500);
            }

            // ✅ En cas d'erreur, retourner une vue d'erreur au lieu de redirection
            return response()->view('layouts.app-conseiller', [
                'error' => 'Erreur de configuration. Contactez votre administrateur.',
                'userInfo' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email
                ],
                'defaultWaitTime' => 15
            ])->setStatusCode(500);
        }
    }
}