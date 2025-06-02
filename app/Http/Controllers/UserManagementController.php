<?php
// app/Http/Controllers/UserManagementController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AdministratorUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Afficher le formulaire de création d'utilisateur
     */
    public function create()
    {
        return view('user.user-create');
    }

    /**
     * Créer un nouvel utilisateur
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:255|unique:users',
            'username' => 'required|string|max:255|unique:users',
            'mobile_number' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'user_type_id' => 'required|in:1,2', // 1=Admin, 2=User
        ], [
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'username.unique' => 'Ce nom d\'utilisateur est déjà pris.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'user_type_id.required' => 'Veuillez sélectionner un type d\'utilisateur.',
            'user_type_id.in' => 'Type d\'utilisateur invalide.',
        ]);

        try {
            DB::beginTransaction();

            // Créer l'utilisateur avec le statut actif par défaut
            $user = User::create([
                'email' => $request->email,
                'username' => $request->username,
                'mobile_number' => $request->mobile_number,
                'password' => Hash::make($request->password),
                'user_type_id' => $request->user_type_id,
                'status_id' => 2, // 2 = Actif par défaut
            ]);

            // Enregistrer la relation administrator_user
            AdministratorUser::create([
                'administrator_id' => Auth::id(),
                'user_id' => $user->id,
            ]);

            DB::commit();

            $roleText = $request->user_type_id == 1 ? 'administrateur' : 'utilisateur';
            $message = "L'utilisateur {$user->username} a été créé avec succès en tant que {$roleText}.";

            // Réponse AJAX
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'type' => $roleText,
                        'status' => 'Actif'
                    ]
                ]);
            }

            // Redirection classique
            return redirect()->route('user.users-list')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            
            $errorMessage = 'Erreur lors de la création de l\'utilisateur : ' . $e->getMessage();
            
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
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        try {
            $users = Auth::user()->createdUsers()
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
                        'created_at' => $user->created_at->format('d/m/Y H:i')
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des utilisateurs'
            ], 500);
        }
    }

    /**
     * Statistiques des utilisateurs créés par l'admin
     */
    public function getMyUserStats(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        try {
            $adminId = Auth::id();
            
            $stats = [
                'total_created' => AdministratorUser::where('administrator_id', $adminId)->count(),
                'active_created' => AdministratorUser::where('administrator_id', $adminId)
                    ->whereHas('user', function($q) {
                        $q->where('status_id', 2);
                    })->count(),
                'admin_created' => AdministratorUser::where('administrator_id', $adminId)
                    ->whereHas('user', function($q) {
                        $q->where('user_type_id', 1);
                    })->count(),
                'user_created' => AdministratorUser::where('administrator_id', $adminId)
                    ->whereHas('user', function($q) {
                        $q->where('user_type_id', 2);
                    })->count(),
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
}