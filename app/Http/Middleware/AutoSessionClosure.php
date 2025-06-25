<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;

class AutoSessionClosure
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Vérifier seulement si l'utilisateur est connecté
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Les admins ne sont pas affectés par la fermeture automatique
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Vérifier si la fermeture automatique est activée
        if (!Setting::isAutoSessionClosureEnabled()) {
            return $next($request);
        }

        // Vérifier si c'est l'heure de fermeture
        if (Setting::shouldCloseSessionsNow()) {
            // Déconnecter l'utilisateur
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Log de l'action
            \Log::info('User automatically logged out due to session closure time', [
                'user_id' => $user->id,
                'username' => $user->username,
                'closure_time' => Setting::getSessionClosureTime(),
                'ip' => $request->ip()
            ]);

            // Rediriger vers la page de connexion avec un message
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Votre session a été fermée automatiquement selon la configuration du système.',
                    'redirect' => route('login')
                ], 401);
            }

            return redirect()->route('login')
                ->with('warning', 'Votre session a été fermée automatiquement. Les connexions sont fermées après ' . Setting::getSessionClosureTime() . '.');
        }

        return $next($request);
    }
}