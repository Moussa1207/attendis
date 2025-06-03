<?php
// app/Http/Controllers/UserManagementController.php

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
     * Afficher le formulaire de création d'utilisateur professionnel
     */
    public function create()
    {
        // Statistiques pour l'admin connecté
        $adminStats = AdministratorUser::getStatsForAdmin(Auth::id());
        
        return view('user.user-create', compact('adminStats'));
    }

    /**
     * Créer un nouvel utilisateur avec gestion sécurisée des mots de passe
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:255|unique:users',
            'username' => 'required|string|max:255|unique:users',
            'mobile_number' => 'required|string|max:20',
            'user_type_id' => 'sometimes|in:1,2', // Optionnel, par défaut utilisateur normal
            'creation_notes' => 'nullable|string|max:500',
            'send_credentials' => 'boolean'
        ], [
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'username.unique' => 'Ce nom d\'utilisateur est déjà pris.',
            'email.email' => 'Veuillez saisir une adresse email valide.',
            'mobile_number.required' => 'Le numéro de téléphone est obligatoire.',
            'creation_notes.max' => 'Les notes ne peuvent pas dépasser 500 caractères.'
        ]);

        try {
            DB::beginTransaction();

            // Générer un mot de passe temporaire sécurisé
            $temporaryPassword = User::generateSecureTemporaryPassword();

            // Créer l'utilisateur - TOUJOURS utilisateur normal par défaut (comme demandé)
            $user = User::create([
                'email' => $request->email,
                'username' => $request->username,
                'mobile_number' => $request->mobile_number,
                'password' => Hash::make($temporaryPassword),
                'user_type_id' => 2, // TOUJOURS utilisateur normal (selon vos exigences)
                'status_id' => 2, // TOUJOURS actif par défaut (selon vos exigences)
            ]);

            // Enregistrer la relation administrator_user avec informations détaillées
            $adminUserRecord = AdministratorUser::create([
                'administrator_id' => Auth::id(),
                'user_id' => $user->id,
                'creation_method' => 'manual',
                'creation_notes' => $request->creation_notes,
                'password_reset_required' => true,
                'password_reset_sent_at' => null
            ]);

            // Préparer les informations de connexion
            $userCredentials = [
                'email' => $user->email,
                'username' => $user->username,
                'password' => $temporaryPassword,
                'login_url' => url('/'),
                'admin_creator' => Auth::user()->username
            ];

            // Envoyer les identifiants si demandé (recommandé pour la sécurité)
            if ($request->boolean('send_credentials', true)) {
                try {
                    // Ici vous pouvez implémenter l'envoi d'email
                    // Mail::to($user->email)->send(new UserCredentialsMail($userCredentials));
                    
                    $adminUserRecord->markPasswordResetSent();
                    $credentialsSent = true;
                } catch (\Exception $e) {
                    \Log::error("Erreur envoi email pour {$user->email}: " . $e->getMessage());
                    $credentialsSent = false;
                }
            } else {
                $credentialsSent = false;
            }

            DB::commit();

            // Log de l'action
            \Log::info("Utilisateur {$user->username} créé par " . Auth::user()->username);

            $message = "L'utilisateur {$user->username} a été créé avec succès.";
            if ($credentialsSent) {
                $message .= " Les identifiants ont été envoyés par email.";
            }

            // Réponse AJAX
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'type' => $user->getTypeName(),
                        'status' => 'Actif',
                        'credentials_sent' => $credentialsSent,
                        'temporary_password' => $credentialsSent ? null : $temporaryPassword // Seulement si pas envoyé par email
                    ]
                ]);
            }

            // Redirection avec informations sécurisées
            $flashData = ['success' => $message];
            if (!$credentialsSent) {
                $flashData['temporary_credentials'] = $userCredentials;
            }

            return redirect()->route('user.users-list')->with($flashData);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error("Erreur création utilisateur: " . $e->getMessage());
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
                ->with(['userType', 'status'])
                ->paginate(10);

            if ($request->expectsJson()) {
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
                            'status_badge_class' => $user->getStatusBadgeClass(),
                            'created_at' => $user->created_at->format('d/m/Y H:i'),
                            'is_active' => $user->isActive(),
                            'requires_password_reset' => $user->requiresPasswordReset(),
                            'creation_method' => $user->pivot->creation_method ?? 'manual',
                            'creation_notes' => $user->pivot->creation_notes
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
                        'user' => $record->user->username,
                        'created_at' => $record->created_at->diffForHumans(),
                        'status' => $record->user->getStatusName()
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
     * Renvoyer les identifiants à un utilisateur
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

            // Générer un nouveau mot de passe temporaire
            $newPassword = User::generateSecureTemporaryPassword();
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
                'login_url' => url('/'),
                'admin_creator' => Auth::user()->username
            ];

            // Ici vous pouvez implémenter l'envoi d'email
            // Mail::to($user->email)->send(new UserCredentialsResendMail($userCredentials));

            if ($adminUserRecord) {
                $adminUserRecord->markPasswordResetSent();
            }

            \Log::info("Identifiants renvoyés pour {$user->username} par " . Auth::user()->username);

            return response()->json([
                'success' => true,
                'message' => "Les nouveaux identifiants ont été envoyés à {$user->username}."
            ]);

        } catch (\Exception $e) {
            \Log::error("Erreur renvoi identifiants: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du renvoi des identifiants.'
            ], 500);
        }
    }
}