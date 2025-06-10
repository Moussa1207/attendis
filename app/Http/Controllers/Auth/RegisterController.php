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
            'company' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'username.unique' => 'Ce nom d\'utilisateur est déjà pris.',
            'company.required' => 'Le nom de l\'entreprise est obligatoire.',
            'company.max' => 'Le nom de l\'entreprise ne peut pas dépasser 255 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);

        // AMÉLIORATION 1 : Les admins sont TOUJOURS actifs à l'inscription (plus d'attente)
        $isFirstUser = User::count() === 0;
        $userTypeId = 1; // 1 = Admin TOUJOURS
        $statusId = 2; // TOUJOURS Actif pour les admins (plus d'attente d'activation)
       
        try {
            // Créer l'utilisateur: administrateur ACTIF IMMÉDIATEMENT
            $user = User::create([
                'email' => $request->email,
                'username' => $request->username,
                'mobile_number' => $request->mobile_number,
                'company' => $request->company,
                'password' => Hash::make($request->password),
                'user_type_id' => $userTypeId, // TOUJOURS Admin
                'status_id' => $statusId, // TOUJOURS Actif (AMÉLIORATION)
            ]);

            // Message personnalisé selon si c'est le premier admin ou non
            if ($isFirstUser) {
                $message = 'Premier administrateur créé avec succès ! Vous pouvez maintenant vous connecter.';
               
                \Log::info('First administrator created', [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'company' => $user->company,
                    'ip' => $request->ip()
                ]);
            } else {
                // AMÉLIORATION : Message clair pour connexion immédiate
                $message = 'Inscription d\'administrateur réussie ! Vous pouvez maintenant vous connecter directement (plus d\'attente d\'activation).';
               
                \Log::info('New administrator registered and activated immediately', [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'company' => $user->company,
                    'ip' => $request->ip(),
                    'status' => 'active_immediately' // AMÉLIORATION
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