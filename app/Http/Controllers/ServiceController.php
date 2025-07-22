<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Exception;

class ServiceController extends Controller
{
    /**
     * ✅ CORRIGÉ : Afficher SEULEMENT les services créés par l'admin connecté
     */
    public function index(Request $request): View
    {
        try {
            // 🔒 ISOLATION : Filtrer par admin connecté
            $query = Service::where('created_by', Auth::id())->with('creator');

            // Recherche (sur ses propres services) - MISE À JOUR pour letter_of_service
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nom', 'LIKE', "%{$search}%")
                      ->orWhere('letter_of_service', 'LIKE', "%{$search}%")
                      ->orWhere('description', 'LIKE', "%{$search}%");
                });
            }

            // Filtrage par statut (sur ses propres services)
            if ($request->filled('statut')) {
                $query->where('statut', $request->statut);
            }

            // Filtre par période récente
            if ($request->filled('recent')) {
                $days = (int) $request->recent;
                $query->where('created_at', '>=', now()->subDays($days));
            }

            // Tri
            $sortBy = $request->get('sort', 'created_at');
            $sortOrder = $request->get('order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $services = $query->paginate(15)->appends($request->query());

            return view('service.service-list', compact('services'));

        } catch (Exception $e) {
            Log::error('Erreur lors de la récupération des services: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);
            
            return redirect()->back()->with('error', 'Erreur lors du chargement des services.');
        }
    }

    /**
     * Formulaire de création
     */
    public function create(): View
    {
        return view('service.service-create');
    }

    /**
     * ✅ MISE À JOUR : Créer un service avec letter_of_service
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            // Validation avec nouvelles règles pour letter_of_service
            $validator = Validator::make($request->all(), [
                'nom' => 'required|string|max:255',
                'letter_of_service' => [
                    'required',
                    'string',
                    'max:5',
                    function ($attribute, $value, $fail) {
                        // Vérifier l'unicité dans les services de l'admin connecté
                        if (Service::where('created_by', Auth::id())
                                  ->where('letter_of_service', strtoupper(trim($value)))
                                  ->exists()) {
                            $fail('Cette lettre de service est déjà utilisée dans vos services.');
                        }
                    }
                ],
                'statut' => 'required|in:actif,inactif',
                'description' => 'nullable|string|max:1000',
            ], [
                'nom.required' => 'Le nom du service est obligatoire.',
                'nom.max' => 'Le nom ne peut pas dépasser 255 caractères.',
                'letter_of_service.required' => 'La lettre de service est obligatoire.',
                'letter_of_service.max' => 'La lettre de service ne peut pas dépasser 5 caractères.',
                'statut.required' => 'Le statut est obligatoire.',
                'statut.in' => 'Le statut doit être "actif" ou "inactif".',
                'description.max' => 'La description ne peut pas dépasser 1000 caractères.',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Normaliser la lettre de service
            $letterOfService = strtoupper(trim($request->letter_of_service));

            // Vérification finale de l'unicité dans les services de l'admin
            if (Service::where('created_by', Auth::id())
                      ->where('letter_of_service', $letterOfService)
                      ->exists()) {
                return redirect()->back()
                    ->withErrors(['letter_of_service' => 'Cette lettre de service est déjà utilisée dans vos services.'])
                    ->withInput();
            }

            // Création du service
            $service = Service::create([
                'nom' => trim($request->nom),
                'letter_of_service' => $letterOfService,
                'statut' => $request->statut,
                'description' => $request->description ? trim($request->description) : null,
                'created_by' => Auth::id(),
            ]);

            Log::info('Service créé avec succès', [
                'service_id' => $service->id,
                'service_name' => $service->nom,
                'letter_of_service' => $service->letter_of_service,
                'created_by' => Auth::user()->username,
            ]);

            return redirect()->route('service.service-list')
                ->with('success', "✅ Service '{$service->nom}' créé avec succès !\nLettre attribuée : {$letterOfService}");

        } catch (Exception $e) {
            Log::error('Erreur lors de la création du service: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'user_id' => Auth::id(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la création du service. Veuillez réessayer.');
        }
    }

    /**
     * ✅ CORRIGÉ : Vérifier l'autorisation pour voir
     */
    public function show(Service $service): JsonResponse
    {
        if ($service->created_by !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas voir ce service.'
            ], 403);
        }

        try {
            $service->load('creator');
            
            return response()->json([
                'success' => true,
                'service' => $service->toApiArray()
            ]);
        } catch (Exception $e) {
            Log::error('Erreur lors de la récupération des détails: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des détails.'
            ], 500);
        }
    }

    /**
     * ✅ CORRIGÉ : Vérifier l'autorisation pour éditer
     */
    

    

    /**
     * ✅ CORRIGÉ : Vérifier l'autorisation pour supprimer
     */
    public function destroy(Service $service): JsonResponse
    {
        if ($service->created_by !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas supprimer ce service.'
            ], 403);
        }

        try {
            if (!$service->canBeDeleted()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce service ne peut pas être supprimé car il est utilisé ailleurs.'
                ], 422);
            }

            $serviceName = $service->nom;
            $letterOfService = $service->letter_of_service;

            $service->delete();

            Log::warning('Service supprimé', [
                'service_name' => $serviceName,
                'letter_of_service' => $letterOfService,
                'deleted_by' => Auth::user()->username,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Service '{$serviceName}' (lettre: {$letterOfService}) supprimé avec succès !"
            ]);

        } catch (Exception $e) {
            Log::error('Erreur lors de la suppression du service: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du service.'
            ], 500);
        }
    }

    /**
     * ✅ NOUVELLE MÉTHODE : Vérifier la disponibilité d'une lettre de service (dans les services de l'admin)
     */
    public function checkLetterAvailability(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'letter' => 'required|string|max:5',
                'exclude_id' => 'sometimes|integer|exists:services,id'            ]);

            if ($validator->fails()) {
                return response()->json([
                    'available' => false,
                    'message' => 'Lettre invalide.'
                ], 422);
            }

            $letter = strtoupper(trim($request->letter));
            $excludeId = $request->exclude_id;

            // 🔒 SÉCURITÉ : Vérifier seulement dans les services de l'admin connecté
            $query = Service::where('created_by', Auth::id())
                           ->where('letter_of_service', $letter);
            
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }

            $available = !$query->exists();

            $response = [
                'available' => $available,
                'letter' => $letter
            ];

            if (!$available) {
                // Générer des suggestions alternatives pour cet admin
                $suggestions = $this->generateLetterSuggestions($letter, $excludeId);
                $response['suggestions'] = $suggestions;
                $response['message'] = "La lettre '{$letter}' est déjà utilisée dans vos services.";
            } else {
                $response['message'] = "La lettre '{$letter}' est disponible.";
            }

            return response()->json($response);

        } catch (Exception $e) {
            Log::error('Erreur lors de la vérification de disponibilité: ' . $e->getMessage());
            return response()->json([
                'available' => false,
                'message' => 'Erreur lors de la vérification.'
            ], 500);
        }
    }

    /**
     * ✅ MISE À JOUR : Générer des suggestions de lettres alternatives (pour l'admin connecté)
     */
    private function generateLetterSuggestions($baseLetter, $excludeId = null, $limit = 5): array
    {
        $suggestions = [];
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        
        // Commencer par essayer les lettres suivantes dans l'alphabet
        $baseIndex = strpos($alphabet, $baseLetter);
        
        if ($baseIndex !== false) {
            // Essayer les lettres suivantes
            for ($i = 1; $i < 26 && count($suggestions) < $limit; $i++) {
                $nextIndex = ($baseIndex + $i) % 26;
                $testLetter = $alphabet[$nextIndex];
                
                // 🔒 SÉCURITÉ : Vérifier seulement dans les services de l'admin
                $query = Service::where('created_by', Auth::id())
                               ->where('letter_of_service', $testLetter);
                if ($excludeId) {
                    $query->where('id', '!=', $excludeId);
                }
                
                if (!$query->exists()) {
                    $suggestions[] = $testLetter;
                }
            }
        }
        
        // Si pas assez de suggestions, essayer des combinaisons
        if (count($suggestions) < $limit) {
            for ($i = 2; $i <= 9 && count($suggestions) < $limit; $i++) {
                $testLetter = $baseLetter . $i;
                
                $query = Service::where('created_by', Auth::id())
                               ->where('letter_of_service', $testLetter);
                if ($excludeId) {
                    $query->where('id', '!=', $excludeId);
                }
                
                if (!$query->exists()) {
                    $suggestions[] = $testLetter;
                }
            }
        }
        
        return $suggestions;
    }

    /**
     * ✅ CORRIGÉ : Activer seulement ses propres services
     */
    public function activate(Service $service): JsonResponse
    {
        if ($service->created_by !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas activer ce service.'
            ], 403);
        }

        try {
            if ($service->isActive()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce service est déjà actif.'
                ]);
            }

            $service->activate();

            return response()->json([
                'success' => true,
                'message' => "Service '{$service->nom}' activé avec succès !"
            ]);

        } catch (Exception $e) {
            Log::error('Erreur lors de l\'activation du service: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'activation.'
            ], 500);
        }
    }

    /**
     * ✅ CORRIGÉ : Désactiver seulement ses propres services
     */
    public function deactivate(Service $service): JsonResponse
    {
        if ($service->created_by !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas désactiver ce service.'
            ], 403);
        }

        try {
            if ($service->isInactive()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce service est déjà inactif.'
                ]);
            }

            $service->deactivate();

            return response()->json([
                'success' => true,
                'message' => "Service '{$service->nom}' désactivé avec succès !"
            ]);

        } catch (Exception $e) {
            Log::error('Erreur lors de la désactivation du service: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la désactivation.'
            ], 500);
        }
    }

    /**
     * ✅ MISE À JOUR : Détails seulement pour ses propres services avec letter_of_service
     */
    public function details(Service $service): JsonResponse
    {
        if ($service->created_by !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas voir ce service.'
            ], 403);
        }

        try {
            $service->load('creator');
            
            // Calculer l'âge du service
            $createdAt = $service->created_at;
            $now = now();
            $ageDays = $createdAt->diffInDays($now);
            $ageFormatted = $this->formatServiceAge($ageDays);
            
            return response()->json([
                'success' => true,
                'service' => [
                    'id' => $service->id,
                    'nom' => $service->nom,
                    'letter_of_service' => $service->letter_of_service,
                    'statut' => $service->statut,
                    'statut_emoji' => $service->getStatusWithEmoji(),
                    'status_badge_color' => $service->getStatusBadgeColor(),
                    'description' => $service->description ?: 'Aucune description disponible',
                    'created_by' => $service->creator ? $service->creator->username : 'Système',
                    'created_at' => $service->created_at->format('d/m/Y à H:i'),
                    'created_at_iso' => $service->created_at->toISOString(),
                    'updated_at' => $service->updated_at->format('d/m/Y à H:i'),
                    'updated_at_iso' => $service->updated_at->toISOString(),
                    'age_formatted' => $ageFormatted,
                    'age_days' => $ageDays,
                    'is_active' => $service->isActive(),
                    'is_inactive' => $service->isInactive(),
                    'created_at_relative' => $service->created_at->diffForHumans(),
                    'updated_at_relative' => $service->updated_at->diffForHumans(),
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Erreur lors de la récupération des détails: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des détails.'
            ], 500);
        }
    }

    /**
     * ✅ MISE À JOUR : Recherche seulement dans ses propres services avec letter_of_service
     */
    public function searchServices(Request $request): JsonResponse
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
            // 🔒 RECHERCHE : Seulement dans ses propres services avec letter_of_service
            $services = Service::where('created_by', Auth::id())
                ->where(function($query) use ($search) {
                    $query->where('nom', 'LIKE', "%{$search}%")
                          ->orWhere('letter_of_service', 'LIKE', "%{$search}%")
                          ->orWhere('description', 'LIKE', "%{$search}%");
                })
                ->with('creator')
                ->limit(5)
                ->get();

            $suggestions = $services->map(function($service) {
                return [
                    'id' => $service->id,
                    'text' => $service->nom,
                    'letter_of_service' => $service->letter_of_service,
                    'statut' => $service->statut,
                    'description' => Str::limit($service->description, 50),
                    'creator' => $service->creator ? $service->creator->username : 'Système'
                ];
            });

            return response()->json([
                'success' => true,
                'suggestions' => $suggestions
            ]);
            
        } catch (Exception $e) {
            Log::error('Erreur lors de la recherche: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la recherche'
            ], 500);
        }
    }

    /**
     * ✅ CORRIGÉ : Activation en masse seulement pour ses services
     */
    public function bulkActivate(Request $request): JsonResponse
    {
        try {
            $count = Service::where('created_by', Auth::id())
                           ->where('statut', 'inactif')
                           ->update(['statut' => 'actif']);

            Log::info('Activation en masse effectuée', [
                'services_activated' => $count,
                'activated_by' => Auth::user()->username,
            ]);

            return response()->json([
                'success' => true,
                'message' => "{$count} de vos service(s) activé(s) avec succès !"
            ]);
        } catch (Exception $e) {
            Log::error('Erreur lors de l\'activation en masse: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'activation en masse.'
            ], 500);
        }
    }

    /**
     * ✅ CORRIGÉ : Suppression en masse seulement pour ses services
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'service_ids' => 'required|array',
            'service_ids.*' => 'exists:services,id'
        ]);

        try {
            // 🔒 SÉCURITÉ : Vérifier que tous les services appartiennent à l'admin
            $services = Service::whereIn('id', $request->service_ids)
                              ->where('created_by', Auth::id())
                              ->get();

            $count = 0;
            $errors = [];

            foreach ($services as $service) {
                if ($service->canBeDeleted()) {
                    $service->delete();
                    $count++;
                } else {
                    $errors[] = "Le service '{$service->nom}' ne peut pas être supprimé.";
                }
            }

            Log::warning('Suppression en masse effectuée', [
                'services_deleted' => $count,
                'deleted_by' => Auth::user()->username,
                'errors' => $errors,
            ]);

            $message = "{$count} de vos service(s) supprimé(s) avec succès !";
            if (!empty($errors)) {
                $message .= " Erreurs: " . implode(' ', $errors);
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (Exception $e) {
            Log::error('Erreur lors de la suppression en masse: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression en masse.'
            ], 500);
        }
    }

    /**
     * ✅ MISE À JOUR : Export seulement des services de l'admin avec letter_of_service
     */
    public function export(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        try {
            // 🔒 EXPORT : Seulement ses propres services
            $services = Service::where('created_by', Auth::id())->with('creator')->get();
            
            $filename = 'mes_services_' . date('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($services) {
                $file = fopen('php://output', 'w');
                
                // BOM pour UTF-8
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                
                // En-têtes CSV
                fputcsv($file, [
                    'ID',
                    'Nom',
                    'Lettre de service',
                    'Statut',
                    'Description',
                    'Créé par',
                    'Date de création',
                    'Dernière modification'
                ], ';');
                
                // Données
                foreach ($services as $service) {
                    fputcsv($file, [
                        $service->id,
                        $service->nom,
                        $service->letter_of_service,
                        $service->statut,
                        $service->description ?: 'Aucune description',
                        $service->creator ? $service->creator->username : 'Système',
                        $service->created_at->format('d/m/Y H:i:s'),
                        $service->updated_at->format('d/m/Y H:i:s')
                    ], ';');
                }
                
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
            
        } catch (Exception $e) {
            Log::error('Erreur lors de l\'export: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'export : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ CORRIGÉ : Statistiques seulement pour ses services
     */
    public function getStats(): JsonResponse
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false, 
                'message' => 'Accès non autorisé'
            ], 403);
        }

        try {
            // 🔒 STATISTIQUES : Seulement ses propres services
            $stats = [
                'total' => Service::where('created_by', Auth::id())->count(),
                'active' => Service::where('created_by', Auth::id())->where('statut', 'actif')->count(),
                'inactive' => Service::where('created_by', Auth::id())->where('statut', 'inactif')->count(),
                'created_today' => Service::where('created_by', Auth::id())->whereDate('created_at', today())->count(),
                'created_this_week' => Service::where('created_by', Auth::id())->where('created_at', '>=', now()->startOfWeek())->count(),
                'created_this_month' => Service::where('created_by', Auth::id())->where('created_at', '>=', now()->startOfMonth())->count(),
                'my_services' => Service::where('created_by', Auth::id())->count(),
                'recent_services' => Service::where('created_by', Auth::id())->where('created_at', '>=', now()->subDays(7))->count(),
            ];

            return response()->json([
                'success' => true, 
                'stats' => $stats,
                'timestamp' => now()->format('d/m/Y H:i:s')
            ]);
            
        } catch (Exception $e) {
            Log::error('Erreur lors de la récupération des statistiques: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Erreur lors de la récupération des statistiques'
            ], 500);
        }
    }

    /**
     * ✅ CORRIGÉ : Statistiques par type seulement pour ses services
     */
    public function getStatsByType(Request $request): JsonResponse
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false, 
                'message' => 'Accès non autorisé'
            ], 403);
        }

        try {
            // 🔒 STATISTIQUES PAR TYPE : Seulement ses propres services
            $statsByCreator = Service::where('created_by', Auth::id())
                ->selectRaw('created_by, COUNT(*) as total')
                ->selectRaw('SUM(CASE WHEN statut = "actif" THEN 1 ELSE 0 END) as active')
                ->selectRaw('SUM(CASE WHEN statut = "inactif" THEN 1 ELSE 0 END) as inactive')
                ->with('creator:id,username')
                ->groupBy('created_by')
                ->get()
                ->mapWithKeys(function($stat) {
                    $creatorName = $stat->creator ? $stat->creator->username : 'Système';
                    return [$creatorName => [
                        'total' => $stat->total,
                        'active' => $stat->active,
                        'inactive' => $stat->inactive
                    ]];
                });

            return response()->json([
                'success' => true, 
                'stats_by_creator' => $statsByCreator,
                'timestamp' => now()->format('d/m/Y H:i:s')
            ]);
            
        } catch (Exception $e) {
            Log::error('Erreur lors de la récupération des statistiques par type: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Erreur lors de la récupération des statistiques par type'
            ], 500);
        }
    }

/*  Nouveau pour la modification d'un service  */
    public function getServiceStats($id)
{
    try {
        $service = Service::findOrFail($id);
        
        // Vérifier l'autorisation
        if (!Auth::user()->isAdmin() || $service->created_by !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Service non autorisé'
            ], 403);
        }

        // Calculer les statistiques du service
        $stats = [
            'total_tickets' => 0,
            'completed_tickets' => 0,
            'today_tickets' => 0,
            'week_tickets' => 0,
            'average_wait_time' => 0
        ];

        // Si le modèle Queue existe, calculer les vraies statistiques
        if (class_exists('\App\Models\Queue')) {
            $stats = [
                'total_tickets' => \App\Models\Queue::where('service_id', $id)->count(),
                'completed_tickets' => \App\Models\Queue::where('service_id', $id)
                                                          ->where('statut_global', 'termine')
                                                          ->count(),
                'today_tickets' => \App\Models\Queue::where('service_id', $id)
                                                   ->whereDate('date', today())
                                                   ->count(),
                'week_tickets' => \App\Models\Queue::where('service_id', $id)
                                                  ->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
                                                  ->count(),
                'average_wait_time' => \App\Models\Queue::where('service_id', $id)
                                                       ->whereNotNull('temps_attente_estime')
                                                       ->avg('temps_attente_estime') ?? 0
            ];
        }

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'service' => [
                'id' => $service->id,
                'nom' => $service->nom,
                'letter_of_service' => $service->letter_of_service,
                'statut' => $service->statut
            ]
        ]);

    } catch (\Exception $e) {
        \Log::error("Erreur récupération statistiques service: " . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la récupération des statistiques'
        ], 500);
    }
}

/* Nouveau pour la modification d'un service */

public function update(Request $request, $id)
{
    try {
        $service = Service::findOrFail($id);
        
        // Vérifications d'autorisation
        if (!Auth::user()->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seuls les administrateurs peuvent modifier les services'
                ], 403);
            }
            abort(403, 'Seuls les administrateurs peuvent modifier les services');
        }

        // Protection : Vérifier que l'admin connecté a créé ce service
        if ($service->created_by !== Auth::id()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez modifier que vos propres services'
                ], 403);
            }
            
            return redirect()->route('service.service-list')
                ->with('error', 'Vous ne pouvez modifier que vos propres services');
        }

        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'letter_of_service' => 'required|string|max:5|unique:services,letter_of_service,' . $service->id,
            'statut' => 'required|in:actif,inactif',
            'description' => 'nullable|string|max:1000'
        ], [
            'nom.required' => 'Le nom du service est obligatoire.',
            'nom.string' => 'Le nom doit être une chaîne de caractères.',
            'nom.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'letter_of_service.required' => 'La lettre de service est obligatoire.',
            'letter_of_service.string' => 'La lettre doit être une chaîne de caractères.',
            'letter_of_service.max' => 'La lettre ne peut pas dépasser 5 caractères.',
            'letter_of_service.unique' => 'Cette lettre de service est déjà utilisée.',
            'statut.required' => 'Le statut est obligatoire.',
            'statut.in' => 'Le statut doit être soit actif soit inactif.',
            'description.string' => 'La description doit être une chaîne de caractères.',
            'description.max' => 'La description ne peut pas dépasser 1000 caractères.'
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        // Sauvegarder les anciennes valeurs pour les logs
        $oldNom = $service->nom;
        $oldLetter = $service->letter_of_service;
        $oldStatut = $service->statut;

        // Mettre à jour le service
        $service->update([
            'nom' => $request->nom,
            'letter_of_service' => strtoupper($request->letter_of_service),
            'statut' => $request->statut,
            'description' => $request->description,
        ]);

        // Log des modifications
        $changes = [];
        if ($oldNom !== $request->nom) {
            $changes[] = "nom: '{$oldNom}' → '{$request->nom}'";
        }
        if ($oldLetter !== strtoupper($request->letter_of_service)) {
            $changes[] = "lettre: '{$oldLetter}' → '" . strtoupper($request->letter_of_service) . "'";
        }
        if ($oldStatut !== $request->statut) {
            $changes[] = "statut: '{$oldStatut}' → '{$request->statut}'";
        }

        \Log::info("Service {$service->nom} (ID: {$service->id}) mis à jour par " . Auth::user()->username, [
            'service_id' => $service->id,
            'admin_id' => Auth::id(),
            'changes' => $changes,
            'old_values' => [
                'nom' => $oldNom,
                'letter_of_service' => $oldLetter,
                'statut' => $oldStatut
            ],
            'new_values' => [
                'nom' => $request->nom,
                'letter_of_service' => strtoupper($request->letter_of_service),
                'statut' => $request->statut
            ]
        ]);

        DB::commit();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Service '{$service->nom}' modifié avec succès",
                'service' => [
                    'id' => $service->id,
                    'nom' => $service->nom,
                    'letter_of_service' => $service->letter_of_service,
                    'statut' => $service->statut,
                    'description' => $service->description,
                    'updated_at' => $service->updated_at->format('d/m/Y H:i')
                ]
            ]);
        }

        return redirect()->route('service.service-list')
            ->with('success', "Service '{$service->nom}' modifié avec succès !");

    } catch (\Exception $e) {
        DB::rollBack();
        
        \Log::error("Erreur mise à jour service: " . $e->getMessage(), [
            'service_id' => $id,
            'admin_id' => Auth::id(),
            'request_data' => $request->all(),
            'error_trace' => $e->getTraceAsString()
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du service',
                'error_details' => $e->getMessage()
            ], 500);
        }

        return redirect()->back()
            ->with('error', 'Erreur lors de la mise à jour du service')
            ->withInput();
    }
}

/* Nouveau pour la modification d'un service */

public function edit($id, Request $request = null)
{
    try {
        $service = Service::findOrFail($id);
        $currentAdmin = Auth::user();
        
        // Vérifier que l'utilisateur est bien admin
        if (!$currentAdmin->isAdmin()) {
            if ($request && $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seuls les administrateurs peuvent modifier les services'
                ], 403);
            }
            abort(403, 'Seuls les administrateurs peuvent modifier les services');
        }

        // Protection : Vérifier que l'admin connecté a créé ce service
        if ($service->created_by !== $currentAdmin->id) {
            if ($request && $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez modifier que vos propres services'
                ], 403);
            }
            
            return redirect()->route('service.service-list')
                ->with('error', 'Vous ne pouvez modifier que vos propres services');
        }
        
        return view('service.service-edit', compact('service'));
        
    } catch (\Exception $e) {
        \Log::error("Erreur lors de l'accès à la modification de service", [
            'admin_id' => Auth::id(),
            'target_service_id' => $id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        if ($request && $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'accès à la modification'
            ], 500);
        }
        
        return redirect()->route('service.service-list')
            ->with('error', 'Erreur lors de l\'accès à la modification du service');
    }
}



    /**
     * Formater l'âge du service (pas de changement)
     */
    private function formatServiceAge(int $days): string
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