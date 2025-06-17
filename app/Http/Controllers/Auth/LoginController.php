<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * ✅ AMÉLIORÉ : Gestion de la connexion avec tracking complet
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'Veuillez saisir une adresse email valide.',
            'password.required' => 'Le mot de passe est obligatoire.',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember');

        // ✅ NOUVEAU : Vérifier si l'utilisateur existe et s'il est verrouillé AVANT la tentative
        $user = User::where('email', $credentials['email'])->first();
        
        if ($user && $user->isLockedOut()) {
            \Log::warning('Login attempt on locked account', [
                'user_id' => $user->id,
                'username' => $user->username,
                'email' => $credentials['email'],
                'failed_attempts' => $user->failed_login_attempts,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('login')
                ->with('error', 'Votre compte est temporairement verrouillé en raison de trop nombreuses tentatives de connexion. Contactez un administrateur.')
                ->withInput($request->only('email'));
        }

        // Tentative de connexion
        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            // ✅ NOUVEAU : Enregistrer la connexion réussie avec tracking complet
            $this->recordSuccessfulLogin($user, $request);

            // Vérifier le statut de l'utilisateur
            if ($user->isInactive()) {
                Auth::logout();
                return redirect()->route('login')
                    ->with('error', 'Votre compte n\'est pas encore activé. Contactez un administrateur.');
            }

            if ($user->isSuspended()) {
                Auth::logout();
                return redirect()->route('login')
                    ->with('error', 'Votre compte a été suspendu. Contactez un administrateur.');
            }

            // Régénérer la session pour la sécurité
            $request->session()->regenerate();

            // LOGIQUE EXISTANTE : Vérifier si l'utilisateur doit changer son mot de passe
            if ($user->mustChangePassword()) {
                // Stocker temporairement l'ID utilisateur en session
                session(['user_must_change_password' => $user->id]);
                
                return redirect()->route('password.mandatory-change')
                    ->with('info', 'Vous devez changer votre mot de passe avant d\'accéder à votre compte.');
            }

            // LOGIQUE DE REDIRECTION NORMALE
            if ($user->isAdmin()) {
                return redirect()->route('layouts.app')
                    ->with('success', 'Bienvenue ' . $user->username . ' ! Vous êtes connecté en tant qu\'administrateur.');
            } else {
                return redirect()->route('layouts.app-users')
                    ->with('success', 'Bienvenue ' . $user->username . ' !');
            }
        }

        // ✅ NOUVEAU : Enregistrer la tentative échouée avec tracking
        $this->recordFailedLogin($request, $credentials['email']);

        return redirect()->route('login')
            ->with('error', 'Les identifiants saisis sont incorrects.')
            ->withInput($request->only('email'));
    }

    /**
     * ✅ NOUVEAU : Enregistrer une connexion réussie avec tracking complet
     */
    private function recordSuccessfulLogin(User $user, Request $request): void
    {
        try {
            $user->update([
                'last_login_at' => now(),
                'failed_login_attempts' => 0, // Remettre à zéro les échecs
                'last_login_ip' => $request->ip(),
                'last_user_agent' => $request->userAgent(),
            ]);

            \Log::info('Successful login recorded', [
                'user_id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'previous_login' => $user->last_login_at ? $user->last_login_at->toISOString() : 'never',
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to record successful login', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * ✅ NOUVEAU : Enregistrer une tentative de connexion échouée avec tracking
     */
    private function recordFailedLogin(Request $request, string $email): void
    {
        try {
            $user = User::where('email', $email)->first();
            
            if ($user) {
                $user->increment('failed_login_attempts');
                
                \Log::warning('Failed login attempt recorded', [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'email' => $email,
                    'failed_attempts' => $user->failed_login_attempts,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'timestamp' => now()->toISOString(),
                    'will_be_locked' => $user->failed_login_attempts >= 5
                ]);

                // ✅ SÉCURITÉ : Avertissement si le compte va être verrouillé
                if ($user->failed_login_attempts >= 5) {
                    \Log::warning('User account locked due to failed attempts', [
                        'user_id' => $user->id,
                        'username' => $user->username,
                        'total_failed_attempts' => $user->failed_login_attempts,
                        'locked_at' => now()->toISOString()
                    ]);
                }
            } else {
                // Tentative sur un email inexistant
                \Log::warning('Failed login attempt on non-existent email', [
                    'email' => $email,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'timestamp' => now()->toISOString()
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to record failed login attempt', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Afficher la page de changement de mot de passe obligatoire
     */
    public function showMandatoryPasswordChange()
    {
        // Vérifier que l'utilisateur est bien en attente de changement
        if (!session('user_must_change_password')) {
            return redirect()->route('login')
                ->with('error', 'Session expirée. Veuillez vous reconnecter.');
        }

        $user = User::find(session('user_must_change_password'));
        
        if (!$user || !$user->mustChangePassword()) {
            session()->forget('user_must_change_password');
            return redirect()->route('login')
                ->with('error', 'Aucun changement de mot de passe requis.');
        }

        return view('auth.mandatory-password-change', compact('user'));
    }

    /**
     * ✅ AMÉLIORÉ : Traiter le changement de mot de passe obligatoire avec tracking
     */
    public function updateMandatoryPassword(Request $request)
    {
        // Validation
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.required' => 'Le nouveau mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        if (!session('user_must_change_password')) {
            return redirect()->route('login')
                ->with('error', 'Session expirée. Veuillez vous reconnecter.');
        }

        $user = User::find(session('user_must_change_password'));
        
        if (!$user) {
            session()->forget('user_must_change_password');
            return redirect()->route('login')
                ->with('error', 'Utilisateur introuvable.');
        }

        try {
            // Valider la force du mot de passe (votre logique existante)
            $passwordStrength = $this->validatePasswordStrength($request->password);
            
            if (!$passwordStrength['is_valid']) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Le mot de passe ne respecte pas les critères de sécurité requis.');
            }

            // ✅ AMÉLIORÉ : Mettre à jour le mot de passe avec tracking de la date
            $user->update([
                'password' => Hash::make($request->password),
                'last_password_change' => now(), // ✅ NOUVEAU : Tracking de la date de changement
                'failed_login_attempts' => 0, // ✅ NOUVEAU : Réinitialiser les tentatives échouées
            ]);

            // Marquer que le password reset n'est plus requis
            $user->markPasswordChanged();

            // Connecter l'utilisateur automatiquement
            Auth::login($user);

            // ✅ NOUVEAU : Enregistrer cette connexion comme réussie
            $this->recordSuccessfulLogin($user, $request);

            // Nettoyer la session
            session()->forget('user_must_change_password');

            // ✅ AMÉLIORÉ : Log plus détaillé
            \Log::info('Mandatory password change completed successfully', [
                'user_id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'password_strength' => $passwordStrength['text'],
                'timestamp' => now()->toISOString()
            ]);

            // Redirection vers le dashboard utilisateur
            return redirect()->route('layouts.app-users')
                ->with('success', 'Bienvenue ' . $user->username . ' ! Votre mot de passe a été mis à jour avec succès.');

        } catch (\Exception $e) {
            \Log::error('Mandatory password change error', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors du changement de mot de passe. Veuillez réessayer.');
        }
    }

    /**
     * Valider la force du mot de passe (votre logique existante conservée)
     */
    private function validatePasswordStrength($password): array
    {
        $score = 0;
        $feedback = [];

        if (strlen($password) >= 8) $score += 1;
        else $feedback[] = 'Au moins 8 caractères';

        if (strlen($password) >= 12) $score += 1;

        if (preg_match('/[a-z]/', $password)) $score += 1;
        else $feedback[] = 'Lettres minuscules';

        if (preg_match('/[A-Z]/', $password)) $score += 1;
        else $feedback[] = 'Lettres majuscules';

        if (preg_match('/[0-9]/', $password)) $score += 1;
        else $feedback[] = 'Chiffres';

        if (preg_match('/[^a-zA-Z0-9]/', $password)) $score += 1;
        else $feedback[] = 'Caractères spéciaux';

        if ($score < 3) {
            $level = 'weak';
            $color = 'danger';
            $text = 'Faible';
        } elseif ($score < 5) {
            $level = 'medium';
            $color = 'warning';
            $text = 'Moyen';
        } elseif ($score < 6) {
            $level = 'strong';
            $color = 'info';
            $text = 'Fort';
        } else {
            $level = 'very_strong';
            $color = 'success';
            $text = 'Très fort';
        }

        return [
            'score' => $score,
            'level' => $level,
            'color' => $color,
            'text' => $text,
            'feedback' => $feedback,
            'is_valid' => $score >= 4
        ];
    }

    /**
     * ✅ AMÉLIORÉ : Déconnexion avec logging détaillé
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        $userName = $user->username ?? '';
        
        // ✅ NOUVEAU : Log détaillé de la déconnexion
        if ($user) {
            \Log::info('User logout', [
                'user_id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'session_duration' => $user->last_login_at ? 
                    now()->diffInMinutes($user->last_login_at) . ' minutes' : 'unknown',
                'logout_timestamp' => now()->toISOString()
            ]);
        }
       
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login')
            ->with('success', 'Au revoir ' . $userName . ' ! Vous avez été déconnecté avec succès.');
    }

    /**
     * ✅ NOUVEAU : Méthode utilitaire pour débloquer un compte (pour les admins)
     * Cette méthode peut être appelée par les administrateurs pour débloquer un compte
     */
    public function unlockAccount(Request $request, User $user)
    {
        // Vérifier que l'utilisateur connecté est admin
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'Action non autorisée');
        }

        try {
            $user->update(['failed_login_attempts' => 0]);
            
            \Log::info('Account unlocked by admin', [
                'unlocked_user_id' => $user->id,
                'unlocked_username' => $user->username,
                'admin_id' => Auth::id(),
                'admin_username' => Auth::user()->username,
                'ip' => $request->ip(),
                'timestamp' => now()->toISOString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Compte de {$user->username} débloqué avec succès"
                ]);
            }

            return redirect()->back()
                ->with('success', "Compte de {$user->username} débloqué avec succès.");

        } catch (\Exception $e) {
            \Log::error('Failed to unlock account', [
                'user_id' => $user->id,
                'admin_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors du déblocage du compte'
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Erreur lors du déblocage du compte.');
        }
    }
}