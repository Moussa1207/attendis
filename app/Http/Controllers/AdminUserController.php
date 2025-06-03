<?php
// app/Http/Controllers/AdminUserController.php (VERSION COMPLÈTE)

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AdministratorUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Afficher le formulaire de création d'utilisateur
     */
    public function create()
    {
        return view('user.user-create');
    }

    /**
     * Créer un nouvel utilisateur (appelé par les admins)
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:255|unique:users',
            'username' => 'required|string|max:255|unique:users',
            'mobile_number' => 'required|string|max:20',
        ], [
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'username.unique' => 'Ce nom d\'utilisateur est déjà pris.',
            'email.required' => 'L\'adresse email est obligatoire.',
            'username.required' => 'Le nom d\'utilisateur est obligatoire.',
            'mobile_number.required' => 'Le numéro de téléphone est obligatoire.',
        ]);

        try {
            // Générer un mot de passe temporaire sécurisé
            $temporaryPassword = $this->generateSecurePassword();
            
            // Créer l'utilisateur avec statut ACTIF par défaut (comme demandé)
            $user = User::create([
                'email' => $request->email,
                'username' => $request->username,
                'mobile_number' => $request->mobile_number,
                'password' => Hash::make($temporaryPassword),
                'user_type_id' => 2, // 2 = Utilisateur normal (pas admin)
                'status_id' => 2, // 2 = Actif par défaut (comme demandé)
            ]);

            // Créer la relation administrator_user pour traçabilité
            AdministratorUser::createRelation(Auth::id(), $user->id);

            // Log de l'activité
            \Log::info('User created by admin', [
                'created_user_id' => $user->id,
                'created_user_username' => $user->username,
                'created_user_email' => $user->email,
                'admin_id' => Auth::id(),
                'admin_username' => Auth::user()->username,
                'temporary_password_length' => strlen($temporaryPassword)
            ]);

            // Réponse pour AJAX
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Utilisateur créé avec succès !',
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'mobile_number' => $user->mobile_number,
                        'temporary_password' => $temporaryPassword,
                        'status' => 'active',
                        'created_by' => Auth::user()->username,
                    ],
                    'redirect_url' => route('user.users-list')
                ]);
            }

            // Redirection avec message de succès et données utilisateur
            // Rester sur la même page avec le mot de passe affiché
        return redirect()->back()->with([
          'success' => " UTILISATEUR CRÉÉ AVEC SUCCÈS !

          Nom : {$user->username}
          Email : {$user->email}  
          Mot de passe temporaire : {$temporaryPassword}

       ⚠️ IMPORTANT : Communiquez ces informations à l'utilisateur de manière sécurisée.
       L'utilisateur devra changer ce mot de passe lors de sa première connexion."
]);

        } catch (\Exception $e) {
            // Log de l'erreur
            \Log::error('Error creating user', [
                'error' => $e->getMessage(),
                'admin_id' => Auth::id(),
                'request_data' => $request->only(['email', 'username', 'mobile_number'])
            ]);

            // Gestion d'erreur
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la création : ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la création de l\'utilisateur : ' . $e->getMessage());
        }
    }

    /**
     * Obtenir les informations d'un utilisateur pour l'admin
     */
    public function show(User $user, Request $request)
    {
        try {
            // Charger les relations
            $user->load(['userType', 'status', 'createdBy.administrator']);
            
            $creationInfo = $user->getCreationInfo();
            
            $userDetails = [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'mobile_number' => $user->mobile_number,
                'type' => $user->getTypeName(),
                'status' => $user->getStatusName(),
                'is_admin' => $user->isAdmin(),
                'is_active' => $user->isActive(),
                'is_suspended' => $user->isSuspended(),
                'created_at' => $user->created_at->format('d/m/Y à H:i'),
                'updated_at' => $user->updated_at->format('d/m/Y à H:i'),
                'creation_info' => $creationInfo,
                'can_be_deleted' => $this->canUserBeDeleted($user),
                'can_be_suspended' => $this->canUserBeSuspended($user),
            ];

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'user' => $userDetails
                ]);
            }

            return view('admin.users.show', compact('user', 'userDetails'));

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la récupération des détails'
                ], 500);
            }

            return redirect()->back()->with('error', 'Erreur lors du chargement des détails utilisateur');
        }
    }

    /**
     * Générer un lien de réinitialisation de mot de passe
     */
    public function generatePasswordResetLink(User $user, Request $request)
    {
        try {
            // Générer un token sécurisé
            $token = Str::random(60);
            
            // Stocker le token dans le cache (1 heure d'expiration)
            \Cache::put("password_reset_{$user->id}", $token, 3600);
            
            // Générer le lien
            $resetLink = route('password.reset', ['token' => $token, 'user' => $user->id]);
            
            // Log de l'activité
            \Log::info('Password reset link generated', [
                'target_user_id' => $user->id,
                'target_user_username' => $user->username,
                'admin_id' => Auth::id(),
                'admin_username' => Auth::user()->username,
                'expires_at' => now()->addHour()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Lien de réinitialisation généré avec succès !',
                    'reset_link' => $resetLink,
                    'expires_in' => '1 heure',
                    'user_info' => [
                        'username' => $user->username,
                        'email' => $user->email
                    ]
                ]);
            }

            return redirect()->back()->with([
                'success' => 'Lien de réinitialisation généré avec succès !',
                'reset_link' => $resetLink,
                'target_user' => $user->username
            ]);

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la génération du lien'
                ], 500);
            }

            return redirect()->back()->with('error', 'Erreur lors de la génération du lien');
        }
    }

    /**
     * Statistiques personnalisées pour l'admin connecté
     */
    public function getAdminStats(Request $request)
    {
        try {
            $currentAdmin = Auth::user();
            
            // Statistiques personnelles
            $myCreatedUsers = $currentAdmin->createdUsers()->with('user')->get();
            
            $stats = [
                'total_users_created_by_me' => $myCreatedUsers->count(),
                'active_users_created_by_me' => $myCreatedUsers->where('user.status_id', 2)->count(),
                'inactive_users_created_by_me' => $myCreatedUsers->where('user.status_id', 1)->count(),
                'suspended_users_created_by_me' => $myCreatedUsers->where('user.status_id', 3)->count(),
                
                // Statistiques temporelles
                'users_created_today' => $currentAdmin->createdUsers()
                    ->whereHas('user', function($query) {
                        $query->whereDate('created_at', today());
                    })->count(),
                    
                'users_created_this_week' => $currentAdmin->createdUsers()
                    ->whereHas('user', function($query) {
                        $query->where('created_at', '>=', now()->startOfWeek());
                    })->count(),
                    
                'users_created_this_month' => $currentAdmin->createdUsers()
                    ->whereHas('user', function($query) {
                        $query->where('created_at', '>=', now()->startOfMonth());
                    })->count(),
                
                // Derniers utilisateurs créés
                'recent_users_created' => $myCreatedUsers->sortByDesc('created_at')->take(5)->map(function($relation) {
                    return [
                        'id' => $relation->user->id,
                        'username' => $relation->user->username,
                        'email' => $relation->user->email,
                        'status' => $relation->user->getStatusName(),
                        'created_at' => $relation->created_at->format('d/m/Y H:i')
                    ];
                })->values(),
                
                // Taux de réussite
                'success_rate' => $myCreatedUsers->count() > 0 ? 
                    round(($myCreatedUsers->where('user.status_id', 2)->count() / $myCreatedUsers->count()) * 100, 2) : 0,
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats,
                'admin_info' => [
                    'username' => $currentAdmin->username,
                    'total_created' => $myCreatedUsers->count()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques personnelles'
            ], 500);
        }
    }

    /**
     * Recherche avancée d'utilisateurs avec filtres
     */
    public function searchUsers(Request $request)
    {
        try {
            $query = User::with(['userType', 'status', 'createdBy.administrator']);
            
            // Filtre par terme de recherche
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('mobile_number', 'like', "%{$search}%");
                });
            }
            
            // Filtre par créateur (utilisateurs créés par moi)
            if ($request->filled('created_by_me') && $request->created_by_me == 'true') {
                $myUserIds = Auth::user()->createdUsers()->pluck('user_id');
                $query->whereIn('id', $myUserIds);
            }
            
            // Filtre par statut
            if ($request->filled('status')) {
                $statusMap = ['active' => 2, 'inactive' => 1, 'suspended' => 3];
                if (isset($statusMap[$request->status])) {
                    $query->where('status_id', $statusMap[$request->status]);
                }
            }
            
            // Filtre par type
            if ($request->filled('type')) {
                $typeMap = ['admin' => 1, 'user' => 2];
                if (isset($typeMap[$request->type])) {
                    $query->where('user_type_id', $typeMap[$request->type]);
                }
            }
            
            // Limite et tri
            $limit = min($request->get('limit', 10), 50); // Max 50 résultats
            $users = $query->orderBy('created_at', 'desc')->limit($limit)->get();
            
            $results = $users->map(function($user) {
                $creationInfo = $user->getCreationInfo();
                return [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'mobile_number' => $user->mobile_number,
                    'type' => $user->getTypeName(),
                    'status' => $user->getStatusName(),
                    'is_admin' => $user->isAdmin(),
                    'is_active' => $user->isActive(),
                    'created_at' => $user->created_at->format('d/m/Y H:i'),
                    'created_by' => $creationInfo ? $creationInfo['created_by'] : 'Inscription directe',
                ];
            });

            return response()->json([
                'success' => true,
                'users' => $results,
                'total_found' => $users->count(),
                'search_params' => $request->only(['search', 'status', 'type', 'created_by_me'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la recherche'
            ], 500);
        }
    }

    /**
     * Vérifier si un utilisateur peut être supprimé
     */
    private function canUserBeDeleted(User $user): bool
    {
        // Ne pas supprimer soi-même
        if ($user->id === Auth::id()) {
            return false;
        }
        
        // Ne pas supprimer le dernier admin actif
        if ($user->isAdmin()) {
            $activeAdmins = User::where('user_type_id', 1)->where('status_id', 2)->count();
            return $activeAdmins > 1;
        }
        
        return true;
    }

    /**
     * Vérifier si un utilisateur peut être suspendu
     */
    private function canUserBeSuspended(User $user): bool
    {
        // Ne pas suspendre soi-même
        if ($user->id === Auth::id()) {
            return false;
        }
        
        // Ne pas suspendre le dernier admin actif
        if ($user->isAdmin() && $user->isActive()) {
            $activeAdmins = User::where('user_type_id', 1)->where('status_id', 2)->count();
            return $activeAdmins > 1;
        }
        
        return true;
    }

    /**
     * Générer un mot de passe sécurisé
     */
    private function generateSecurePassword($length = 6): string
{
    $voyelles = 'aeiou';
    $consonnes = 'bcdfghjklmnpqrstvwxz';
    $password = '';
    
    // Consonne-Voyelle-Consonne + 3 chiffres
    $password .= $consonnes[rand(0, strlen($consonnes) - 1)];
    $password .= $voyelles[rand(0, strlen($voyelles) - 1)];
    $password .= $consonnes[rand(0, strlen($consonnes) - 1)];
    $password .= rand(100, 999);
    
    return $password;
}

    /**
     * Valider la force d'un mot de passe
     */
    public function validatePasswordStrength($password): array
    {
        $score = 0;
        $feedback = [];

        // Critères de validation
        if (strlen($password) >= 8) $score += 1;
        else $feedback[] = 'Au moins 8 caractères';

        if (preg_match('/[a-z]/', $password)) $score += 1;
        else $feedback[] = 'Lettres minuscules';

        if (preg_match('/[A-Z]/', $password)) $score += 1;
        else $feedback[] = 'Lettres majuscules';

        if (preg_match('/[0-9]/', $password)) $score += 1;
        else $feedback[] = 'Chiffres';

        if (preg_match('/[^a-zA-Z0-9]/', $password)) $score += 1;
        else $feedback[] = 'Caractères spéciaux';

        // Déterminer le niveau
        if ($score < 3) {
            $level = 'weak';
            $color = 'danger';
        } elseif ($score < 5) {
            $level = 'medium';
            $color = 'warning';
        } else {
            $level = 'strong';
            $color = 'success';
        }

        return [
            'score' => $score,
            'level' => $level,
            'color' => $color,
            'feedback' => $feedback,
            'is_valid' => $score >= 4
        ];
    }
}