<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserType;
use App\Models\Status;
use App\Models\AdministratorUser;
use App\Models\Agency;
use App\Models\Service;
use Illuminate\Support\Facades\Hash; 
use Illuminate\Support\Facades\Log;  
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
     * ✅ CORRIGÉ : Dashboard admin avec statistiques ISOLÉES
     * Chaque admin ne voit que SES statistiques d'utilisateurs créés
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
            
            // 🔒 ISOLATION CORRECTE - Récupérer UNIQUEMENT les utilisateurs créés par cet admin
            $myUserIds = AdministratorUser::where('administrator_id', $currentAdminId)
                                         ->pluck('user_id')
                                         ->toArray();
            $myUserIds[] = $currentAdminId; // Inclure l'admin lui-même
            
            // Statistiques ISOLÉES pour cet admin uniquement
            $stats = [
                'total_users' => count($myUserIds), // Ses utilisateurs + lui-même
                'active_users' => User::whereIn('id', $myUserIds)->where('status_id', 2)->count(),
                'inactive_users' => User::whereIn('id', $myUserIds)->where('status_id', 1)->count(),
                'suspended_users' => User::whereIn('id', $myUserIds)->where('status_id', 3)->count(),
                'admin_users' => User::whereIn('id', $myUserIds)->where('user_type_id', 1)->count(),
                'ecran_users' => User::whereIn('id', $myUserIds)->where('user_type_id', 2)->count(),
                'accueil_users' => User::whereIn('id', $myUserIds)->where('user_type_id', 3)->count(),
                'conseiller_users' => User::whereIn('id', $myUserIds)->where('user_type_id', 4)->count(),
                'recent_users' => User::whereIn('id', $myUserIds)->where('created_at', '>=', now()->subDays(7))->count(),
                'users_created_today' => User::whereIn('id', $myUserIds)->whereDate('created_at', today())->count(),
                'users_created_this_week' => User::whereIn('id', $myUserIds)->where('created_at', '>=', now()->startOfWeek())->count(),
                'users_created_this_month' => User::whereIn('id', $myUserIds)->where('created_at', '>=', now()->startOfMonth())->count(),
                
                // 🔒 Mes agences et services
                'my_agencies' => Agency::where('created_by', $currentAdminId)->count(),
                'my_active_agencies' => Agency::where('created_by', $currentAdminId)->where('status', 'active')->count(),
                'my_services' => Service::where('created_by', $currentAdminId)->count(),
                'my_active_services' => Service::where('created_by', $currentAdminId)->where('statut', 'actif')->count(),
            ];

            // Statistiques personnelles pour l'admin connecté (SES créations)
            $personalStats = [
                'users_created_by_me' => AdministratorUser::where('administrator_id', $currentAdminId)->count(),
                'active_users_created_by_me' => User::whereIn('id', $myUserIds)->where('status_id', 2)->count() - 1, // -1 pour l'admin
                'users_created_by_me_today' => User::whereIn('id', $myUserIds)->whereDate('created_at', today())->count(),
                'users_created_by_me_this_week' => User::whereIn('id', $myUserIds)->where('created_at', '>=', now()->startOfWeek())->count(),
                'agencies_created_by_me' => Agency::where('created_by', $currentAdminId)->count(),
                'services_created_by_me' => Service::where('created_by', $currentAdminId)->count(),
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
     * 🆕 NOUVELLE LOGIQUE : Dashboard utilisateur avec différenciation selon le type
     * - POSTE ECRAN → Interface sans sidebar + grille services
     * - ACCUEIL/CONSEILLER → Interface actuelle adaptée
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
            // 🎯 DIFFÉRENCIATION SELON LE TYPE D'UTILISATEUR
            if ($user->isEcranUser()) {
                return $this->ecranDashboard($user);
            } else {
                return $this->normalUserDashboard($user);
            }

        } catch (\Exception $e) {
            \Log::error('User dashboard error', [
                'user_id' => Auth::id(),
                'user_type' => $user->getUserRole(),
                'error' => $e->getMessage()
            ]);

            return redirect()->route('login')
                ->with('error', 'Erreur lors du chargement de votre espace.');
        }
    }

    /**
     * 🆕 Dashboard pour utilisateurs POSTE ECRAN
     * Interface sans sidebar + grille de services créés par l'admin
     */
    private function ecranDashboard(User $user)
    {
        try {
            // Récupérer l'admin créateur de cet utilisateur
            $creator = $user->getCreator();
            
            if (!$creator) {
                \Log::warning("Utilisateur écran sans créateur détecté", [
                    'user_id' => $user->id,
                    'username' => $user->username
                ]);
                
                return view('layouts.app-ecran', [
                    'services' => collect(),
                    'userInfo' => $this->getUserInfo($user),
                    'noCreator' => true
                ]);
            }

            // Récupérer TOUS les services créés par l'admin (actifs et inactifs)
            $services = $creator->createdServices()
                              ->orderBy('statut', 'desc') // Actifs en premier
                              ->orderBy('created_at', 'desc')
                              ->get();

            // Statistiques des services pour l'interface écran
            $serviceStats = [
                'total_services' => $services->count(),
                'active_services' => $services->where('statut', 'actif')->count(),
                'inactive_services' => $services->where('statut', 'inactif')->count(),
                'recent_services' => $services->where('created_at', '>=', now()->subDays(7))->count(),
            ];

            \Log::info("Interface écran chargée", [
                'user_id' => $user->id,
                'creator_id' => $creator->id,
                'services_count' => $services->count()
            ]);

            return view('layouts.app-ecran', [
                'services' => $services,
                'serviceStats' => $serviceStats,
                'userInfo' => $this->getUserInfo($user),
                'creatorInfo' => [
                    'username' => $creator->username,
                    'company' => $creator->company,
                    'email' => $creator->email
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur dashboard écran', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return view('layouts.app-ecran', [
                'services' => collect(),
                'userInfo' => $this->getUserInfo($user),
                'error' => 'Erreur lors du chargement des services'
            ]);
        }
    }

    /**
     * 🆕 Dashboard pour utilisateurs ACCUEIL et CONSEILLER
     * Interface actuelle app-users.blade.php adaptée selon le type
     */
    private function normalUserDashboard(User $user)
    {
        // Statistiques personnelles pour l'utilisateur
        $userStats = [
            'days_since_creation' => $user->created_at->diffInDays(now()),
            'account_age_formatted' => $user->created_at->diffForHumans(),
            'is_recently_created' => $user->created_at->diffInDays(now()) < 7,
            'creator_info' => $user->getCreationInfo(),
            'login_count_today' => 1,
            'last_password_change' => $user->updated_at->diffForHumans(),
        ];

        // Statistiques adaptées selon le type
        $typeSpecificData = $this->getTypeSpecificData($user);

        return view('layouts.app-users', [
            'userStats' => $userStats,
            'typeSpecificData' => $typeSpecificData,
            'userInfo' => $this->getUserInfo($user)
        ]);
    }

    /**
     * 🆕 Données spécifiques selon le type d'utilisateur
     */
    private function getTypeSpecificData(User $user): array
    {
        $data = [
            'type_description' => '',
            'type_features' => [],
            'type_recommendations' => []
        ];

        if ($user->isAccueilUser()) {
            $data['type_description'] = 'Poste Accueil - Réception et orientation des visiteurs';
            $data['type_features'] = [
                'Accueil des visiteurs',
                'Orientation et information',
                'Gestion des rendez-vous',
                'Communication interne'
            ];
            $data['type_recommendations'] = [
                'Vérifiez régulièrement les nouveaux visiteurs',
                'Tenez à jour les informations d\'orientation',
                'Communiquez avec l\'équipe de gestion'
            ];
        } elseif ($user->isConseillerUser()) {
            $data['type_description'] = 'Poste Conseiller - Support et assistance client';
            $data['type_features'] = [
                'Support client avancé',
                'Résolution de problèmes',
                'Conseils personnalisés',
                'Suivi client'
            ];
            $data['type_recommendations'] = [
                'Restez à jour sur les procédures',
                'Documentez les interactions clients',
                'Collaborez avec l\'équipe support'
            ];
        }

        return $data;
    }

    /**
     * 🆕 Informations utilisateur formatées
     */
    private function getUserInfo(User $user): array
    {
        return [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'mobile_number' => $user->mobile_number,
            'company' => $user->company,
            'type_info' => $user->getTypeInfo(),
            'status_info' => $user->getStatusInfo(),
            'creation_info' => $user->getCreationInfo(),
            'agency' => $user->agency ? [
                'name' => $user->agency->name,
                'city' => $user->agency->city,
                'country' => $user->agency->country
            ] : null,
            'login_info' => $user->getLastLoginInfo(),
            'password_info' => $user->getPasswordInfo()
        ];
    }

    // ===============================================
    // 🔒 VÉRIFICATION D'AUTORISATION
    // ===============================================

    /**
     * 🔒 Vérifier que l'admin connecté a créé cet utilisateur
     */
    private function checkUserOwnership(User $user): bool
    {
        $currentAdmin = Auth::user();
        
        // L'admin peut toujours se modifier lui-même
        if ($user->id === $currentAdmin->id) {
            return true;
        }
        
        // Vérifier via la table administrator_user
        return AdministratorUser::where('administrator_id', $currentAdmin->id)
                               ->where('user_id', $user->id)
                               ->exists();
    }

    // ===============================================
    // GESTION DES UTILISATEURS (Pour users-list)
    // ===============================================

    /**
     * ✅ CORRIGÉ : Liste des utilisateurs créés par l'admin connecté UNIQUEMENT
     * ISOLATION COMPLÈTE - Chaque admin ne voit QUE ses utilisateurs créés
     */
    public function usersList(Request $request)
    {
        try {
            $currentAdmin = Auth::user();
            
            if (!$currentAdmin->isAdmin()) {
                abort(403, 'Accès non autorisé');
            }

            // 🔒 FILTRAGE CORRECT : Récupérer seulement les utilisateurs créés par cet admin
            $myUserIds = AdministratorUser::where('administrator_id', $currentAdmin->id)
                                          ->pluck('user_id')
                                          ->toArray();
            
            // Inclure l'admin lui-même dans la liste (optionnel)
            $myUserIds[] = $currentAdmin->id;
            
            // 🔒 Variable pour la vue (condition du bouton Modifier)
            $myCreatedUserIds = $myUserIds;

            $query = User::whereIn('id', $myUserIds)
                        ->with(['userType', 'status', 'agency', 'createdBy']);

            // Recherche (dans ses utilisateurs uniquement)
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('username', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%")
                      ->orWhere('mobile_number', 'LIKE', "%{$search}%")
                      ->orWhere('company', 'LIKE', "%{$search}%");
                });
            }

            // Filtres par type
            if ($request->filled('user_type')) {
                $typeMapping = [
                    'admin' => 1,
                    'ecran' => 2,
                    'accueil' => 3,
                    'conseiller' => 4,
                ];
                
                if (isset($typeMapping[$request->user_type])) {
                    $query->where('user_type_id', $typeMapping[$request->user_type]);
                }
            }

            // Filtres par statut
            if ($request->filled('status')) {
                $statusMapping = [
                    'active' => 2,
                    'inactive' => 1,
                    'suspended' => 3,
                ];
                
                if (isset($statusMapping[$request->status])) {
                    $query->where('status_id', $statusMapping[$request->status]);
                }
            }

            // Filtres par agence (🔒 seulement ses agences)
            if ($request->filled('agency_id')) {
                $query->where('agency_id', $request->agency_id);
            }

            // Tri
            $sortBy = $request->get('sort', 'created_at');
            $sortOrder = $request->get('order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $users = $query->paginate(15)->appends($request->query());

            // 🔒 STATISTIQUES : Seulement pour les utilisateurs de cet admin
            $stats = [
                'total_my_users' => count($myUserIds) - 1, // -1 pour exclure l'admin lui-même du compte
                'active_my_users' => User::whereIn('id', $myUserIds)->where('status_id', 2)->count() - 1,
                'inactive_my_users' => User::whereIn('id', $myUserIds)->where('status_id', 1)->count(),
                'suspended_my_users' => User::whereIn('id', $myUserIds)->where('status_id', 3)->count(),
                'recent_my_users' => User::whereIn('id', $myUserIds)->where('created_at', '>=', now()->subDays(7))->count(),
            ];

            // 🔒 AGENCES : Seulement celles créées par cet admin pour les filtres
            $myAgencies = Agency::where('created_by', $currentAdmin->id)
                               ->orderBy('name')
                               ->get();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'users' => $users->map(function($user) {
                        return [
                            'id' => $user->id,
                            'username' => $user->username,
                            'email' => $user->email,
                            'mobile_number' => $user->mobile_number,
                            'company' => $user->company,
                            'type' => $user->getTypeName(),
                            'type_icon' => $user->getTypeIcon(),
                            'type_badge_color' => $user->getTypeBadgeColor(),
                            'type_emoji' => $user->getTypeEmoji(),
                            'agency' => $user->agency ? $user->agency->name : null,
                            'agency_id' => $user->agency_id,
                            'status' => $user->getStatusName(),
                            'status_badge_color' => $user->getStatusBadgeColor(),
                            'created_at' => $user->created_at->format('d/m/Y H:i'),
                            'last_login' => $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Jamais',
                            'is_active' => $user->isActive(),
                            'is_admin' => $user->isAdmin(),
                            'creation_info' => $user->getCreationInfo(),
                        ];
                    }),
                    'pagination' => [
                        'current_page' => $users->currentPage(),
                        'total' => $users->total(),
                        'per_page' => $users->perPage(),
                        'last_page' => $users->lastPage()
                    ],
                    'stats' => $stats
                ]);
            }

            return view('User.users-list', compact('users', 'stats', 'myAgencies', 'myCreatedUserIds'));

        } catch (\Exception $e) {
            \Log::error("Erreur liste utilisateurs pour admin " . Auth::id() . ": " . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la récupération des utilisateurs'
                ], 500);
            }
            
            return redirect()->route('layouts.app')
                    ->with('error', 'Erreur lors de la récupération des utilisateurs.');
        }
    }

    // ===============================================
    // ACTIONS SUR LES UTILISATEURS (users-list)
    // ===============================================

    /**
     * ✅ CORRIGÉ : Activer utilisateur (vérification d'autorisation)
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

        // 🔒 Vérifier l'autorisation
        if (!$this->checkUserOwnership($user)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez pas modifier cet utilisateur.'
                ], 403);
            }
            abort(403, 'Vous ne pouvez pas modifier cet utilisateur.');
        }

        try {
            $success = $user->activate();
            
            if ($success) {
                \Log::info("Utilisateur {$user->username} activé par " . Auth::user()->username);
                
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => "Utilisateur {$user->username} activé avec succès !"
                    ]);
                }
                
                return redirect()->back()->with('success', "Utilisateur {$user->username} activé !");
            }
            
            throw new \Exception('Échec de l\'activation');
            
        } catch (\Exception $e) {
            \Log::error("Erreur activation utilisateur: " . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'activation de l\'utilisateur.'
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Erreur lors de l\'activation.');
        }
    }

    /**
     * ✅ CORRIGÉ : Suspendre utilisateur (vérification d'autorisation)
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

        // 🔒 Vérifier l'autorisation
        if (!$this->checkUserOwnership($user)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez pas modifier cet utilisateur.'
                ], 403);
            }
            abort(403, 'Vous ne pouvez pas modifier cet utilisateur.');
        }

        // Empêcher un admin de se suspendre lui-même
        if ($user->id === Auth::id()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez pas vous suspendre vous-même.'
                ], 400);
            }
            return redirect()->back()->with('error', 'Vous ne pouvez pas vous suspendre vous-même.');
        }

        try {
            $success = $user->suspend();
            
            if ($success) {
                \Log::info("Utilisateur {$user->username} suspendu par " . Auth::user()->username);
                
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => "Utilisateur {$user->username} suspendu avec succès !"
                    ]);
                }
                
                return redirect()->back()->with('success', "Utilisateur {$user->username} suspendu !");
            }
            
            throw new \Exception('Échec de la suspension');
            
        } catch (\Exception $e) {
            \Log::error("Erreur suspension utilisateur: " . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la suspension de l\'utilisateur.'
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Erreur lors de la suspension.');
        }
    }

    /**
     * ✅ CORRIGÉ : Réactiver utilisateur (alias pour activate)
     */
    public function reactivateUser(User $user, Request $request)
    {
        return $this->activateUser($user, $request);
    }

    /**
     * ✅ CORRIGÉ : Supprimer utilisateur (vérification d'autorisation)
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

        // 🔒 Vérifier l'autorisation
        if (!$this->checkUserOwnership($user)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez pas supprimer cet utilisateur.'
                ], 403);
            }
            abort(403, 'Vous ne pouvez pas supprimer cet utilisateur.');
        }

        // Empêcher un admin de se supprimer lui-même
        if ($user->id === Auth::id()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez pas vous supprimer vous-même.'
                ], 400);
            }
            return redirect()->back()->with('error', 'Vous ne pouvez pas vous supprimer vous-même.');
        }

        try {
            $username = $user->username;
            
            // Supprimer la relation administrator_user
            AdministratorUser::where('user_id', $user->id)->delete();
            
            // Supprimer l'utilisateur
            $user->delete();
            
            \Log::info("Utilisateur {$username} supprimé par " . Auth::user()->username);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Utilisateur {$username} supprimé avec succès !"
                ]);
            }
            
            return redirect()->back()->with('success', "Utilisateur {$username} supprimé !");
            
        } catch (\Exception $e) {
            \Log::error("Erreur suppression utilisateur: " . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la suppression de l\'utilisateur.'
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Erreur lors de la suppression.');
        }
    }

    /**
     * ✅ CORRIGÉ : Actions en masse seulement sur ses utilisateurs
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
            $userIds = $request->input('user_ids', []);
            $currentAdminId = Auth::id();
            
            // 🔒 Récupérer les utilisateurs de l'admin connecté
            $myUserIds = AdministratorUser::where('administrator_id', $currentAdminId)
                                         ->pluck('user_id')
                                         ->toArray();

            // ✅ NOUVEAU : Si aucun user_ids, activer TOUS les inactifs
            if (empty($userIds)) {
                // Activer tous les utilisateurs inactifs de cet admin
                $count = User::whereIn('id', $myUserIds)
                            ->where('status_id', 1) // Seulement les inactifs
                            ->update(['status_id' => 2]);

                if ($count === 0) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Aucun utilisateur en attente d\'activation.'
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => "{$count} utilisateur(s) en attente activé(s) avec succès !"
                ]);
            }

            // ✅ EXISTANT : Mode sélection (gardé intact)
            // Vérifier que tous les utilisateurs appartiennent à l'admin
            $validUserIds = array_intersect($userIds, $myUserIds);
            
            if (empty($validUserIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun utilisateur autorisé dans la sélection.'
                ], 403);
            }

            $count = User::whereIn('id', $validUserIds)
                        ->where('status_id', '!=', 2)
                        ->update(['status_id' => 2]);

            return response()->json([
                'success' => true,
                'message' => "{$count} de vos utilisateur(s) activé(s) avec succès !"
            ]);

        } catch (\Exception $e) {
            \Log::error("Erreur activation en masse: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'activation en masse.'
            ], 500);
        }
    }

    /**
     * ✅ CORRIGÉ : Suppression en masse seulement sur ses utilisateurs
     */
    public function bulkDeleteUsers(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false, 
                'message' => 'Accès non autorisé'
            ], 403);
        }

        try {
            $userIds = $request->input('user_ids', []);
            
            if (empty($userIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun utilisateur sélectionné.'
                ], 400);
            }

            // Empêcher la suppression de soi-même
            $userIds = array_filter($userIds, function($id) {
                return $id != Auth::id();
            });

            // 🔒 SÉCURITÉ : Vérifier que tous les utilisateurs appartiennent à l'admin
            $myUserIds = AdministratorUser::where('administrator_id', Auth::id())
                                         ->pluck('user_id')
                                         ->toArray();
            
            $validUserIds = array_intersect($userIds, $myUserIds);
            
            if (empty($validUserIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun utilisateur autorisé dans la sélection.'
                ], 403);
            }

            // Supprimer les relations
            AdministratorUser::whereIn('user_id', $validUserIds)->delete();
            
            // Supprimer les utilisateurs
            $count = User::whereIn('id', $validUserIds)->delete();

            return response()->json([
                'success' => true,
                'message' => "{$count} de vos utilisateur(s) supprimé(s) avec succès !"
            ]);

        } catch (\Exception $e) {
            \Log::error("Erreur suppression en masse: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression en masse.'
            ], 500);
        }
    }

    /**
     * ✅ CORRIGÉ : Réinitialiser mot de passe (vérification d'autorisation)
     */
    public function resetUserPassword(User $user, Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false, 
                'message' => 'Accès non autorisé'
            ], 403);
        }

        // 🔒 Vérifier l'autorisation
        if (!$this->checkUserOwnership($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas réinitialiser le mot de passe de cet utilisateur.'
            ], 403);
        }

        try {
            // Générer un nouveau mot de passe
            $newPassword = $this->generateSecurePassword();
            $user->update(['password' => Hash::make($newPassword)]);

            // Marquer comme nécessitant une réinitialisation
            $adminUserRecord = AdministratorUser::where('administrator_id', Auth::id())
                ->where('user_id', $user->id)
                ->first();
            
            if ($adminUserRecord) {
                $adminUserRecord->update([
                    'password_reset_required' => true,
                    'temporary_password' => $newPassword
                ]);
            }

            \Log::info("Mot de passe réinitialisé pour {$user->username} par " . Auth::user()->username);

            return response()->json([
                'success' => true,
                'message' => "Mot de passe réinitialisé pour {$user->username}",
                'new_password' => $newPassword,
                'credentials' => [
                    'email' => $user->email,
                    'username' => $user->username,
                    'password' => $newPassword,
                    'login_url' => route('login')
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error("Erreur réinitialisation mot de passe: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la réinitialisation du mot de passe.'
            ], 500);
        }
    }

    // ===============================================
    // API AJAX POUR STATISTIQUES ET RECHERCHE
    // ===============================================

    /**
     * ✅ CORRIGÉ : Statistiques seulement pour les utilisateurs de l'admin
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
            
            // 🔒 IDS DES UTILISATEURS : Seulement ceux créés par cet admin
            $myUserIds = AdministratorUser::where('administrator_id', $currentAdmin->id)
                                         ->pluck('user_id')
                                         ->toArray();
            
            // Inclure l'admin lui-même
            $myUserIds[] = $currentAdmin->id;

            $stats = [
                'my_total_users' => count($myUserIds) - 1, // -1 pour exclure l'admin du compte
                'my_active_users' => User::whereIn('id', $myUserIds)->where('status_id', 2)->count() - 1,
                'my_inactive_users' => User::whereIn('id', $myUserIds)->where('status_id', 1)->count(),
                'my_suspended_users' => User::whereIn('id', $myUserIds)->where('status_id', 3)->count(),
                'my_recent_users' => User::whereIn('id', $myUserIds)->where('created_at', '>=', now()->subDays(7))->count(),
                'my_users_needing_password_reset' => AdministratorUser::where('administrator_id', $currentAdmin->id)
                                                                     ->where('password_reset_required', true)
                                                                     ->count(),
                
                // Statistiques par type (seulement mes utilisateurs)
                'my_admin_users' => User::whereIn('id', $myUserIds)->where('user_type_id', 1)->count(),
                'my_ecran_users' => User::whereIn('id', $myUserIds)->where('user_type_id', 2)->count(),
                'my_accueil_users' => User::whereIn('id', $myUserIds)->where('user_type_id', 3)->count(),
                'my_conseiller_users' => User::whereIn('id', $myUserIds)->where('user_type_id', 4)->count(),
                
                // Mes agences et services
                'my_agencies' => Agency::where('created_by', $currentAdmin->id)->count(),
                'my_active_agencies' => Agency::where('created_by', $currentAdmin->id)->where('status', 'active')->count(),
                'my_services' => Service::where('created_by', $currentAdmin->id)->count(),
                'my_active_services' => Service::where('created_by', $currentAdmin->id)->where('statut', 'actif')->count(),
            ];

            return response()->json([
                'success' => true, 
                'stats' => $stats,
                'admin_info' => [
                    'id' => $currentAdmin->id,
                    'username' => $currentAdmin->username,
                    'email' => $currentAdmin->email
                ],
                'timestamp' => now()->format('d/m/Y H:i:s')
            ]);
            
        } catch (\Exception $e) {
            \Log::error("Erreur statistiques isolées: " . $e->getMessage());
            
            return response()->json([
                'success' => false, 
                'message' => 'Erreur lors de la récupération des statistiques'
            ], 500);
        }
    }

    /**
     * ✅ CORRIGÉ : Recherche seulement dans ses utilisateurs
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
            // 🔒 RECHERCHE : Seulement dans ses utilisateurs
            $currentAdminId = Auth::id();
            $myUserIds = AdministratorUser::where('administrator_id', $currentAdminId)
                                         ->pluck('user_id')
                                         ->toArray();
            $myUserIds[] = $currentAdminId;
            
            $users = User::whereIn('id', $myUserIds)
                        ->where(function($q) use ($search) {
                            $q->where('username', 'LIKE', "%{$search}%")
                              ->orWhere('email', 'LIKE', "%{$search}%")
                              ->orWhere('mobile_number', 'LIKE', "%{$search}%")
                              ->orWhere('company', 'LIKE', "%{$search}%");
                        })
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
     * ✅ CORRIGÉ : Détails utilisateur (vérification d'autorisation)
     */
    public function getUserDetails(User $user, Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false, 
                'message' => 'Accès non autorisé'
            ], 403);
        }

        // 🔒 Vérifier l'autorisation
        if (!$this->checkUserOwnership($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas voir les détails de cet utilisateur.'
            ], 403);
        }

        try {
            $user->load(['userType', 'status', 'agency', 'createdBy.administrator']);
            
            $userDetails = [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'mobile_number' => $user->mobile_number,
                'company' => $user->company,
                'type_info' => $user->getTypeInfo(),
                'status_info' => $user->getStatusInfo(),
                'agency' => $user->agency ? [
                    'id' => $user->agency->id,
                    'name' => $user->agency->name,
                    'city' => $user->agency->city,
                    'country' => $user->agency->country
                ] : null,
                'login_info' => $user->getLastLoginInfo(),
                'password_info' => $user->getPasswordInfo(),
                'security_info' => $user->getLoginAttemptsInfo(),
                'creation_info' => $user->getCreationInfo(),
                'required_actions' => $user->getRequiredActions(),
                'permissions' => [
                    'can_edit' => true, // Puisqu'on a vérifié l'autorisation
                    'can_delete' => $user->id !== Auth::id(), // Ne peut pas se supprimer
                    'can_suspend' => $user->id !== Auth::id(), // Ne peut pas se suspendre
                    'can_reset_password' => true,
                ]
            ];

            return response()->json([
                'success' => true,
                'user' => $userDetails
            ]);

        } catch (\Exception $e) {
            \Log::error("Erreur détails utilisateur: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des détails.'
            ], 500);
        }
    }

    // ===============================================
    // STATISTIQUES AVANCÉES
    // ===============================================

    /**
     * ✅ CORRIGÉ : Statistiques avancées isolées
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
            $currentAdminId = Auth::id();
            
            // 🔒 ISOLATION - Statistiques pour SES utilisateurs uniquement
            $myUserIds = AdministratorUser::where('administrator_id', $currentAdminId)
                                         ->pluck('user_id')
                                         ->toArray();
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
                'message' => 'Erreur lors de la récupération des statistiques avancées'
            ], 500);
        }
    }

    // ===============================================
    // EXPORT ET UTILITAIRES
    // ===============================================

    /**
     * ✅ CORRIGÉ : Export seulement des utilisateurs de l'admin
     */
    public function exportUsers(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Accès non autorisé');
        }

        try {
            // 🔒 EXPORT : Seulement ses propres utilisateurs
            $currentAdminId = Auth::id();
            $myUserIds = AdministratorUser::where('administrator_id', $currentAdminId)
                                         ->pluck('user_id')
                                         ->toArray();
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

    /**
     * Générer un mot de passe sécurisé
     */
    private function generateSecurePassword($length = 8): string 
    {
        $voyelles = 'aeiou';
        $consonnes = 'bcdfghjklmnpqrstvwxz';
        $password = '';
        
        $password .= strtoupper($consonnes[rand(0, strlen($consonnes) - 1)]);
        $password .= $voyelles[rand(0, strlen($voyelles) - 1)];
        $password .= $consonnes[rand(0, strlen($consonnes) - 1)];
        $password .= rand(100, 999);
        $password .= '@';
        
        return $password;
    }

    
    /**
     * Formater l'âge du compte
     */
    private function formatAccountAge(int $days): string
    {
        if ($days < 1) {
            return 'Moins d\'un jour';
        } elseif ($days === 1) {
            return '1 jour';
        } elseif ($days < 7) {
            return $days . ' jours';
        } elseif ($days < 30) {
            $weeks = floor($days / 7);
            return $weeks . ' semaine' . ($weeks > 1 ? 's' : '');
        } elseif ($days < 365) {
            $months = floor($days / 30);
            return $months . ' mois';
        } else {
            $years = floor($days / 365);
            return $years . ' an' . ($years > 1 ? 's' : '');
        }
    }
    
}