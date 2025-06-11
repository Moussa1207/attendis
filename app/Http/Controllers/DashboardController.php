<?php

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
    // DASHBOARDS PRINCIPAUX: app(admin) et app-users(utilisateur quelconque)
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
     * Dashboard admin avec statistiques ISOLÉES
     * AMÉLIORATION 2 : Chaque admin ne voit que SES statistiques d'utilisateurs créés
     */
    public function adminDashboard()
    {
        // Vérifier que l'utilisateur est bien admin
        if (!Auth::user()->isAdmin()) {
            return redirect()->route('layouts.app-users')
                ->with('error', 'Accès non autorisé à la zone administrateur.');
        }

        try {
            $currentAdminId = Auth::id();
            
            // AMÉLIORATION 2 : ISOLATION - Récupérer UNIQUEMENT les utilisateurs créés par cet admin
            $myUserIds = Auth::user()->createdUsers()->pluck('user_id')->toArray();
            $myUserIds[] = $currentAdminId; // Inclure l'admin lui-même
            
            // Statistiques ISOLÉES pour cet admin uniquement
            $stats = [
                'total_users' => count($myUserIds), // Ses utilisateurs + lui-même
                'active_users' => User::whereIn('id', $myUserIds)->where('status_id', 2)->count(),
                'inactive_users' => User::whereIn('id', $myUserIds)->where('status_id', 1)->count(),
                'suspended_users' => User::whereIn('id', $myUserIds)->where('status_id', 3)->count(),
                'admin_users' => 1, // Seulement lui-même (isolé des autres admins)
                'normal_users' => User::whereIn('id', $myUserIds)->where('user_type_id', 2)->count(),
                'recent_users' => User::whereIn('id', $myUserIds)->where('created_at', '>=', now()->subDays(7))->count(),
                'users_created_today' => User::whereIn('id', $myUserIds)->whereDate('created_at', today())->count(),
                'users_created_this_week' => User::whereIn('id', $myUserIds)->where('created_at', '>=', now()->startOfWeek())->count(),
                'users_created_this_month' => User::whereIn('id', $myUserIds)->where('created_at', '>=', now()->startOfMonth())->count(),
            ];

            // Statistiques personnelles pour l'admin connecté (SES créations)
            $personalStats = [
                'users_created_by_me' => Auth::user()->createdUsers()->count(),
                'active_users_created_by_me' => Auth::user()->createdUsers()
                    ->whereHas('user', function($query) {
                        $query->where('status_id', 2);
                    })->count(),
                'users_created_by_me_today' => Auth::user()->createdUsers()
                    ->whereHas('user', function($query) {
                        $query->whereDate('created_at', today());
                    })->count(),
                'users_created_by_me_this_week' => Auth::user()->createdUsers()
                    ->whereHas('user', function($query) {
                        $query->where('created_at', '>=', now()->startOfWeek());
                    })->count(),
            ];

            // Activité récente ISOLÉE (SES utilisateurs seulement)
            $recentActivity = User::with(['userType', 'status', 'createdBy.administrator'])
                ->whereIn('id', $myUserIds)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            // Utilisateurs en attente d'activation ISOLÉS (SES utilisateurs seulement)
            $pendingUsers = User::whereIn('id', $myUserIds)
                ->where('status_id', 1)
                ->with(['userType', 'createdBy.administrator'])
                ->orderBy('created_at', 'desc')
                ->limit(15)
                ->get();

            return view('layouts.app', compact(
                'stats', 
                'personalStats', 
                'recentActivity', 
                'pendingUsers'
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
            // Statistiques personnelles pour l'utilisateur (cohérent avec app-users.blade.php)
            $userStats = [
                'days_since_creation' => $user->created_at->diffInDays(now()),
                'account_age_formatted' => $user->created_at->diffForHumans(),
                'is_recently_created' => $user->created_at->diffInDays(now()) < 7,
                'creator_info' => $user->getCreationInfo(),
                'login_count_today' => 1,
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
    // GESTION DES UTILISATEURS (Pour users-list.blade.php)
    // ===============================================

    /**
     * Liste des utilisateurs créés par l'admin connecté UNIQUEMENT
     * AMÉLIORATION 2 : ISOLATION COMPLÈTE - Chaque admin ne voit QUE ses utilisateurs créés
     */
    public function usersList(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Accès non autorisé');
        }

        try {
            $currentAdminId = Auth::id();
            
            // AMÉLIORATION 2 : ISOLATION - Récupérer UNIQUEMENT les utilisateurs créés par cet admin
            $createdByMe = Auth::user()->createdUsers()->pluck('user_id')->toArray();
            
            // Ajouter l'admin lui-même à la liste (pour qu'il se voie)
            $createdByMe[] = $currentAdminId;
            
            // Query de base LIMITÉE aux utilisateurs de cet admin
            $query = User::with(['userType', 'status', 'createdBy.administrator'])
                         ->whereIn('id', $createdByMe);

            // Filtrage par recherche (dans SES utilisateurs seulement)
            if ($request->filled('search')) {
                $search = $request->search;
                $query->search($search);
            }

            // Filtrage par statut (dans SES utilisateurs seulement)
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

            // Filtrage par type (dans SES utilisateurs seulement) 
            // IMPORTANT : Exclure les autres admins du type "admin"
            if ($request->filled('type')) {
    $typeMapping = [
        'admin' => 1,       // Administrateur
        'ecran' => 2,       // Poste Ecran  
        'accueil' => 3,     // Poste Accueil
        'conseiller' => 4,  // Poste Conseiller
        'user' => [2, 3, 4] // Tous les utilisateurs normaux
    ];
    
    $requestedType = $request->type;
    
    if ($requestedType === 'admin') {
        // Montrer seulement lui-même s'il est admin
        $query->where('id', $currentAdminId)->where('user_type_id', 1);
    } elseif ($requestedType === 'user') {
        // Montrer tous ses utilisateurs normaux créés (écran, accueil, conseiller)
        $query->whereIn('user_type_id', [2, 3, 4]);
    } elseif (isset($typeMapping[$requestedType])) {
        // Filtrer par type spécifique
        $typeId = $typeMapping[$requestedType];
        $query->where('user_type_id', $typeId);
    }
   }


            $users = $query->orderBy('created_at', 'desc')->paginate(15);

            // Informations de traçabilité
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
    // ACTIONS SUR LES UTILISATEURS (Pour users-list.blade.php)
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
            
            \Log::info('User activated', [
                'activated_user_id' => $user->id,
                'activated_user_username' => $user->username,
                'admin_id' => Auth::id(),
                'admin_username' => Auth::user()->username
            ]);
            
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
            
            \Log::info('User suspended', [
                'suspended_user_id' => $user->id,
                'suspended_user_username' => $user->username,
                'admin_id' => Auth::id(),
                'admin_username' => Auth::user()->username
            ]);
            
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

            $user->activate();
            
            $message = 'L\'utilisateur ' . $user->username . ' a été réactivé avec succès.';
            
            \Log::info('User reactivated', [
                'reactivated_user_id' => $user->id,
                'reactivated_user_username' => $user->username,
                'admin_id' => Auth::id(),
                'admin_username' => Auth::user()->username
            ]);
            
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
            
            \Log::warning('User deleted', [
                'deleted_user_id' => $userId,
                'deleted_user_username' => $username,
                'deleted_user_email' => $user->email,
                'admin_id' => Auth::id(),
                'admin_username' => Auth::user()->username
            ]);
            
            $user->delete();
            
            $message = 'L\'utilisateur ' . $username . ' a été supprimé définitivement.';
            
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
            
            $usersToDelete = User::whereIn('id', $userIds)->get();
            
            if ($usersToDelete->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun utilisateur trouvé pour la suppression'
                ]);
            }
            
            $errors = [];
            
            if (in_array($currentAdminId, $userIds)) {
                $errors[] = 'Vous ne pouvez pas supprimer votre propre compte';
            }
            
            $adminUsers = $usersToDelete->where('user_type_id', 1);
            $activeAdmins = User::admins()->active()->count();
            
            if ($adminUsers->count() >= $activeAdmins) {
                $errors[] = 'Impossible de supprimer tous les administrateurs actifs';
            }
            
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
            // AMÉLIORATION 2 : Activation en masse ISOLÉE - seulement SES utilisateurs inactifs
            $currentAdminId = Auth::id();
            $myUserIds = Auth::user()->createdUsers()->pluck('user_id')->toArray();
            $myUserIds[] = $currentAdminId;
            
            $inactiveUsers = User::whereIn('id', $myUserIds)->inactive()->get();
            
            if ($inactiveUsers->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun de vos utilisateurs inactifs à activer'
                ]);
            }

            $activatedCount = 0;
            
            DB::beginTransaction();
            
            foreach ($inactiveUsers as $user) {
                try {
                    $user->activate();
                    $activatedCount++;
                    
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
                'message' => "{$activatedCount} de vos utilisateur(s) ont été activés avec succès",
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
     * Obtenir les statistiques en temps réel ISOLÉES (AJAX - ADMINS)
     * AMÉLIORATION 2 : Statistiques isolées pour SES utilisateurs uniquement
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
            $currentAdminId = Auth::id();
            
            // AMÉLIORATION 2 : ISOLATION - Statistiques pour SES utilisateurs uniquement
            $myUserIds = Auth::user()->createdUsers()->pluck('user_id')->toArray();
            $myUserIds[] = $currentAdminId; // Inclure l'admin lui-même
            
            $stats = [
                'total_users' => count($myUserIds),
                'active_users' => User::whereIn('id', $myUserIds)->where('status_id', 2)->count(),
                'inactive_users' => User::whereIn('id', $myUserIds)->where('status_id', 1)->count(),
                'suspended_users' => User::whereIn('id', $myUserIds)->where('status_id', 3)->count(),
                'admin_users' => 1, // Seulement lui-même
                'normal_users' => User::whereIn('id', $myUserIds)->where('user_type_id', 2)->count(),
                'users_created_by_me' => Auth::user()->createdUsers()->count(),
                'recent_users' => User::whereIn('id', $myUserIds)->where('created_at', '>=', now()->subDays(7))->count(),
                'users_created_today' => User::whereIn('id', $myUserIds)->whereDate('created_at', today())->count(),
                'users_created_this_week' => User::whereIn('id', $myUserIds)->where('created_at', '>=', now()->startOfWeek())->count(),
                'users_created_this_month' => User::whereIn('id', $myUserIds)->where('created_at', '>=', now()->startOfMonth())->count(),
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
     * AMÉLIORATION 2 : Recherche ISOLÉE dans SES utilisateurs uniquement
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
            // AMÉLIORATION 2 : ISOLATION - Recherche dans SES utilisateurs uniquement
            $currentAdminId = Auth::id();
            $myUserIds = Auth::user()->createdUsers()->pluck('user_id')->toArray();
            $myUserIds[] = $currentAdminId;
            
            $users = User::whereIn('id', $myUserIds)
                        ->search($search)
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
     * Obtenir les détails sur utilisateur (AJAX - ADMINS)
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
     * AMÉLIORATION 2 : Export ISOLÉ - seulement SES utilisateurs
     */
    public function exportUsers(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Accès non autorisé');
        }

        try {
            // AMÉLIORATION 2 : ISOLATION - Export de SES utilisateurs uniquement
            $currentAdminId = Auth::id();
            $myUserIds = Auth::user()->createdUsers()->pluck('user_id')->toArray();
            $myUserIds[] = $currentAdminId;
            
            $users = User::whereIn('id', $myUserIds)
                        ->with(['userType', 'status', 'createdBy.administrator'])
                        ->get();
            
            $filename = 'mes_utilisateurs_' . date('Y-m-d_H-i-s') . '.csv';
            
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


public function getStatsByType(Request $request)
{
    if (!Auth::user()->isAdmin()) {
        return response()->json([
            'success' => false, 
            'message' => 'Accès non autorisé'
        ], 403);
    }

    try {
        $currentAdminId = Auth::id();
        
        // ISOLATION - Statistiques pour SES utilisateurs uniquement
        $myUserIds = Auth::user()->createdUsers()->pluck('user_id')->toArray();
        $myUserIds[] = $currentAdminId; // Inclure l'admin lui-même
        
        // Statistiques détaillées par type
        $statsByType = [
            'admin' => [
                'total' => User::whereIn('id', $myUserIds)->where('user_type_id', 1)->count(),
                'active' => User::whereIn('id', $myUserIds)->where('user_type_id', 1)->where('status_id', 2)->count(),
                'inactive' => User::whereIn('id', $myUserIds)->where('user_type_id', 1)->where('status_id', 1)->count(),
                'suspended' => User::whereIn('id', $myUserIds)->where('user_type_id', 1)->where('status_id', 3)->count(),
            ],
            'ecran' => [
                'total' => User::whereIn('id', $myUserIds)->where('user_type_id', 2)->count(),
                'active' => User::whereIn('id', $myUserIds)->where('user_type_id', 2)->where('status_id', 2)->count(),
                'inactive' => User::whereIn('id', $myUserIds)->where('user_type_id', 2)->where('status_id', 1)->count(),
                'suspended' => User::whereIn('id', $myUserIds)->where('user_type_id', 2)->where('status_id', 3)->count(),
            ],
            'accueil' => [
                'total' => User::whereIn('id', $myUserIds)->where('user_type_id', 3)->count(),
                'active' => User::whereIn('id', $myUserIds)->where('user_type_id', 3)->where('status_id', 2)->count(),
                'inactive' => User::whereIn('id', $myUserIds)->where('user_type_id', 3)->where('status_id', 1)->count(),
                'suspended' => User::whereIn('id', $myUserIds)->where('user_type_id', 3)->where('status_id', 3)->count(),
            ],
            'conseiller' => [
                'total' => User::whereIn('id', $myUserIds)->where('user_type_id', 4)->count(),
                'active' => User::whereIn('id', $myUserIds)->where('user_type_id', 4)->where('status_id', 2)->count(),
                'inactive' => User::whereIn('id', $myUserIds)->where('user_type_id', 4)->where('status_id', 1)->count(),
                'suspended' => User::whereIn('id', $myUserIds)->where('user_type_id', 4)->where('status_id', 3)->count(),
            ]
        ];

        return response()->json([
            'success' => true, 
            'stats_by_type' => $statsByType,
            'timestamp' => now()->format('d/m/Y H:i:s')
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false, 
            'message' => 'Erreur lors de la récupération des statistiques par type'
        ], 500);
    }
}

    /**
     * Statistiques avancées pour les admins (AJAX)
     * AMÉLIORATION 2 : Statistiques avancées ISOLÉES
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
            // AMÉLIORATION 2 : ISOLATION - Statistiques avancées pour SES utilisateurs uniquement
            $currentAdminId = Auth::id();
            $myUserIds = Auth::user()->createdUsers()->pluck('user_id')->toArray();
            $myUserIds[] = $currentAdminId;
            
            // Statistiques temporelles ISOLÉES
            $stats = [
                'users_created_today' => User::whereIn('id', $myUserIds)->whereDate('created_at', today())->count(),
                'users_created_yesterday' => User::whereIn('id', $myUserIds)->whereDate('created_at', yesterday())->count(),
                'users_created_this_week' => User::whereIn('id', $myUserIds)->where('created_at', '>=', now()->startOfWeek())->count(),
                'users_created_last_week' => User::whereIn('id', $myUserIds)->whereBetween('created_at', [
                    now()->subWeek()->startOfWeek(),
                    now()->subWeek()->endOfWeek()
                ])->count(),
                'users_created_this_month' => User::whereIn('id', $myUserIds)->where('created_at', '>=', now()->startOfMonth())->count(),
                'users_created_last_month' => User::whereIn('id', $myUserIds)->whereBetween('created_at', [
                    now()->subMonth()->startOfMonth(),
                    now()->subMonth()->endOfMonth()
                ])->count(),
            ];
            
            // Ratios et tendances ISOLÉS
            $totalUsers = count($myUserIds);
            $stats['activation_rate'] = $totalUsers > 0 ? 
                round((User::whereIn('id', $myUserIds)->active()->count() / $totalUsers) * 100, 2) : 0;
            $stats['admin_ratio'] = $totalUsers > 0 ? 
                round((1 / $totalUsers) * 100, 2) : 0; // Seulement lui-même
            
            // Croissance ISOLÉE
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