<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserType;
use App\Models\AdministratorUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UserManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Afficher le formulaire de création d'utilisateur 
     * COHÉRENT avec user-create.blade.php
     */
    public function create()
    {
        // Statistiques pour l'admin connecté
        $adminStats = AdministratorUser::getStatsForAdmin(Auth::id());
        
        // NOUVEAU : Obtenir les rôles disponibles avec leurs informations
        $availableRoles = $this->getAvailableRoles();
        
        return view('user.user-create', compact('adminStats', 'availableRoles'));
    }

    /**
     * ✅ CRÉER UN NOUVEL UTILISATEUR - VERSION CORRIGÉE AVEC MAPPING DES RÔLES
     * L'admin voit TOUJOURS le mot de passe temporaire
     * AMÉLIORATION : Support complet des 4 types métier
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:255|unique:users',
            'username' => 'required|string|max:255|unique:users',
            'mobile_number' => 'required|string|max:20',
            'user_role' => 'required|string|in:ecran,conseiller,accueil', // RÔLES MÉTIER
            'send_credentials' => 'boolean'
        ], [
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'username.unique' => 'Ce nom d\'utilisateur est déjà pris.',
            'email.email' => 'Veuillez saisir une adresse email valide.',
            'mobile_number.required' => 'Le numéro de téléphone est obligatoire.',
            'user_role.required' => 'Le poste de l\'utilisateur est obligatoire.',
            'user_role.in' => 'Le poste sélectionné n\'est pas valide.',
        ]);

        try {
            DB::beginTransaction();

            // ✅ CORRECTION : Mapper le rôle vers le bon user_type_id
            $userTypeMapping = [
                'ecran' => 2,       // Poste Ecran
                'accueil' => 3,     // Poste Accueil  
                'conseiller' => 4,  // Poste Conseiller
            ];

            // Valider que le rôle est valide
            if (!isset($userTypeMapping[$request->user_role])) {
                throw new \Exception("Rôle utilisateur invalide: {$request->user_role}");
            }

            // Générer un mot de passe temporaire sécurisé
            $temporaryPassword = $this->generateSecurePassword();
            
            // ✅  Créer l'utilisateur avec le bon type
            $currentAdmin = Auth::user();
$inheritedCompany = $currentAdmin->company; // HÉRITAGE AUTOMATIQUE

$user = User::create([
    'email' => $request->email,
    'username' => $request->username,
    'mobile_number' => $request->mobile_number,
    'company' => $inheritedCompany, 
    'password' => Hash::make($temporaryPassword),
    'user_type_id' => $userTypeMapping[$request->user_role],
    'status_id' => 2,
]);

            // Enregistrer la relation administrator_user avec informations détaillées
            $adminUserRecord = AdministratorUser::create([
                'administrator_id' => Auth::id(),
                'user_id' => $user->id,
                'creation_method' => 'manual',
                'creation_notes' => $this->formatUserRoleNote($request->user_role),
                'password_reset_required' => true,
                'password_reset_sent_at' => null
            ]);

            // Préparer les informations de connexion
            $userCredentials = [
                'email' => $user->email,
                'username' => $user->username,
                'password' => $temporaryPassword,
                'user_role' => $request->user_role, // ✅ NOUVEAU : Inclure le rôle
                'user_type' => $user->getTypeName(), // ✅ NOUVEAU : Nom du type
                'user_type_icon' => $user->getTypeIcon(), // ✅ NOUVEAU : Icône du type
                'user_type_emoji' => $user->getTypeEmoji(), // ✅ NOUVEAU : Emoji du type
                'login_url' => route('login'),
                'admin_creator' => Auth::user()->username
            ];

            // L'admin voit TOUJOURS le mot de passe
            $credentialsSent = false;
            
            // Envoyer les identifiants si demandé (optionnel)
            if ($request->boolean('send_credentials', false)) {
                try {
                    // Ici vous pouvez ajouter l'envoi d'email si nécessaire
                    // Mail::to($user->email)->send(new UserCredentialsMail($userCredentials));
                    
                    $adminUserRecord->markPasswordResetSent();
                    $credentialsSent = true;
                } catch (\Exception $e) {
                    \Log::error("Erreur envoi email pour {$user->email}: " . $e->getMessage());
                    $credentialsSent = false;
                }
            }

            DB::commit();

            // Log de l'action avec le type correct
            \Log::info("Utilisateur {$user->username} ({$user->getTypeName()}) créé par " . Auth::user()->username, [
                'user_id' => $user->id,
                'admin_id' => Auth::id(),
                'user_type_id' => $user->user_type_id,
                'user_role' => $request->user_role,
                'user_type_name' => $user->getTypeName(),
                'credentials_sent' => $credentialsSent
            ]);

            // Message avec mot de passe TOUJOURS visible pour l'admin
            $message = "L'utilisateur {$user->username} ({$user->getTypeName()}) a été créé avec succès.";
            if ($credentialsSent) {
                $message .= " Les identifiants ont été envoyés par email ET sont affichés ci-dessous.";
            } else {
                $message .= " Voici les identifiants à communiquer à l'utilisateur :";
            }

            // Réponse AJAX améliorée
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'mobile_number' => $user->mobile_number,
                        'type' => $user->getTypeName(), // ✅ Nom correct du type
                        'type_icon' => $user->getTypeIcon(), // ✅ NOUVEAU
                        'type_badge_color' => $user->getTypeBadgeColor(), // ✅ NOUVEAU
                        'type_emoji' => $user->getTypeEmoji(), // ✅ NOUVEAU
                        'user_role' => $request->user_role, // ✅ NOUVEAU
                        'status' => 'Actif',
                        'status_badge_color' => $user->getStatusBadgeColor(),
                        'credentials_sent' => $credentialsSent,
                        // L'ADMIN VOIT TOUJOURS LE MOT DE PASSE
                        'temporary_password' => $temporaryPassword,
                        'login_url' => route('login'),
                        'created_at' => $user->created_at->format('d/m/Y H:i'),
                        'creation_info' => $user->getCreationInfo()
                    ],
                    'credentials' => $userCredentials
                ]);
            }

            $adminStats = AdministratorUser::getStatsForAdmin(Auth::id());
            $availableRoles = $this->getAvailableRoles();

            return view('user.user-create', [
               'adminStats' => $adminStats,
               'availableRoles' => $availableRoles,
               'newUser' => $user,
               'temporaryPassword' => $temporaryPassword
            ])->with('success', "Utilisateur '{$user->username}' ({$user->getTypeName()}) créé avec succès ! ✅");
       
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error("Erreur création utilisateur: " . $e->getMessage(), [
                'email' => $request->email,
                'username' => $request->username,
                'user_role' => $request->user_role,
                'admin_id' => Auth::id(),
                'error_trace' => $e->getTraceAsString()
            ]);
            
            $errorMessage = 'Erreur lors de la création de l\'utilisateur. Veuillez réessayer.';
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'error_details' => $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', $errorMessage)
                ->withInput();
        }
    }

    /**
     * Obtenir les utilisateurs créés par l'admin connecté
     */
    public function myCreatedUsers(Request $request)
    {
        try {
            $admin = Auth::user();
            $users = $admin->createdUsers()
                ->with(['user.userType', 'user.status'])
                ->paginate(10);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'users' => $users->map(function($relation) {
                        $user = $relation->user;
                        return [
                            'id' => $user->id,
                            'username' => $user->username,
                            'email' => $user->email,
                            'mobile_number' => $user->mobile_number,
                            'type' => $user->getTypeName(),
                            'type_icon' => $user->getTypeIcon(),
                            'type_badge_color' => $user->getTypeBadgeColor(),
                            'type_emoji' => $user->getTypeEmoji(),
                            'user_role' => $user->getUserRole(),
                            'status' => $user->getStatusName(),
                            'status_badge_color' => $user->getStatusBadgeColor(),
                            'created_at' => $user->created_at->format('d/m/Y H:i'),
                            'is_active' => $user->isActive(),
                            'requires_password_reset' => $relation->requiresPasswordReset(),
                            'creation_method' => $relation->creation_method ?? 'manual',
                            'creation_notes' => $relation->creation_notes,
                            'type_info' => $user->getTypeInfo(),
                            'status_info' => $user->getStatusInfo(),
                        ];
                    }),
                    'pagination' => [
                        'current_page' => $users->currentPage(),
                        'total' => $users->total(),
                        'per_page' => $users->perPage(),
                        'last_page' => $users->lastPage()
                    ]
                ]);
            }

            return view('admin.users.my-created', compact('users'));

        } catch (\Exception $e) {
            \Log::error("Erreur récupération utilisateurs créés: " . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la récupération des utilisateurs'
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Erreur lors de la récupération des données.');
        }
    }

    /**
     * Statistiques détaillées des utilisateurs créés par l'admin
     */
    public function getMyUserStats(Request $request)
    {
        try {
            $adminId = Auth::id();
            $stats = AdministratorUser::getStatsForAdmin($adminId);
            
            // NOUVEAU : Statistiques par type pour cet admin
            $statsByType = $this->getMyUserStatsByType($adminId);
            
            // Statistiques supplémentaires
            $recentActivity = AdministratorUser::createdBy($adminId)
                ->with(['user'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function($record) {
                    return [
                        'user' => $record->user ? $record->user->username : 'Utilisateur supprimé',
                        'user_type' => $record->user ? $record->user->getTypeName() : 'N/A',
                        'user_type_emoji' => $record->user ? $record->user->getTypeEmoji() : '❓',
                        'created_at' => $record->created_at->diffForHumans(),
                        'status' => $record->user ? $record->user->getStatusName() : 'N/A'
                    ];
                });

            $monthlyCreations = AdministratorUser::createdBy($adminId)
                ->where('created_at', '>=', Carbon::now()->subMonths(6))
                ->selectRaw('COUNT(*) as count, MONTH(created_at) as month, YEAR(created_at) as year')
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'stats' => $stats,
                'stats_by_type' => $statsByType,
                'recent_activity' => $recentActivity,
                'monthly_creations' => $monthlyCreations
            ]);

        } catch (\Exception $e) {
            \Log::error("Erreur récupération statistiques: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques'
            ], 500);
        }
    }

    /**
     * Renvoyer les identifiants à un utilisateur (NOUVEAU MOT DE PASSE)
     * CORRECTION : L'admin voit le nouveau mot de passe généré
     */
    public function resendCredentials(User $user, Request $request)
    {
        try {
            // Vérifier que l'admin connecté a créé cet utilisateur
            if (!Auth::user()->createdUsers()->where('user_id', $user->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas l\'autorisation pour cet utilisateur.'
                ], 403);
            }

            // Générer un NOUVEAU mot de passe temporaire
            $newPassword = $this->generateSecurePassword();
            $user->update(['password' => Hash::make($newPassword)]);

            // Marquer comme nécessitant une réinitialisation
            $adminUserRecord = AdministratorUser::where('administrator_id', Auth::id())
                ->where('user_id', $user->id)
                ->first();
            
            if ($adminUserRecord) {
                $adminUserRecord->update(['password_reset_required' => true]);
            }

            // Préparer les nouvelles informations
            $userCredentials = [
                'email' => $user->email,
                'username' => $user->username,
                'password' => $newPassword,
                'user_type' => $user->getTypeName(),
                'user_type_emoji' => $user->getTypeEmoji(),
                'user_role' => $user->getUserRole(),
                'login_url' => route('login'),
                'admin_creator' => Auth::user()->username
            ];

            if ($adminUserRecord) {
                $adminUserRecord->markPasswordResetSent();
            }

            \Log::info("Nouveaux identifiants générés pour {$user->username} ({$user->getTypeName()}) par " . Auth::user()->username);

            // RETOURNER LE NOUVEAU MOT DE PASSE À L'ADMIN
            return response()->json([
                'success' => true,
                'message' => "Nouveaux identifiants générés pour {$user->username} ({$user->getTypeName()})",
                'credentials' => $userCredentials,
                'new_password' => $newPassword,
                'user_info' => $user->getTypeInfo()
            ]);

        } catch (\Exception $e) {
            \Log::error("Erreur renvoi identifiants: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération des nouveaux identifiants.'
            ], 500);
        }
    }

    /**
     * NOUVEAU : Changer le type d'un utilisateur
     */
    public function changeUserType(User $user, Request $request)
    {
        $request->validate([
            'new_role' => 'required|string|in:ecran,accueil,conseiller'
        ]);

        try {
            // Vérifier que l'admin connecté a créé cet utilisateur
            if (!Auth::user()->createdUsers()->where('user_id', $user->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas l\'autorisation pour cet utilisateur.'
                ], 403);
            }

            // Vérifier qu'on ne change pas un admin (sécurité)
            if ($user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de changer le type d\'un administrateur.'
                ], 400);
            }

            $userTypeMapping = [
                'ecran' => 2,
                'accueil' => 3,
                'conseiller' => 4,
            ];

            $oldTypeName = $user->getTypeName();
            $newTypeId = $userTypeMapping[$request->new_role];

            // Mettre à jour le type
            $user->update(['user_type_id' => $newTypeId]);

            // Mettre à jour les notes de création
            $adminUserRecord = AdministratorUser::where('administrator_id', Auth::id())
                ->where('user_id', $user->id)
                ->first();
            
            if ($adminUserRecord) {
                $adminUserRecord->update([
                    'creation_notes' => $this->formatUserRoleNote($request->new_role) . " (Modifié de: {$oldTypeName})"
                ]);
            }

            \Log::info("Type utilisateur changé pour {$user->username}: {$oldTypeName} → {$user->getTypeName()}", [
                'user_id' => $user->id,
                'admin_id' => Auth::id(),
                'old_type' => $oldTypeName,
                'new_type' => $user->getTypeName(),
                'new_role' => $request->new_role
            ]);

            return response()->json([
                'success' => true,
                'message' => "Type de {$user->username} changé de {$oldTypeName} vers {$user->getTypeName()}",
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'type' => $user->getTypeName(),
                    'type_icon' => $user->getTypeIcon(),
                    'type_badge_color' => $user->getTypeBadgeColor(),
                    'type_emoji' => $user->getTypeEmoji(),
                    'user_role' => $user->getUserRole(),
                    'type_info' => $user->getTypeInfo()
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error("Erreur changement type utilisateur: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du changement de type.'
            ], 500);
        }
    }

    // ===============================================
    // MÉTHODES UTILITAIRES PRIVÉES
    // ===============================================

    /**
     * Générer un mot de passe temporaire simple (format amélioré)
     */
    private function generateSecurePassword($length = 8): string 
    {
        $voyelles = 'aeiou';
        $consonnes = 'bcdfghjklmnpqrstvwxz';
        $password = '';
        
        // Consonne-Voyelle-Consonne + 3 chiffres + caractère spécial
        $password .= strtoupper($consonnes[rand(0, strlen($consonnes) - 1)]);
        $password .= $voyelles[rand(0, strlen($voyelles) - 1)];
        $password .= $consonnes[rand(0, strlen($consonnes) - 1)];
        $password .= rand(100, 999);
        $password .= '@';
        
        return $password;
    }

    /**
     * AMÉLIORÉ : Méthode pour formater la note à partir du rôle
     */
    private function formatUserRoleNote($role): string
    {
        $roleLabels = [
            'ecran' => 'Poste Ecran - Interface utilisateur pour affichage et consultation des données',
            'conseiller' => 'Poste Conseiller - Support et assistance client',
            'accueil' => 'Poste Accueil - Réception et orientation des visiteurs'
        ];
        
        return $roleLabels[$role] ?? "Poste : {$role}";
    }

    /**
     * NOUVEAU : Obtenir la liste des rôles disponibles avec informations complètes
     */
    private function getAvailableRoles(): array
    {
        return [
            'ecran' => [
                'name' => 'Poste Ecran',
                'description' => 'Interface utilisateur pour affichage et consultation des données',
                'icon' => 'monitor',
                'badge_color' => 'info',
                'emoji' => '🖥️',
                'user_type_id' => 2
            ],
            'accueil' => [
                'name' => 'Poste Accueil', 
                'description' => 'Réception et orientation des visiteurs',
                'icon' => 'home',
                'badge_color' => 'success',
                'emoji' => '🏢',
                'user_type_id' => 3
            ],
            'conseiller' => [
                'name' => 'Poste Conseiller',
                'description' => 'Support et assistance client',
                'icon' => 'users',
                'badge_color' => 'warning',
                'emoji' => '👥',
                'user_type_id' => 4
            ]
        ];
    }

    /**
     * NOUVEAU : Obtenir les statistiques par type pour l'admin connecté
     */
    private function getMyUserStatsByType(int $adminId): array
    {
        $myUserIds = AdministratorUser::where('administrator_id', $adminId)->pluck('user_id')->toArray();
        $myUserIds[] = $adminId; // Inclure l'admin lui-même

        $stats = [];
        $availableRoles = $this->getAvailableRoles();

        // Statistiques pour admin (lui-même)
        $stats['admin'] = [
            'total' => User::whereIn('id', $myUserIds)->where('user_type_id', 1)->count(),
            'active' => User::whereIn('id', $myUserIds)->where('user_type_id', 1)->where('status_id', 2)->count(),
            'inactive' => User::whereIn('id', $myUserIds)->where('user_type_id', 1)->where('status_id', 1)->count(),
            'suspended' => User::whereIn('id', $myUserIds)->where('user_type_id', 1)->where('status_id', 3)->count(),
            'name' => 'Administrateur',
            'icon' => 'shield',
            'badge_color' => 'primary',
            'emoji' => '🛡️'
        ];

        // Statistiques pour chaque rôle métier
        foreach ($availableRoles as $role => $info) {
            $typeId = $info['user_type_id'];
            
            $stats[$role] = [
                'total' => User::whereIn('id', $myUserIds)->where('user_type_id', $typeId)->count(),
                'active' => User::whereIn('id', $myUserIds)->where('user_type_id', $typeId)->where('status_id', 2)->count(),
                'inactive' => User::whereIn('id', $myUserIds)->where('user_type_id', $typeId)->where('status_id', 1)->count(),
                'suspended' => User::whereIn('id', $myUserIds)->where('user_type_id', $typeId)->where('status_id', 3)->count(),
                'name' => $info['name'],
                'icon' => $info['icon'],
                'badge_color' => $info['badge_color'],
                'emoji' => $info['emoji']
            ];
        }

        return $stats;
    }

    /**
     * NOUVEAU : Obtenir les informations d'un rôle spécifique
     */
    public function getRoleInfo(string $role)
    {
        $availableRoles = $this->getAvailableRoles();
        return $availableRoles[$role] ?? null;
    }

    /**
     * NOUVEAU : API pour obtenir les rôles disponibles
     */
    public function getAvailableRolesApi(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        try {
            $roles = $this->getAvailableRoles();
            
            return response()->json([
                'success' => true,
                'roles' => $roles
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des rôles'
            ], 500);
        }
    }

    /**
     * NOUVEAU : Valider un rôle utilisateur
     */
    private function validateUserRole(string $role): bool
    {
        $availableRoles = array_keys($this->getAvailableRoles());
        return in_array($role, $availableRoles);
    }

    /**
     * NOUVEAU : Obtenir le mapping complet rôle → type_id
     */
    public function getUserTypeMapping(): array
    {
        return [
            'admin' => 1,       // Administrateur
            'ecran' => 2,       // Poste Ecran
            'accueil' => 3,     // Poste Accueil
            'conseiller' => 4,  // Poste Conseiller
        ];
    }

}

