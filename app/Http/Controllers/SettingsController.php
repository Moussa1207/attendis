<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Afficher la page des paramètres généraux
     * Route: GET /layouts/setting/general
     */
    public function index(): View
    {
        try {
            // Récupérer les paramètres de gestion des utilisateurs pour la vue
            $userManagementSettings = Setting::getGroupFormatted('user_management');
            
            // S'assurer que tous les paramètres attendus par la vue existent
            $expectedSettings = [
                Setting::AUTO_DETECT_ADVISORS,
                Setting::AUTO_ASSIGN_SERVICES,
                Setting::ENABLE_SESSION_CLOSURE,
                Setting::SESSION_CLOSURE_TIME
            ];
            
            foreach ($expectedSettings as $key) {
                if (!isset($userManagementSettings[$key])) {
                    // Créer le paramètre manquant avec une valeur par défaut
                    $this->createDefaultSetting($key);
                }
            }
            
            // Recharger les paramètres après création des manquants
            $userManagementSettings = Setting::getGroupFormatted('user_management');
            
            // Log pour debug
            Log::info('Settings page loaded', [
                'admin_id' => Auth::id(),
                'settings_loaded' => array_keys($userManagementSettings),
                'settings_values' => collect($userManagementSettings)->map(function($setting) {
                    return [
                        'value' => $setting->value ?? null,
                        'formatted_value' => $setting->formatted_value ?? null,
                        'type' => $setting->type ?? null
                    ];
                })->toArray()
            ]);
            
            return view('layouts.setting', compact('userManagementSettings'));
            
        } catch (\Exception $e) {
            Log::error('Settings index error', [
                'admin_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('layouts.app')
                ->with('error', 'Erreur lors du chargement des paramètres.');
        }
    }

    /**
     * Mettre à jour les paramètres généraux
     * Route: PUT /layouts/setting/general
     */
    public function update(Request $request): RedirectResponse
    {
        // Log des données reçues pour debug
        Log::info('Settings update request received', [
            'admin_id' => Auth::id(),
            'request_data' => $request->all(),
            'request_method' => $request->method(),
            'content_type' => $request->header('content-type')
        ]);

        // Validation des données (adaptation pour les strings '0'/'1' des checkboxes)
        $validator = Validator::make($request->all(), [
            'auto_detect_available_advisors' => 'required|in:0,1',
            'auto_assign_all_services_to_advisors' => 'required|in:0,1', 
            'enable_auto_session_closure' => 'required|in:0,1',
            'auto_session_closure_time' => [
                'required_if:enable_auto_session_closure,1',
                'nullable',
                function ($attribute, $value, $fail) use ($request) {
                    // Si la fermeture auto est activée, l'heure est obligatoire
                    if ($request->input('enable_auto_session_closure') == '1' && empty($value)) {
                        $fail('L\'heure de fermeture est obligatoire quand la fermeture automatique est activée.');
                        return;
                    }
                    
                    // Valider le format si une valeur est fournie
                    if (!empty($value) && !preg_match('/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/', $value)) {
                        $fail('L\'heure doit être au format HH:MM (24h).');
                    }
                }
            ],
        ], [
            'auto_detect_available_advisors.required' => 'Le paramètre de détection automatique est obligatoire.',
            'auto_detect_available_advisors.in' => 'Valeur invalide pour la détection automatique.',
            'auto_assign_all_services_to_advisors.required' => 'Le paramètre d\'attribution automatique est obligatoire.',
            'auto_assign_all_services_to_advisors.in' => 'Valeur invalide pour l\'attribution automatique.',
            'enable_auto_session_closure.required' => 'Le paramètre de fermeture automatique est obligatoire.',
            'enable_auto_session_closure.in' => 'Valeur invalide pour la fermeture automatique.',
            'auto_session_closure_time.required_if' => 'L\'heure de fermeture est obligatoire quand la fermeture automatique est activée.',
        ]);

        if ($validator->fails()) {
            Log::warning('Settings validation failed', [
                'admin_id' => Auth::id(),
                'errors' => $validator->errors()->toArray(),
                'input' => $request->all()
            ]);

            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Veuillez corriger les erreurs dans le formulaire.');
        }

        try {
            DB::beginTransaction();
            
            $updatedSettings = [];
            
            // Traitement de la détection automatique des conseillers
            $autoDetectAdvisors = $request->input('auto_detect_available_advisors') === '1';
            Setting::set(Setting::AUTO_DETECT_ADVISORS, $autoDetectAdvisors, 'boolean', 'user_management');
            $updatedSettings['Détection automatique des conseillers'] = $autoDetectAdvisors ? 'Activée' : 'Désactivée';

            // Traitement de l'attribution automatique des services
            $autoAssignServices = $request->input('auto_assign_all_services_to_advisors') === '1';
            Setting::set(Setting::AUTO_ASSIGN_SERVICES, $autoAssignServices, 'boolean', 'user_management');
            $updatedSettings['Attribution automatique des services'] = $autoAssignServices ? 'Activée' : 'Désactivée';

            // Traitement de la fermeture automatique des sessions
            $enableSessionClosure = $request->input('enable_auto_session_closure') === '1';
            Setting::set(Setting::ENABLE_SESSION_CLOSURE, $enableSessionClosure, 'boolean', 'user_management');
            $updatedSettings['Fermeture automatique des sessions'] = $enableSessionClosure ? 'Activée' : 'Désactivée';

            // Traitement de l'heure de fermeture
            if ($enableSessionClosure && $request->filled('auto_session_closure_time')) {
                $closureTime = $request->input('auto_session_closure_time');
                Setting::set(Setting::SESSION_CLOSURE_TIME, $closureTime, 'time', 'user_management');
                $updatedSettings['Heure de fermeture'] = $closureTime;
            } elseif (!$enableSessionClosure) {
                // Réinitialiser à la valeur par défaut si désactivé
                Setting::set(Setting::SESSION_CLOSURE_TIME, '18:00', 'time', 'user_management');
                $updatedSettings['Heure de fermeture'] = 'Réinitialisée (18:00)';
            }

            DB::commit();

            // Vider le cache des paramètres après mise à jour
            Setting::clearCache();

            // Log de l'action
            Log::info('Settings updated successfully by admin', [
                'admin_id' => Auth::id(),
                'admin_username' => Auth::user()->username,
                'updated_settings' => $updatedSettings,
                'raw_input' => $request->only([
                    'auto_detect_available_advisors',
                    'auto_assign_all_services_to_advisors', 
                    'enable_auto_session_closure',
                    'auto_session_closure_time'
                ]),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Message de succès détaillé
            $message = 'Paramètres mis à jour avec succès : ' . implode(', ', array_map(
                fn($key, $value) => "{$key}: {$value}",
                array_keys($updatedSettings),
                array_values($updatedSettings)
            ));

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Settings update error', [
                'admin_id' => Auth::id(),
                'error' => $e->getMessage(),
                'input' => $request->except(['_token', '_method']),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour des paramètres. Veuillez réessayer.');
        }
    }

    /**
     * Réinitialiser tous les paramètres aux valeurs par défaut
     * Route: POST /layouts/setting/reset
     */
    public function reset(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            
            $success = Setting::resetToDefaults();
            
            if ($success) {
                DB::commit();
                
                // Vider le cache après reset
                Setting::clearCache();
                
                Log::info('Settings reset to defaults', [
                    'admin_id' => Auth::id(),
                    'admin_username' => Auth::user()->username,
                    'ip' => $request->ip()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Tous les paramètres ont été réinitialisés aux valeurs par défaut.'
                ]);
            } else {
                DB::rollBack();
                
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la réinitialisation des paramètres.'
                ], 500);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Settings reset error', [
                'admin_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la réinitialisation : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vider le cache des paramètres
     * Route: POST /setting/clear-cache
     */
    public function clearCache(Request $request): JsonResponse
    {
        try {
            // Vider le cache spécifique aux paramètres
            Setting::clearCache();
            
            // Vider également le cache général si nécessaire
            Cache::flush();
            
            // Optionnel : Vider les autres caches Laravel
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');

            Log::info('Cache cleared by admin', [
                'admin_id' => Auth::id(),
                'admin_username' => Auth::user()->username,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cache vidé avec succès. Les paramètres ont été rechargés.'
            ]);

        } catch (\Exception $e) {
            Log::error('Cache clear error', [
                'admin_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du vidage du cache : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API pour obtenir les paramètres d'un groupe (AJAX)
     */
    public function getGroupSettings(Request $request, string $group): JsonResponse
    {
        try {
            $settings = Setting::getGroupFormatted($group);
            
            return response()->json([
                'success' => true,
                'settings' => $settings,
                'group' => $group
            ]);

        } catch (\Exception $e) {
            Log::error('Get group settings error', [
                'admin_id' => Auth::id(),
                'group' => $group,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des paramètres'
            ], 500);
        }
    }

    /**
     * API pour mettre à jour un paramètre spécifique (AJAX)
     */
    public function updateSetting(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string',
            'value' => 'required',
            'type' => 'sometimes|string|in:string,boolean,integer,float,json,array,time'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();
            
            $key = $request->key;
            $value = $request->value;
            $type = $request->type ?? 'string';

            // Validation de la valeur
            $validation = Setting::validateValue($key, $value);
            if (!$validation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $validation['message']
                ], 422);
            }

            // Mise à jour du paramètre
            $success = Setting::set($key, $value, $type);

            if ($success) {
                DB::commit();
                
                // Vider le cache après mise à jour
                Setting::clearCache();
                
                Log::info('Individual setting updated', [
                    'admin_id' => Auth::id(),
                    'key' => $key,
                    'value' => $value,
                    'type' => $type
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Paramètre mis à jour avec succès',
                    'setting' => [
                        'key' => $key,
                        'value' => $value,
                        'formatted_value' => Setting::get($key)
                    ]
                ]);
            } else {
                DB::rollBack();
                
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la mise à jour'
                ], 500);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Setting update error', [
                'admin_id' => Auth::id(),
                'error' => $e->getMessage(),
                'input' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du paramètre'
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques des paramètres (pour le dashboard)
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = [
                'total_settings' => Setting::count(),
                'active_settings' => Setting::active()->count(),
                'groups_count' => Setting::distinct('group')->count(),
                'last_updated' => Setting::latest('updated_at')->first()?->updated_at?->format('d/m/Y H:i'),
                'by_groups' => Setting::selectRaw('`group`, COUNT(*) as count')
                                    ->groupBy('group')
                                    ->pluck('count', 'group')
                                    ->toArray(),
                'by_types' => Setting::selectRaw('type, COUNT(*) as count')
                                   ->groupBy('type')
                                   ->pluck('count', 'type')
                                   ->toArray(),
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Get settings stats error', [
                'admin_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques'
            ], 500);
        }
    }

    /**
     * Debug method - Obtenir l'état actuel des paramètres
     * Route: GET /layouts/setting/debug (à supprimer en production)
     */
    public function debug(): JsonResponse
    {
        if (!app()->environment(['local', 'staging'])) {
            abort(404);
        }

        try {
            $settings = Setting::where('group', 'user_management')->get();
            $formattedSettings = Setting::getGroupFormatted('user_management');
            
            return response()->json([
                'success' => true,
                'raw_settings' => $settings->toArray(),
                'formatted_settings' => $formattedSettings,
                'cache_status' => [
                    'has_cache' => Cache::has('settings_user_management'),
                    'cache_value' => Cache::get('settings_user_management')
                ],
                'environment' => app()->environment(),
                'current_user' => [
                    'id' => Auth::id(),
                    'username' => Auth::user()->username
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    // ===============================================
    // MÉTHODES PRIVÉES UTILITAIRES
    // ===============================================

    /**
     * Créer un paramètre par défaut s'il n'existe pas
     */
    private function createDefaultSetting(string $key): void
    {
        $defaults = [
            Setting::AUTO_DETECT_ADVISORS => [
                'value' => true,
                'type' => 'boolean',
                'group' => 'user_management',
                'label' => 'Détection automatique des conseillers',
                'description' => 'Détecter automatiquement les conseillers disponibles lors de leur connexion',
                'sort_order' => 1
            ],
            Setting::AUTO_ASSIGN_SERVICES => [
                'value' => true,
                'type' => 'boolean',
                'group' => 'user_management',
                'label' => 'Attribution automatique des services',
                'description' => 'Attribuer automatiquement tous les services à tous les conseillers',
                'sort_order' => 2
            ],
            Setting::ENABLE_SESSION_CLOSURE => [
                'value' => false,
                'type' => 'boolean',
                'group' => 'user_management',
                'label' => 'Fermeture automatique des sessions',
                'description' => 'Fermer automatiquement les sessions à une heure définie',
                'sort_order' => 3
            ],
            Setting::SESSION_CLOSURE_TIME => [
                'value' => '18:00',
                'type' => 'time',
                'group' => 'user_management',
                'label' => 'Heure de fermeture',
                'description' => 'Heure à laquelle fermer automatiquement les sessions',
                'sort_order' => 4
            ]
        ];

        if (isset($defaults[$key])) {
            try {
                $default = $defaults[$key];
                Setting::create([
                    'key' => $key,
                    'value' => Setting::prepareValue($default['value'], $default['type']),
                    'type' => $default['type'],
                    'group' => $default['group'],
                    'label' => $default['label'],
                    'description' => $default['description'],
                    'sort_order' => $default['sort_order'],
                    'is_active' => true
                ]);

                Log::info('Default setting created', [
                    'key' => $key,
                    'default_value' => $default['value']
                ]);

            } catch (\Exception $e) {
                Log::error('Error creating default setting', [
                    'key' => $key,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Valider les permissions pour l'accès aux paramètres
     */
    private function checkPermissions(): bool
    {
        $user = Auth::user();
        
        if (!$user || !$user->isAdmin()) {
            Log::warning('Unauthorized settings access attempt', [
                'user_id' => $user?->id,
                'ip' => request()->ip()
            ]);
            return false;
        }

        // Ajouter ici d'autres vérifications de permissions si nécessaire
        
        return true;
    }

    /**
     * Convertir les valeurs de checkbox (0/1) en boolean
     */
    private function convertCheckboxValue(string $value): bool
    {
        return $value === '1';
    }

    /**
     * Valider une heure au format HH:MM
     */
    private function validateTimeFormat(string $time): bool
    {
        return preg_match('/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/', $time);
    }
}