<?php
// app/Http/Controllers/PasswordManagementController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\User;

class PasswordManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Changer le mot de passe par l'utilisateur lui-même
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'current_password.required' => 'Le mot de passe actuel est obligatoire.',
            'new_password.required' => 'Le nouveau mot de passe est obligatoire.',
            'new_password.min' => 'Le nouveau mot de passe doit contenir au moins 8 caractères.',
            'new_password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreurs de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = Auth::user();

        // Vérifier le mot de passe actuel
        if (!Hash::check($request->current_password, $user->password)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le mot de passe actuel est incorrect.'
                ], 400);
            }

            return redirect()->back()
                ->with('error', 'Le mot de passe actuel est incorrect.')
                ->withInput();
        }

        // Vérifier que le nouveau mot de passe est différent
        if (Hash::check($request->new_password, $user->password)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le nouveau mot de passe doit être différent de l\'actuel.'
                ], 400);
            }

            return redirect()->back()
                ->with('error', 'Le nouveau mot de passe doit être différent de l\'actuel.');
        }

        try {
            // Mettre à jour le mot de passe
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            // Log de l'activité (optionnel)
            \Log::info('Password changed', [
                'user_id' => $user->id,
                'username' => $user->username,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Mot de passe modifié avec succès !'
                ]);
            }

            return redirect()->back()
                ->with('success', 'Votre mot de passe a été modifié avec succès !');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la modification du mot de passe.'
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Erreur lors de la modification du mot de passe.');
        }
    }

    /**
     * Afficher le formulaire de réinitialisation avec token
     */
    public function showResetForm($token, $userId)
{
    // Vérifier que le token existe et est valide
    $cachedToken = \Cache::get("password_reset_{$userId}");
    
    if (!$cachedToken || $cachedToken !== $token) {
        return redirect()->route('login')
            ->with('error', 'Lien de réinitialisation invalide ou expiré.');
    }

    $user = User::find($userId);
    if (!$user) {
        return redirect()->route('login')
            ->with('error', 'Utilisateur introuvable.');
    }

    // VOTRE VUE reset-password.blade.php sera utilisée ici
    return view('auth.reset-password', compact('token', 'user'));
}

/**
 * Réinitialiser le mot de passe avec token
 * COMPATIBLE AVEC VOTRE FORMULAIRE
 */
public function resetPassword(Request $request)
{
    $validator = Validator::make($request->all(), [
        'token' => 'required',
        'user_id' => 'required|exists:users,id',
        'password' => 'required|string|min:8|confirmed',
    ], [
        'token.required' => 'Token de réinitialisation manquant.',
        'user_id.required' => 'Identifiant utilisateur manquant.',
        'user_id.exists' => 'Utilisateur introuvable.',
        'password.required' => 'Le nouveau mot de passe est obligatoire.',
        'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
    ]);

    if ($validator->fails()) {
        return redirect()->back()
            ->withErrors($validator)
            ->withInput()
            ->with('error', 'Veuillez corriger les erreurs dans le formulaire.');
    }

    // Vérifier le token
    $cachedToken = \Cache::get("password_reset_{$request->user_id}");
    
    if (!$cachedToken || $cachedToken !== $request->token) {
        return redirect()->route('login')
            ->with('error', 'Lien de réinitialisation invalide ou expiré.');
    }

    try {
        $user = User::findOrFail($request->user_id);
        
        // Valider la force du mot de passe (compatible avec votre JavaScript)
        $passwordStrength = $this->validatePasswordStrength($request->password);
        
        if (!$passwordStrength['is_valid']) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Le mot de passe ne respecte pas les critères de sécurité requis.');
        }
        
        // Mettre à jour le mot de passe
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Supprimer le token utilisé
        \Cache::forget("password_reset_{$request->user_id}");

        // Log de l'activité
        \Log::info('Password reset completed', [
            'user_id' => $user->id,
            'username' => $user->username,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->route('login')
            ->with('success', 'Votre mot de passe a été réinitialisé avec succès ! Vous pouvez maintenant vous connecter avec votre nouveau mot de passe.');

    } catch (\Exception $e) {
        \Log::error('Password reset error', [
            'user_id' => $request->user_id,
            'error' => $e->getMessage()
        ]);
        
        return redirect()->back()
            ->withInput()
            ->with('error', 'Erreur lors de la réinitialisation du mot de passe. Veuillez réessayer.');
    }
}

/**
 * Valider la force du mot de passe (COMPATIBLE avec votre JavaScript)
 */
private function validatePasswordStrength($password): array
{
    $score = 0;
    $feedback = [];

    // Critères identiques à votre JavaScript
    if (strlen($password) >= 8) $score += 1;
    else $feedback[] = 'Au moins 8 caractères';

    if (strlen($password) >= 12) $score += 1;

    if (preg_match('/[a-z]/', $password)) $score += 1;
    else $feedback[] = 'Lettres minuscules';

    if (preg_match('/[A-Z]/', $password)) $score += 1;
    else $feedback[] = 'Lettres majuscules';

    if (preg_match('/[0-9]/', $password)) $score += 1;
    else $feedback[] = 'Chiffres';

    if (preg_match('/[^a-zA-Z0-9]/', $password)) $score += 1;
    else $feedback[] = 'Caractères spéciaux';

    // Logique identique à votre JavaScript
    if ($score < 3) {
        $level = 'weak';
        $color = 'danger';
        $text = 'Faible';
    } elseif ($score < 5) {
        $level = 'medium';
        $color = 'warning';
        $text = 'Moyen';
    } elseif ($score < 6) {
        $level = 'strong';
        $color = 'info';
        $text = 'Fort';
    } else {
        $level = 'very_strong';
        $color = 'success';
        $text = 'Très fort';
    }

    return [
        'score' => $score,
        'level' => $level,
        'color' => $color,
        'text' => $text,
        'feedback' => $feedback,
        'is_valid' => $score >= 4 // Même critère que votre JavaScript
    ];
}