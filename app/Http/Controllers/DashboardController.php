<?php
// app/Http/Controllers/DashboardController.php (VERSION COMPLÈTE RÉVISÉE)

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserType;
use App\Models\Status;
use App\Models\AdministratorUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.user.status');
    }

    // ===============================================
    // DASHBOARDS PRINCIPAUX
    // ===============================================

    /**
     * Dashboard principal - Redirection intelligente selon le type d'utilisateur
     */
    public function index()
    {
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

        // Redirection selon le type d'utilisateur
        if ($user->isAdmin()) {
            return redirect()->route('layouts.app');
        } else {
            return redirect()->route('layouts.app-users');
        }
    }

    /**
     * Dashboard admin (layouts/app.blade.php) avec statistiques complètes
     */
    public function adminDashboard()
    {
        // Vérifier que l'utilisateur est bien admin
        if (!Auth::user()->isAdmin()) {
            return redirect()->route('layouts.app-users')
                ->with('error', 'Accès non autorisé à la zone administrateur.');
        }

        try {
            // Statistiques générales du système
            $stats = [
                'total_users' => User::count(),
                'active_users' => User::active()->count(),
                'inactive_users' => User::inactive()->count(),
                'suspended_users' => User::suspended()->count(),
                'admin_users' => User::admins()->count(),
                'normal_users' => User::users()->count(),
                'recent_users' => User::where('created_at', '>=', now()->subDays(7))->count(),
                'users_created_today' => User::whereDate('created_at', today())->count(),
                'users_created_this_week' => User::where('created_at', '>=', now()->startOfWeek())->count(),
                'users_created_this_month' => User::where('created_at', '>=', now()->startOfMonth())->count(),
            ];

            // Statistiques personnelles pour l'admin connecté
            $currentAdmin = Auth::user();
            $personalStats = [
                'users_created_by_me' => $currentAdmin->createdUsers()->count(),
                'active_users_created_by_me' => $currentAdmin->createdUsers()
                    ->whereHas('user', function($query) {
                        $query->where('status_id', 2);
                    })->count(),
                'users_created_by_me_today' => $currentAdmin->createdUsers()
                    ->whereHas('user', function($query) {
                        $query->whereDate('created_at', today());
                    })->count(),
                'users_created_by_me_this_week' => $currentAdmin->createdUsers()
                    ->whereHas('user', function($query) {
                        $query->where('created_at', '>=', now()->startOfWeek());
                    })->count(),
            ];

            // Activité récente (derniers utilisateurs créés)
            $recentActivity = User::with(['userType', 'status', 'createdBy.administrator'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            // Utilisateurs en attente d'activation
            $pendingUsers = User::inactive()
                ->with(['userType', 'createdBy.administrator'])
                ->orderBy('created_at', 'desc')
                ->limit(15)
                ->get();

            // Statistiques par type d'utilisateur
            $userTypeStats = UserType::with('users')->get()->map(function($type) {
                return [
                    'type' => $type,
                    'stats' => $type->getStats()
                ];
            });

            // Statistiques par statut
            $statusStats = Status::with('users')->get()->map(function($status) {
                return [
                    'status' => $status,
                    'count' => $status->getUsersCount()
                ];
            });

            return view('layouts.app', compact(
                'stats', 
                'personalStats', 
                'recentActivity', 
                'pendingUsers',
                'userTypeStats',
                'statusStats'
            ));

        } catch (\Exception $e) {
            \Log::error('Admin dashboard error', [
                'admin_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return redirect()->route('login')
                ->with('error', 'Erreur lors du chargement du dashboard administrateur.');
        }
    }

    /**
     * Dashboard utilisateur normal (layouts/app-users.blade.php)
     */
    public function userDashboard()
    {
        $user = Auth::user();

        // Si c'est un admin, on redirige vers le dashboard admin
        if ($user->isAdmin()) {
            return redirect()->route('layouts.app')
                ->with('info', 'Redirection vers le dashboard administrateur.');
        }

        try {
            // Statistiques personnelles pour l'utilisateur
            $userStats = [
                'days_since_creation' => $user->created_at->diffInDays(now()),
                'account_age_formatted' => $user->created_at->diffForHumans(),
                'is_recently_created' => $user->created_at->diffInDays(now()) < 7,
                'creator_info' => $user->getCreationInfo(),
                'login_count_today' => 1, // Peut être implémenté avec un système de log
                'last_password_change' => $user->updated_at->diffForHumans(),
            ];

            return view('layouts.app-users', compact('userStats'));

        } catch (\Exception $e) {
            \Log::error('User dashboard error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return redirect()->route('login')
                ->with('error', 'Erreur lors du chargement de votre espace personnel.');
        }
    }

    // ===============================================
    // GESTION DES UTILISATEURS
    // ===============================================

    /**
     * Liste complète des utilisateurs avec filtres (ADMINS UNIQUEMENT)
     */
    public function usersList(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Accès non autorisé');
        }

        try {
            $query = User::with(['userType', 'status', 'createdBy.administrator']);

            // Filtrage par recherche
            if ($request->filled('search')) {
                $search = $request->search;
                $query->search($search);
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

            // Filtrage par créateur
            if ($request->filled('creator')) {
                if ($request->creator === 'me') {
                    $createdByMe = Auth::user()->createdUsers()->pluck('user_id');
                    $query->whereIn('id', $createdByMe);
                }
            }

            $users = $query->orderBy('created_at', 'desc')->paginate(15);

            // Ajouter les informations de traçabilité
            $users->getCollection()->transform(function ($user) {
                $user->creation_info = $user->getCreationInfo();
                $user->can_be_deleted = $user->canBeDeleted();
                $user->can_be_suspended = $user->canBeSuspended();
                return $user;
            });

            return view('user.users-list', compact('users'));

        } catch (\Exception $e) {
            \Log::error('Users list error', [
                'admin_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return redirect()->route('layouts.app')
                ->with('error', 'Erreur lors du chargement de la liste des utilisateurs.');
        }
    }

    // ===============================================
    // ACTIONS SUR LES UTILISATEURS
    // ===============================================

    /**
     * Activer un utilisateur (ADMINS UNIQUEMENT)
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
            
            // Log de l'activité
            \Log::info('User activated', [
                'activated_user_id' => $user->id,
                'activated_user_username' => $user->username,
                'admin_id' => Auth::id(),
                'admin_username' => Auth::user()->username
            ]);
            
            // Réponse AJAX
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true, 
                    'message' => $message,
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'status' => 'active',
                        'status_badge_color' => $user->getStatusBadgeColor()
                    ]
                ]);
            }
            
            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            $errorMessage = 'Erreur lors de l\'activation : ' . $e->getMessage();
            
            \Log::error('User activation error', [
                'user_id' => $user->id,
                'admin_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
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
     * Suspendre un utilisateur (ADMINS UNIQUEMENT)
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
            // Vérifications de sécurité
            if (!$user->canBeSuspended()) {
                $message = 'Impossible de suspendre cet utilisateur.';
                
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false, 
                        'message' => $message
                    ], 400);
                }
                
                return redirect()->back()->with('error', $message);
            }

            // Empêcher de se suspendre soi-même
            if ($user->id === Auth::id()) {
                $message = 'Vous ne pouvez pas suspendre votre propre compte.';
                
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false, 
                        'message' => $message
                    ], 400);
                }
                
                return redirect()->back()->with('error', $message);
            }

            $user->suspend();
            
            $message = 'L\'utilisateur ' . $user->username . ' a été suspendu.';
            
            // Log de l'activité
            \Log::info('User suspended', [
                'suspended_user_id' => $user->id,
                'suspended_user_username' => $user->username,
                'admin_id' => Auth::id(),
                'admin_username' => Auth::user()->username
            ]);
            
            // Réponse AJAX
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true, 
                    'message' => $message,
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'status' => 'suspended',
                        'status_badge_color' => $user->getStatusBadgeColor()
                    ]
                ]);
            }
            
            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            $errorMessage = 'Erreur lors de la suspension : ' . $e->getMessage();
            
            \Log::error('User suspension error', [
                'user_id' => $user->id,
                'admin_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
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
     * Réactiver un utilisateur suspendu (ADMINS UNIQUEMENT)
     */
    public function reactivateUser(User $user, Request $request)
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
            // Vérifier que l'utilisateur est bien suspendu
            if (!$user->isSuspended()) {
                $message = 'L\'utilisateur ' . $user->username . ' n\'est pas suspendu.';
                
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false, 
                        'message' => $message
                    ], 400);
                }
                
                return redirect()->back()->with('error', $message);
            }

            $user->activate(); // Réactiver = statut actif
            
            $message = 'L\'utilisateur ' . $user->username . ' a été réactivé avec succès.';
            
            // Log de l'activité
            \Log::info('User reactivated', [
                'reactivated_user_id' => $user->id,
                'reactivated_user_username' => $user->username,
                'admin_id' => Auth::id(),
                'admin_username' => Auth::user()->username
            ]);
            
            // Réponse AJAX
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true, 
                    'message' => $message,
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'status' => 'active',
                        'status_badge_color' => $user->getStatusBadgeColor()
                    ]
                ]);
            }
            
            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            $errorMessage = 'Erreur lors de la réactivation : ' . $e->getMessage();
            
            \Log::error('User reactivation error', [
                'user_id' => $user->id,
                'admin_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
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
     * Supprimer un utilisateur (ADMINS UNIQUEMENT)
     */
    public function deleteUser(User $user, Request $request)
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
            // Vérifications de sécurité
            if (!$user->canBeDeleted()) {
                $message = 'Impossible de supprimer cet utilisateur.';
                
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false, 
                        'message' => $message
                    ], 400);
                }
                
                return redirect()->back()->with('error', $message);
            }

            // Empêcher de se supprimer soi-même
            if ($user->id === Auth::id()) {
                $message = 'Vous ne pouvez pas supprimer votre propre compte.';
                
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false, 
                        'message' => $message
                    ], 400);
                }
                
                return redirect()->back()->with('error', $message);
            }

            $username = $user->username;
            $userId = $user->id;
            
            // Log avant suppression
            \Log::warning('User deleted', [
                'deleted_user_id' => $userId,
                'deleted_user_username' => $username,
                'deleted_user_email' => $user->email,
                'admin_id' => Auth::id(),
                'admin_username' => Auth::user()->username
            ]);
            
            // Supprimer l'utilisateur (cascade supprimera les relations)
            $user->delete();
            
            $message = 'L\'utilisateur ' . $username . ' a été supprimé définitivement.';
            
            // Réponse AJAX
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true, 
                    'message' => $message,
                    'deleted_user_id' => $userId
                ]);
            }
            
            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            $errorMessage = 'Erreur lors de la suppression : ' . $e->getMessage();
            
            \Log::error('User deletion error', [
                'user_id' => $user->id,
                'admin_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
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
     * Suppression en masse d'utilisateurs (ADMINS UNIQUEMENT)
     */
    public function bulkDeleteUsers(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false, 
                'message' => 'Accès non autorisé'
            ], 403);
        }

        $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id'
        ]);

        try {
            $userIds = $request->user_ids;
            $currentAdminId = Auth::id();
            
            // Récupérer les utilisateurs à supprimer
            $usersToDelete = User::whereIn('id', $userIds)->get();
            
            if ($usersToDelete->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun utilisateur trouvé pour la suppression'
                ]);
            }
            
            // Vérifications de sécurité
            $errors = [];
            
            // Empêcher de se supprimer soi-même
            if (in_array($currentAdminId, $userIds)) {
                $errors[] = 'Vous ne pouvez pas supprimer votre propre compte';
            }
            
            // Vérifier les administrateurs
            $adminUsers = $usersToDelete->where('user_type_id', 1);
            $activeAdmins = User::admins()->active()->count();
            
            if ($adminUsers->count() >= $activeAdmins) {
                $errors[] = 'Impossible de supprimer tous les administrateurs actifs';
            }
            
            // Vérifier les utilisateurs qui ne peuvent pas être supprimés
            foreach ($usersToDelete as $user) {
                if (!$user->canBeDeleted() && $user->id !== $currentAdminId) {
                    $errors[] = "L'utilisateur {$user->username} ne peut pas être supprimé";
                }
            }
            
            if (!empty($errors)) {
                return response()->json([
                    'success' => false,
                    'message' => implode('. ', $errors)
                ], 400);
            }
            
            $deletedCount = 0;
            $deletedUsers = [];
            
            DB::beginTransaction();
            
            foreach ($usersToDelete as $user) {
                if ($user->canBeDeleted() && $user->id !== $currentAdminId) {
                    try {
                        // Log avant suppression
                        \Log::warning('Bulk user deletion', [
                            'deleted_user_id' => $user->id,
                            'deleted_user_username' => $user->username,
                            'deleted_user_email' => $user->email,
                            'admin_id' => $currentAdminId,
                            'admin_username' => Auth::user()->username
                        ]);
                        
                        $deletedUsers[] = [
                            'id' => $user->id,
                            'username' => $user->username,
                            'email' => $user->email
                        ];
                        
                        $user->delete();
                        $deletedCount++;
                        
                    } catch (\Exception $e) {
                        \Log::error('Error deleting user in bulk operation', [
                            'user_id' => $user->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$deletedCount} utilisateur(s) ont été supprimés avec succès",
                'deleted_count' => $deletedCount,
                'deleted_users' => $deletedUsers
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Bulk deletion error', [
                'admin_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression en masse : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activation en masse des utilisateurs inactifs (AJAX - ADMINS)
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
            $inactiveUsers = User::inactive()->get();
            
            if ($inactiveUsers->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun utilisateur inactif à activer'
                ]);
            }

            $activatedCount = 0;
            
            DB::beginTransaction();
            
            foreach ($inactiveUsers as $user) {
                try {
                    $user->activate();
                    $activatedCount++;
                    
                    // Log pour chaque activation
                    \Log::info('Bulk user activation', [
                        'activated_user_id' => $user->id,
                        'activated_user_username' => $user->username,
                        'admin_id' => Auth::id(),
                        'admin_username' => Auth::user()->username
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Error in bulk activation', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$activatedCount} utilisateur(s) ont été activés avec succès",
                'activated_count' => $activatedCount
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'activation en masse : ' . $e->getMessage()
            ], 500);
        }
    }

    // ===============================================
    // API AJAX POUR STATISTIQUES ET RECHERCHE
    // ===============================================

    /**
     * Obtenir les statistiques en temps réel (AJAX - ADMINS)
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
            $currentAdmin = Auth::user();
            
            $stats = [
                'total_users' => User::count(),
                'active_users' => User::active()->count(),
                'inactive_users' => User::inactive()->count(),
                'suspended_users' => User::suspended()->count(),
                'admin_users' => User::admins()->count(),
                'normal_users' => User::users()->count(),
                'users_created_by_me' => $currentAdmin->createdUsers()->count(),
                'recent_users' => User::where('created_at', '>=', now()->subDays(7))->count(),
                'users_created_today' => User::whereDate('created_at', today())->count(),
                'users_created_this_week' => User::where('created_at', '>=', now()->startOfWeek())->count(),
                'users_created_this_month' => User::where('created_at', '>=', now()->startOfMonth())->count(),
            ];

            return response()->json([
                'success' => true, 
                'stats' => $stats,
                'timestamp' => now()->format('d/m/Y H:i:s')
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Erreur lors de la récupération des statistiques'
            ], 500);
        }
    }

    /**
     * Recherche d'utilisateurs en temps réel (AJAX - ADMINS)
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
            $users = User::search($search)
                        ->with(['userType', 'status'])
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
                    'creation_info' => $user->getCreationInfo()
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
     * Obtenir les détails d'un utilisateur (AJAX - ADMINS)
     */
    public function getUserDetails(User $user, Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false, 
                'message' => 'Accès non autorisé'
            ], 403);
        }

        try {
            $user->load(['userType', 'status', 'createdBy.administrator']);
            
            $userDetails = [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'mobile_number' => $user->mobile_number,
                'type' => $user->getTypeName(),
                'type_icon' => $user->getTypeIcon(),
                'status' => $user->getStatusName(),
                'status_badge_color' => $user->getStatusBadgeColor(),
                'created_at' => $user->created_at->format('d/m/Y à H:i'),
                'creation_info' => $user->getCreationInfo(),
                'last_activity' => $user->updated_at->format('d/m/Y à H:i'),
                'is_admin' => $user->isAdmin(),
                'is_active' => $user->isActive(),
                'is_suspended' => $user->isSuspended(),
                'can_be_suspended' => $user->canBeSuspended(),
                'can_be_deleted' => $user->canBeDeleted(),
                'account_age_days' => $user->created_at->diffInDays(now()),
            ];

            return response()->json([
                'success' => true,
                'user' => $userDetails
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des détails'
            ], 500);
        }
    }

    // ===============================================
    // EXPORT ET UTILITAIRES
    // ===============================================

    /**
     * Export des utilisateurs (CSV/Excel) - ADMINS UNIQUEMENT
     */
    public function exportUsers(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Accès non autorisé');
        }

        try {
            $users = User::with(['userType', 'status', 'createdBy.administrator'])->get();
            
            $filename = 'utilisateurs_' . date('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($users) {
                $file = fopen('php://output', 'w');
                
                // BOM pour UTF-8
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                
                // En-têtes CSV
                fputcsv($file, [
                    'ID',
                    'Nom d\'utilisateur',
                    'Email',
                    'Téléphone',
                    'Type',
                    'Statut',
                    'Créé par',
                    'Date de création',
                    'Dernière modification'
                ], ';');
                
                // Données
                foreach ($users as $user) {
                    $createdBy = $user->getCreator();
                    
                    fputcsv($file, [
                        $user->id,
                        $user->username,
                        $user->email,
                        $user->mobile_number,
                        $user->getTypeName(),
                        $user->getStatusName(),
                        $createdBy ? $createdBy->username : 'Inscription directe',
                        $user->created_at->format('d/m/Y H:i:s'),
                        $user->updated_at->format('d/m/Y H:i:s')
                    ], ';');
                }
                
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
            
        } catch (\Exception $e) {
            \Log::error('Export error', [
                'admin_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Erreur lors de l\'export : ' . $e->getMessage());
        }
    }

    /**
     * Statistiques avancées pour les admins (AJAX)
     */
    public function getAdvancedStats(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false, 
                'message' => 'Accès non autorisé'
            ], 403);
        }

        try {
            $currentAdmin = Auth::user();
            
            // Statistiques temporelles
            $stats = [
                'users_created_today' => User::whereDate('created_at', today())->count(),
                'users_created_yesterday' => User::whereDate('created_at', yesterday())->count(),
                'users_created_this_week' => User::where('created_at', '>=', now()->startOfWeek())->count(),
                'users_created_last_week' => User::whereBetween('created_at', [
                    now()->subWeek()->startOfWeek(),
                    now()->subWeek()->endOfWeek()
                ])->count(),
                'users_created_this_month' => User::where('created_at', '>=', now()->startOfMonth())->count(),
                'users_created_last_month' => User::whereBetween('created_at', [
                    now()->subMonth()->startOfMonth(),
                    now()->subMonth()->endOfMonth()
                ])->count(),
            ];
            
            // Ratios et tendances
            $totalUsers = User::count();
            $stats['activation_rate'] = $totalUsers > 0 ? 
                round((User::active()->count() / $totalUsers) * 100, 2) : 0;
            $stats['admin_ratio'] = $totalUsers > 0 ? 
                round((User::admins()->count() / $totalUsers) * 100, 2) : 0;
            
            // Croissance
            $stats['weekly_growth'] = $stats['users_created_last_week'] > 0 ? 
                round((($stats['users_created_this_week'] - $stats['users_created_last_week']) / $stats['users_created_last_week']) * 100, 2) : 0;
            $stats['monthly_growth'] = $stats['users_created_last_month'] > 0 ? 
                round((($stats['users_created_this_month'] - $stats['users_created_last_month']) / $stats['users_created_last_month']) * 100, 2) : 0;

            return response()->json([
                'success' => true, 
                'stats' => $stats,
                'timestamp' => now()->format('d/m/Y H:i:s')
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques avancées'
            ], 500);
        }
    }
}