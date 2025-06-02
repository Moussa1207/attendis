<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AdministratorUser;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Dashboard principal admin (layouts/app.blade.php)
     * Accessible uniquement aux administrateurs
     */
    public function adminDashboard()
    {
        // Vérifier que l'utilisateur est bien admin
        if (!Auth::user()->isAdmin()) {
            // Si c'est un utilisateur normal, rediriger vers app-users
            return redirect()->route('app-users')
                ->with('error', 'Accès réservé aux administrateurs.');
        }

        // Statistiques pour le dashboard admin
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('status_id', 2)->count(),
            'inactive_users' => User::where('status_id', 1)->count(),
            'suspended_users' => User::where('status_id', 3)->count(),
            'admin_users' => User::where('user_type_id', 1)->count(),
            'normal_users' => User::where('user_type_id', 2)->count(),
            // Nouvelles stats pour les créations
            'my_created_users' => Auth::user()->createdUsers()->count(),
            'my_active_created' => Auth::user()->createdUsers()->where('status_id', 2)->count(),
        ];

        // layouts/app.blade.php sert de dashboard admin avec les stats
        return view('layouts.app', compact('stats'));
    }

    /**
     * ANCIENNE MÉTHODE - Maintenue pour compatibilité
     * Redirige vers adminDashboard ou app-users selon le rôle
     */
    public function userDashboard()
    {
        $user = Auth::user();
        
        if ($user->isAdmin()) {
            return redirect()->route('layouts.app');
        } else {
            return redirect()->route('app-users');
        }
    }

    /**
     * Liste complète des utilisateurs avec filtres (page dédiée)
     * Accessible via le menu "Utilisateurs" > "Liste"
     */
    public function usersList(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Accès non autorisé');
        }

        $query = User::with(['userType', 'status']);

        // Filtrage par recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile_number', 'like', "%{$search}%");
            });
        }

        // Filtrage par statut
        if ($request->filled('status')) {
            $statusMap = [
                'active' => 2,
                'inactive' => 1,
                'suspended' => 3
            ];
            if (isset($statusMap[$request->status])) {
                $query->where('status_id', $statusMap[$request->status]);
            }
        }

        // Filtrage par type
        if ($request->filled('type')) {
            $typeMap = [
                'admin' => 1,
                'user' => 2
            ];
            if (isset($typeMap[$request->type])) {
                $query->where('user_type_id', $typeMap[$request->type]);
            }
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('user.users-list', compact('users'));
    }

    /**
     * Liste des utilisateurs pour le dashboard admin (version simple)
     * ANCIENNE MÉTHODE - Maintenue pour compatibilité
     */
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

    /**
     * Activer un utilisateur (support AJAX et redirection classique)
     */
    public function activateUser(User $user, Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Accès non autorisé'
                ], 403);
            }
            abort(403, 'Accès non autorisé');
        }

        try {
            $user->activate();
            
            $message = 'L\'utilisateur ' . $user->username . ' a été activé avec succès.';
            
            // Réponse AJAX
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true, 
                    'message' => $message,
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'status' => 'active',
                        'status_name' => $user->getStatusName()
                    ]
                ]);
            }
            
            // Redirection classique
            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            $errorMessage = 'Erreur lors de l\'activation : ' . $e->getMessage();
            
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
     * Suspendre un utilisateur (support AJAX et redirection classique)
     */
    public function suspendUser(User $user, Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Accès non autorisé'
                ], 403);
            }
            abort(403, 'Accès non autorisé');
        }

        try {
            // Empêcher de suspendre le dernier admin actif
            if ($user->isAdmin()) {
                $activeAdmins = User::where('user_type_id', 1)
                                  ->where('status_id', 2)
                                  ->count();
                
                if ($activeAdmins <= 1) {
                    $message = 'Impossible de suspendre le dernier administrateur actif.';
                    
                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false, 
                            'message' => $message
                        ], 400);
                    }
                    
                    return redirect()->back()->with('error', $message);
                }
            }

            $user->suspend();
            
            $message = 'L\'utilisateur ' . $user->username . ' a été suspendu.';
            
            // Réponse AJAX
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true, 
                    'message' => $message,
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'status' => 'suspended',
                        'status_name' => $user->getStatusName()
                    ]
                ]);
            }
            
            // Redirection classique
            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            $errorMessage = 'Erreur lors de la suspension : ' . $e->getMessage();
            
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
     * Obtenir les statistiques en temps réel (AJAX)
     */
    public function getStats(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false, 
                'message' => 'Accès non autorisé'
            ], 403);
        }

        try {
            $admin = Auth::user();
            
            $stats = [
                // Statistiques générales
                'total_users' => User::count(),
                'active_users' => User::where('status_id', 2)->count(),
                'inactive_users' => User::where('status_id', 1)->count(),
                'suspended_users' => User::where('status_id', 3)->count(),
                'admin_users' => User::where('user_type_id', 1)->count(),
                'normal_users' => User::where('user_type_id', 2)->count(),
                
                // Nouvelles statistiques pour les créations de l'admin connecté
                'my_created_total' => $admin->createdUsers()->count(),
                'my_created_active' => $admin->createdUsers()->where('status_id', 2)->count(),
                'my_created_admins' => $admin->createdUsers()->where('user_type_id', 1)->count(),
                'my_created_users' => $admin->createdUsers()->where('user_type_id', 2)->count(),
                
                // Statistiques de tous les administrateurs
                'total_administrators' => User::where('user_type_id', 1)->count(),
                'total_created_by_admins' => AdministratorUser::count(),
            ];

            return response()->json([
                'success' => true, 
                'stats' => $stats
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Erreur lors de la récupération des statistiques'
            ], 500);
        }
    }

    /**
     * Recherche d'utilisateurs en temps réel (AJAX)
     */
    public function searchUsers(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false, 
                'message' => 'Accès non autorisé'
            ], 403);
        }

        $search = $request->get('q', '');
        
        if (strlen($search) < 2) {
            return response()->json([
                'success' => true,
                'suggestions' => []
            ]);
        }

        try {
            $users = User::with(['userType', 'status'])
                        ->where('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('mobile_number', 'like', "%{$search}%")
                        ->limit(5)
                        ->get();

            $suggestions = $users->map(function($user) {
                return [
                    'id' => $user->id,
                    'text' => $user->username,
                    'email' => $user->email,
                    'phone' => $user->mobile_number,
                    'type' => $user->getTypeName(),
                    'status' => $user->getStatusName(),
                    'created_by_admin' => $user->wasCreatedByAdmin()
                ];
            });

            return response()->json([
                'success' => true,
                'suggestions' => $suggestions
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la recherche'
            ], 500);
        }
    }

    /**
     * Activation en masse des utilisateurs inactifs (AJAX)
     */
    public function bulkActivate(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false, 
                'message' => 'Accès non autorisé'
            ], 403);
        }

        try {
            $inactiveUsers = User::where('status_id', 1)->get();
            
            if ($inactiveUsers->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun utilisateur inactif à activer'
                ]);
            }

            $activatedCount = 0;
            foreach ($inactiveUsers as $user) {
                $user->activate();
                $activatedCount++;
            }

            return response()->json([
                'success' => true,
                'message' => "{$activatedCount} utilisateur(s) ont été activés avec succès",
                'activated_count' => $activatedCount,
                'new_stats' => [
                    'active_users' => User::where('status_id', 2)->count(),
                    'inactive_users' => User::where('status_id', 1)->count(),
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'activation en masse : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * NOUVELLE MÉTHODE : Obtenir les utilisateurs créés par l'admin connecté
     */
    public function getMyCreatedUsers(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false, 
                'message' => 'Accès non autorisé'
            ], 403);
        }

        try {
            $admin = Auth::user();
            $users = $admin->createdUsers()
                          ->with(['userType', 'status'])
                          ->orderBy('created_at', 'desc')
                          ->get();

            return response()->json([
                'success' => true,
                'users' => $users->map(function($user) {
                    return [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'mobile_number' => $user->mobile_number,
                        'type' => $user->getTypeName(),
                        'status' => $user->getStatusName(),
                        'created_at' => $user->created_at->format('d/m/Y H:i'),
                        'is_active' => $user->isActive(),
                        'is_admin' => $user->isAdmin()
                    ];
                }),
                'total' => $users->count()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des utilisateurs créés'
            ], 500);
        }
    }
}