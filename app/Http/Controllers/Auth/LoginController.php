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

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

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

            // NOUVELLE LOGIQUE : Vérifier si l'utilisateur doit changer son mot de passe
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

        return redirect()->route('login')
            ->with('error', 'Les identifiants saisis sont incorrects.')
            ->withInput($request->only('email'));
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
     * Traiter le changement de mot de passe obligatoire
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
            // Valider la force du mot de passe (même logique que PasswordManagementController)
            $passwordStrength = $this->validatePasswordStrength($request->password);
            
            if (!$passwordStrength['is_valid']) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Le mot de passe ne respecte pas les critères de sécurité requis.');
            }

            // Mettre à jour le mot de passe
            $user->update([
                'password' => Hash::make($request->password)
            ]);

            // Marquer que le password reset n'est plus requis
            $user->markPasswordChanged();

            // Connecter l'utilisateur automatiquement
            Auth::login($user);

            // Nettoyer la session
            session()->forget('user_must_change_password');

            // Log de l'activité
            \Log::info('Mandatory password change completed', [
                'user_id' => $user->id,
                'username' => $user->username,
                'ip' => $request->ip(),
            ]);

            // Redirection vers le dashboard utilisateur
            return redirect()->route('layouts.app-users')
                ->with('success', 'Bienvenue ' . $user->username . ' ! Votre mot de passe a été mis à jour avec succès.');

        } catch (\Exception $e) {
            \Log::error('Mandatory password change error', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors du changement de mot de passe. Veuillez réessayer.');
        }
    }

    /**
     * Valider la force du mot de passe (même logique que PasswordManagementController)
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

    public function logout(Request $request)
    {
        $userName = Auth::user()->username ?? '';
       
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login')
            ->with('success', 'Au revoir ' . $userName . ' ! Vous avez été déconnecté avec succès.');
    }
}