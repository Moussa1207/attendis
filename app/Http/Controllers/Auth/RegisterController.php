<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.login');
    }

    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:255|unique:users',
            'username' => 'required|string|max:255|unique:users',
            'mobile_number' => 'required|string|max:20',
            'company' => 'required|string|max:255', // NOUVEAU CHAMP
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'username.unique' => 'Ce nom d\'utilisateur est déjà pris.',
            'company.required' => 'Le nom de l\'entreprise est obligatoire.', // NOUVEAU
            'company.max' => 'Le nom de l\'entreprise ne peut pas dépasser 255 caractères.', // NOUVEAU
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);

        // Inscription réservée aux admins uniquement
        $isFirstUser = User::count() === 0;
       
        // role du nouvel inscrit
        $userTypeId = 1; // 1 = Admin TOUJOURS
       
        // Statut selon si c'est le premier utilisateur ou non
        $statusId = $isFirstUser ? 2 : 1; // Premier = Actif, autres = En attente
       
        try {
            // Créer l'utilisateur: administrateur
            $user = User::create([
                'email' => $request->email,
                'username' => $request->username,
                'mobile_number' => $request->mobile_number,
                'company' => $request->company, // NOUVEAU CHAMP
                'password' => Hash::make($request->password),
                'user_type_id' => $userTypeId, // TOUJOURS Admin
                'status_id' => $statusId,
            ]);

            // Message personnalisé selon le statut
            if ($isFirstUser) {
                $message = 'Premier administrateur créé avec succès ! Vous pouvez maintenant vous connecter.';
                
                // Log de création du premier admin
                \Log::info('First administrator created', [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'company' => $user->company,
                    'ip' => $request->ip()
                ]);
            } else {
                $message = 'Demande d\'inscription envoyée. En attente d\'activation par un administrateur.';
                
                // Log de demande d'inscription
                \Log::info('Admin registration request submitted', [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'company' => $user->company,
                    'ip' => $request->ip(),
                    'status' => 'pending_activation'
                ]);
            }

            return redirect()->route('login')->with('success', $message);
            
        } catch (\Exception $e) {
            \Log::error('Registration error', [
                'email' => $request->email,
                'username' => $request->username,
                'company' => $request->company,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                ->with('error', 'Erreur lors de l\'inscription. Veuillez réessayer.')
                ->withInput($request->except('password', 'password_confirmation'));
        }
    }
}