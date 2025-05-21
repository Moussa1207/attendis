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
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'mobile_number' => 'required|string|max:20',
        ]);
        
        // Créer l'utilisateur
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'mobile_number' => $request->mobile_number,
            'user_type_id' => 2, // Utilisateur normal par défaut
            'status_id' => 1, // Actif par défaut
        ]);
        
        // Connecter l'utilisateur après inscription
        Auth::login($user);
        
        return redirect()->route('dashboard.index');
    }
}