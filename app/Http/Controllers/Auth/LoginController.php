<?php
// app/Http/Controllers/Auth/LoginController.php
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
            'username' => 'required',
            'password' => 'required',
        ]);
        
        $credentials = $request->only('username', 'password');
        
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // Vérifier si l'utilisateur est actif
            if (!$user->isActive()) {
                Auth::logout();
                return redirect()->route('login')
                    ->with('error', 'Votre compte est inactif. Contactez l\'administrateur.');
            }
            
            // Redirection en fonction du type d'utilisateur
            if ($user->isAdmin()) {
                return redirect()->route('dashboard_layout.index');
            } else {
                return redirect()->route('dashboard_layout.index');
            }
        }
        
        return redirect()->route('login')
            ->with('error', 'Les identifiants sont incorrects.');
    }
    
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }
}