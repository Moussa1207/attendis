<?php
// ========================================
// 1. CORRECTION : UserDashboardController.php
// ========================================

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UserDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.user.status');
    }

    /**
     * Dashboard pour les utilisateurs normaux (app-users)
     */
    public function userDashboard()
    {
        $user = Auth::user();

        // Si c'est un admin, rediriger vers le dashboard admin
        if ($user->isAdmin()) {
            return redirect()->route('layouts.app');
        }

        // Récupérer l'administrateur qui a créé cet utilisateur
        $createdByAdmin = $user->createdBy();

        // Statistiques personnalisées pour l'utilisateur
        $userStats = [
            'account_age_days' => $user->created_at->diffInDays(now()),
            'last_login' => $user->updated_at->format('d/m/Y H:i'),
            'created_by_admin' => $createdByAdmin,
        ];

        return view('layouts.app-users', compact('userStats'));
    }

    /**
     * Profil utilisateur
     */
    public function profile()
    {
        $user = Auth::user();
        
        return view('user.profile', compact('user'));
    }

    /**
     * Mettre à jour le profil
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'mobile_number' => 'required|string|max:20',
        ], [
            'username.unique' => 'Ce nom d\'utilisateur est déjà pris.',
        ]);

        try {
            $user->update([
                'username' => $request->username,
                'mobile_number' => $request->mobile_number,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Profil mis à jour avec succès !',
                    'user' => [
                        'username' => $user->username,
                        'mobile_number' => $user->mobile_number,
                    ]
                ]);
            }

            return redirect()->back()->with('success', 'Profil mis à jour avec succès !');

        } catch (\Exception $e) {
            $errorMessage = 'Erreur lors de la mise à jour : ' . $e->getMessage();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }
            
            return redirect()->back()->with('error', $errorMessage);
        }
    }

    /**
     * Obtenir les informations de l'utilisateur connecté
     */
    public function getUserInfo(Request $request)
    {
        try {
            $user = Auth::user();
            $createdBy = $user->createdBy();

            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'mobile_number' => $user->mobile_number,
                    'type' => $user->getTypeName(),
                    'status' => $user->getStatusName(),
                    'created_at' => $user->created_at->format('d/m/Y H:i'),
                    'created_by' => $createdBy ? [
                        'id' => $createdBy->id,
                        'username' => $createdBy->username,
                        'email' => $createdBy->email,
                    ] : null,
                    'account_age_days' => $user->created_at->diffInDays(now()),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des informations'
            ], 500);
        }
    }
}