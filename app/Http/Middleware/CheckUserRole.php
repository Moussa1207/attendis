<?php
// app/Http/Middleware/CheckUserRole.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserRole
{
    public function handle(Request $request, Closure $next, $role)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        $user = Auth::user();
        
        if ($role === 'admin' && !$user->isAdmin()) {
            return redirect()->route('user.dashboard')
                ->with('error', 'Vous n\'avez pas accès à cette section.');
        }
        
        if ($role === 'user' && $user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        
        if (!$user->isActive()) {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Votre compte est inactif. Contactez l\'administrateur.');
        }
        
        return $next($request);
    }
}