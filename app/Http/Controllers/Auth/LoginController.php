<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

            //  LOGIQUE DE REDIRECTION
            if ($user->isAdmin()) {
                // Les admins  sur le dashboard admin (layouts/app)
                return redirect()->route('layouts.app')
                    ->with('success', 'Bienvenue ' . $user->username . ' ! Vous êtes connecté en tant qu\'administrateur.');
            } else {
                // Les utilisateurs normaux  sur app-users
                return redirect()->route('layouts.app-users')
                    ->with('success', 'Bienvenue ' . $user->username . ' !');
            }
        }

        return redirect()->route('login')
            ->with('error', 'Les identifiants saisis sont incorrects.')
            ->withInput($request->only('email'));
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