<?php
// app/Http/Controllers/Auth/RegisterController.php

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

        // NOUVEAU CONCEPT : Tous les inscrits deviennent automatiquement administrateurs
        $user = User::create([
            'email' => $request->email,
            'username' => $request->username,
            'mobile_number' => $request->mobile_number,
            'password' => Hash::make($request->password),
            'user_type_id' => 1, // 1 = Administrateur (toujours admin)
            'status_id' => 2, // 2 = Actif (directement actif)
        ]);

        // Message de confirmation
        $message = 'Félicitations ! Votre compte administrateur a été créé avec succès. Vous pouvez maintenant vous connecter.';

        return redirect()->route('login')->with('success', $message);
    }
}