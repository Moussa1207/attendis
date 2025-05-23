<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function adminDashboard()
    {
        // Vérifier que l'utilisateur est bien admin
        if (!Auth::user()->isAdmin()) {
            return redirect()->route('user.dashboard')
                ->with('error', 'Accès non autorisé à la zone administrateur.');
        }

        // Statistiques pour le dashboard admin
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('status_id', 2)->count(),
            'inactive_users' => User::where('status_id', 1)->count(),
            'suspended_users' => User::where('status_id', 3)->count(),
            'admin_users' => User::where('user_type_id', 1)->count(),
        ];

        return view('layouts.app', compact('stats'));
    }

    public function userDashboard()
    {
        // Vérifier que l'utilisateur n'est pas admin (optionnel)
        if (Auth::user()->isAdmin()) {
            return redirect()->route('layouts.app');
        }

        return view('user.dashboard');
    }

    // Méthode pour gérer les utilisateurs (admin seulement)
    public function manageUsers()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Accès non autorisé');
        }

        $users = User::with(['userType', 'status'])
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);

        return view('admin.users.index', compact('users'));
    }

    // Méthode pour activer un utilisateur
    public function activateUser(User $user)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Accès non autorisé');
        }

        $user->activate();
        
        return redirect()->back()
            ->with('success', 'L\'utilisateur ' . $user->username . ' a été activé avec succès.');
    }

    // Méthode pour suspendre un utilisateur
    public function suspendUser(User $user)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Accès non autorisé');
        }

        // Empêcher de suspendre le dernier admin
        if ($user->isAdmin() && User::where('user_type_id', 1)->where('status_id', 2)->count() <= 1) {
            return redirect()->back()
                ->with('error', 'Impossible de suspendre le dernier administrateur actif.');
        }

        $user->suspend();
        
        return redirect()->back()
            ->with('success', 'L\'utilisateur ' . $user->username . ' a été suspendu.');
    }
}