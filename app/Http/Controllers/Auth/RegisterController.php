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

        // Inscription réservée aux admins uniquement
        $isFirstUser = User::count() === 0;
        
        // role du nouvel inscrit
        $userTypeId = 1; // 1 = Admin TOUJOURS
        
        // Statut selon si c'est le premier utilisateur ou non
        $statusId = $isFirstUser ? 2 : 1; // Premier = Actif
        
        // Créer l'utilisateur: administrateur
        $user = User::create([
            'email' => $request->email,
            'username' => $request->username,
            'mobile_number' => $request->mobile_number,
            'password' => Hash::make($request->password),
            'user_type_id' => $userTypeId, // TOUJOURS Admin
            'status_id' => $statusId,
        ]);


        return redirect()->route('login')->with('success', $message);
    }
}