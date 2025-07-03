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
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    /**
     * ✅ CORRIGÉ : Afficher SEULEMENT les services créés par l'admin connecté
     */
    public function index(Request $request): View
    {
        // 🔒 ISOLATION : Filtrer par admin connecté
        $query = Service::where('created_by', Auth::id())->with('creator');

        // Recherche (sur ses propres services)
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filtrage par statut (sur ses propres services)
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        // Tri
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $services = $query->paginate(15)->appends($request->query());

        return view('service.service-list', compact('services'));
    }

    /**
     * Formulaire de création (pas de changement)
     */
    public function create(): View
    {
        return view('service.service-create');
    }

    /**
     * Créer un service (pas de changement - déjà correct)
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:services,code',
            'statut' => 'required|in:actif,inactif',
            'description' => 'nullable|string|max:1000',
        ], [
            'nom.required' => 'Le nom du service est obligatoire.',
            'nom.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'code.required' => 'Le code du service est obligatoire.',
            'code.unique' => 'Ce code existe déjà. Veuillez en choisir un autre.',
            'code.max' => 'Le code ne peut pas dépasser 50 caractères.',
            'statut.required' => 'Le statut est obligatoire.',
            'statut.in' => 'Le statut doit être "actif" ou "inactif".',
            'description.max' => 'La description ne peut pas dépasser 1000 caractères.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Générer automatiquement le code si pas fourni ou nettoyer celui fourni
            $code = $request->code;
            if (empty($code)) {
                $code = Str::slug($request->nom);
            } else {
                $code = Str::slug($code);
            }

            // Vérifier l'unicité après transformation
            $originalCode = $code;
            $counter = 1;
            while (Service::where('code', $code)->exists()) {
                $code = $originalCode . '-' . $counter;
                $counter++;
            }

            $service = Service::create([
                'nom' => $request->nom,
                'code' => $code,
                'statut' => $request->statut,
                'description' => $request->description,
                'created_by' => Auth::id(), // ✅ Déjà correct
            ]);

            return redirect()->route('service.service-list')
                ->with('success', "Service '{$service->nom}' créé avec succès !\nCode généré : {$service->code}");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la création du service : ' . $e->getMessage())
                ->withInput();
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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des détails.'
            ], 500);
        }
    }

    /**
     * ✅ CORRIGÉ : Vérifier l'autorisation pour éditer
     */
    public function edit(Service $service): View
    {
        if ($service->created_by !== Auth::id()) {
            abort(403, 'Vous ne pouvez pas modifier ce service.');
        }

        return view('service.service-edit', compact('service'));
    }

    /**
     * ✅ CORRIGÉ : Vérifier l'autorisation pour mettre à jour
     */
    public function update(Request $request, Service $service): RedirectResponse
    {
        if ($service->created_by !== Auth::id()) {
            return redirect()->back()
                ->with('error', 'Vous ne pouvez pas modifier ce service.');
        }

        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:services,code,' . $service->id,
            'statut' => 'required|in:actif,inactif',
            'description' => 'nullable|string|max:1000',
        ], [
            'nom.required' => 'Le nom du service est obligatoire.',
            'code.required' => 'Le code du service est obligatoire.',
            'code.unique' => 'Ce code existe déjà.',
            'statut.in' => 'Le statut doit être "actif" ou "inactif".',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $service->update([
                'nom' => $request->nom,
                'code' => Str::slug($request->code),
                'statut' => $request->statut,
                'description' => $request->description,
            ]);

            return redirect()->route('service.service-list')
                ->with('success', "Service '{$service->nom}' mis à jour avec succès !");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage())
                ->withInput();
        }
    }

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
            $serviceName = $service->nom;
            $service->delete();

            return response()->json([
                'success' => true,
                'message' => "Service '{$serviceName}' supprimé avec succès !"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression : ' . $e->getMessage()
            ], 500);
        }
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
            $service->activate();

            return response()->json([
                'success' => true,
                'message' => "Service '{$service->nom}' activé avec succès !"
            ]);
        } catch (\Exception $e) {
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
            $service->deactivate();

            return response()->json([
                'success' => true,
                'message' => "Service '{$service->nom}' désactivé avec succès !"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la désactivation.'
            ], 500);
        }
    }

    /**
     * ✅ CORRIGÉ : Détails seulement pour ses propres services
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
                    'code' => $service->code,
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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des détails.'
            ], 500);
        }
    }

    /**
     * ✅ CORRIGÉ : Recherche seulement dans ses propres services
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
            // 🔒 RECHERCHE : Seulement dans ses propres services
            $services = Service::where('created_by', Auth::id())
                ->where(function($query) use ($search) {
                    $query->where('nom', 'LIKE', "%{$search}%")
                          ->orWhere('code', 'LIKE', "%{$search}%")
                          ->orWhere('description', 'LIKE', "%{$search}%");
                })
                ->with('creator')
                ->limit(5)
                ->get();

            $suggestions = $services->map(function($service) {
                return [
                    'id' => $service->id,
                    'text' => $service->nom,
                    'code' => $service->code,
                    'statut' => $service->statut,
                    'description' => Str::limit($service->description, 50),
                    'creator' => $service->creator ? $service->creator->username : 'Système'
                ];
            });

            return response()->json([
                'success' => true,
                'suggestions' => $suggestions
            ]);
            
        } catch (\Exception $e) {
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

            return response()->json([
                'success' => true,
                'message' => "{$count} de vos service(s) activé(s) avec succès !"
            ]);
        } catch (\Exception $e) {
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
            $count = Service::whereIn('id', $request->service_ids)
                           ->where('created_by', Auth::id())
                           ->delete();

            return response()->json([
                'success' => true,
                'message' => "{$count} de vos service(s) supprimé(s) avec succès !"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression en masse.'
            ], 500);
        }
    }

    /**
     * ✅ CORRIGÉ : Export seulement des services de l'admin
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
                    'Code',
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
                        $service->code,
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
            
        } catch (\Exception $e) {
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
            
        } catch (\Exception $e) {
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
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Erreur lors de la récupération des statistiques par type'
            ], 500);
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