<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
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
        
        return view('user.user-create', compact('adminStats'));
    }

    /**
     * Créer un nouvel utilisateur avec gestion sécurisée des mots de passe
     * L'admin voit TOUJOURS le mot de passe temporaire
     * AMÉLIORATION 3 : SUPPRESSION du champ company
     */
    public function store(Request $request)
    {
        $request->validate([
    'email' => 'required|string|email|max:255|unique:users',
    'username' => 'required|string|max:255|unique:users',
    'mobile_number' => 'required|string|max:20',
    'user_role' => 'required|string|in:ecran,conseiller,accueil', // NOUVEAU
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

            // Générer un mot de passe temporaire sécurisé
            $temporaryPassword = $this->generateSecurePassword();
            
            // Créer l'utilisateur - TOUJOURS utilisateur normal par défaut (SANS champ company)
            $user = User::create([
                'email' => $request->email,
                'username' => $request->username,
                'mobile_number' => $request->mobile_number,
                // AMÉLIORATION 3 : SUPPRESSION 'company' => $request->company,
                'password' => Hash::make($temporaryPassword),
                'user_type_id' => 2, // TOUJOURS utilisateur normal 
                'status_id' => 2, // TOUJOURS actif par défaut 
            ]);

            // Enregistrer la relation administrator_user avec informations détaillées
            $adminUserRecord = AdministratorUser::create([
    'administrator_id' => Auth::id(),
    'user_id' => $user->id,
    'creation_method' => 'manual',
    'creation_notes' => $this->formatUserRoleNote($request->user_role), // NOUVEAU
    'password_reset_required' => true,
    'password_reset_sent_at' => null
]);

            // Préparer les informations de connexion (SANS company)
            $userCredentials = [
                'email' => $user->email,
                'username' => $user->username,
                // AMÉLIORATION 3 : SUPPRESSION 'company' => $user->company,
                'password' => $temporaryPassword,
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

            // Log de l'action (SANS company)
            \Log::info("Utilisateur {$user->username} créé par " . Auth::user()->username, [
                'user_id' => $user->id,
                'admin_id' => Auth::id(),
                // AMÉLIORATION 3 : SUPPRESSION 'company' => $user->company,
                'credentials_sent' => $credentialsSent
            ]);

            // Message avec mot de passe TOUJOURS visible pour l'admin
            $message = "L'utilisateur {$user->username} a été créé avec succès.";
            if ($credentialsSent) {
                $message .= " Les identifiants ont été envoyés par email ET sont affichés ci-dessous.";
            } else {
                $message .= " Voici les identifiants à communiquer à l'utilisateur :";
            }

            // Réponse AJAX (SANS company)
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email,
                        // AMÉLIORATION 3 : SUPPRESSION 'company' => $user->company,
                        'type' => $user->getTypeName(),
                        'status' => 'Actif',
                        'credentials_sent' => $credentialsSent,
                        // L'ADMIN VOIT TOUJOURS LE MOT DE PASSE
                        'temporary_password' => $temporaryPassword,
                        'login_url' => route('login')
                    ],
                    'credentials' => $userCredentials
                ]);
            }

            $adminStats = AdministratorUser::getStatsForAdmin(Auth::id());

            return view('user.user-create', [
               'adminStats' => $adminStats,
               'newUser' => $user,
               'temporaryPassword' => $temporaryPassword
            ])->with('success', "Utilisateur '{$user->username}' créé avec succès ! ✅");
       
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error("Erreur création utilisateur: " . $e->getMessage(), [
                'email' => $request->email,
                'username' => $request->username,
                // AMÉLIORATION 3 : SUPPRESSION 'company' => $request->company,
                'admin_id' => Auth::id()
            ]);
            
            $errorMessage = 'Erreur lors de la création de l\'utilisateur. Veuillez réessayer.';
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
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
                            'status' => $user->getStatusName(),
                            'status_badge_class' => $user->getStatusBadgeColor(),
                            'created_at' => $user->created_at->format('d/m/Y H:i'),
                            'is_active' => $user->isActive(),
                            'requires_password_reset' => $relation->requiresPasswordReset(),
                            'creation_method' => $relation->creation_method ?? 'manual',
                            'creation_notes' => $relation->creation_notes
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
            
            // Statistiques supplémentaires
            $recentActivity = AdministratorUser::createdBy($adminId)
                ->with(['user'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function($record) {
                    return [
                        'user' => $record->user ? $record->user->username : 'Utilisateur supprimé',
                        // AMÉLIORATION 3 : SUPPRESSION 'company' => $record->user ? $record->user->company : 'N/A',
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

            // Préparer les nouvelles informations (SANS company)
            $userCredentials = [
                'email' => $user->email,
                'username' => $user->username,
                // AMÉLIORATION 3 : SUPPRESSION 'company' => $user->company,
                'password' => $newPassword,
                'login_url' => route('login'),
                'admin_creator' => Auth::user()->username
            ];

            if ($adminUserRecord) {
                $adminUserRecord->markPasswordResetSent();
            }

            \Log::info("Nouveaux identifiants générés pour {$user->username} par " . Auth::user()->username);

            //RETOURNER LE NOUVEAU MOT DE PASSE À L'ADMIN
            return response()->json([
                'success' => true,
                'message' => "Nouveaux identifiants générés pour {$user->username}",
                'credentials' => $userCredentials,
                'new_password' => $newPassword
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
     * Générer un mot de passe temporaire simple (format amélioré)
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

    //MÉTHODE pour formater la note à partir du rôle
private function formatUserRoleNote($role): string
{
    $roleLabels = [
        'ecran' => 'Poste Ecran - Interface utilisateur',
        'conseiller' => 'Poste Conseiller - Support client',
        'accueil' => 'Poste Accueil - Réception et orientation'
    ];
    
    return $roleLabels[$role] ?? "Poste : {$role}";
}

}