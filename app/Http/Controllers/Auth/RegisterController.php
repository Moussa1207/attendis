<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;


class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.login');
    }

    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:255|unique:users',
            'username' => 'required|string|max:255|unique:users',
            'mobile_number' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'username.unique' => 'Ce nom d\'utilisateur est déjà pris.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);

        // Vérifier si c'est le premier utilisateur (sera admin)
        $isFirstUser = User::count() === 0;
        
        // Déterminer le type d'utilisateur
        $userTypeId = $isFirstUser ? 1 : 2; // 1 = Admin, 2 = User normal
        
        // Créer l'utilisateur
        $user = User::create([
            'email' => $request->email,
            'username' => $request->username,
            'mobile_number' => $request->mobile_number,
            'password' => Hash::make($request->password),
            'user_type_id' => $userTypeId,
            'status_id' => 1, // 1 = Inactif par défaut
        ]);

        // Message personnalisé selon le type d'utilisateur
        if ($isFirstUser) {
            $message = 'Félicitations ! Vous êtes le premier administrateur. Votre compte est en attente d\'activation.';
        } else {
            $message = 'Inscription réussie ! Votre compte est en attente d\'activation par un administrateur.';
        }

        return redirect()->route('login')->with('success', $message);
    }
}