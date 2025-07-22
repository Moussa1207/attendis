<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AgencyController extends Controller
{
    /**
     * ✅ CORRIGÉ : Afficher SEULEMENT les agences créées par l'admin connecté
     */
    public function index(Request $request)
    {
        // 🔒 ISOLATION : Filtrer par admin connecté
        $query = Agency::where('created_by', Auth::id());

        // Recherche
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('address_1', 'LIKE', "%{$search}%")
                  ->orWhere('city', 'LIKE', "%{$search}%")
                  ->orWhere('country', 'LIKE', "%{$search}%");
            });
        }

        // Filtres (même logique, mais sur les agences de l'admin)
        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'active') {
                $query->where('status', 'active');
            } elseif ($status === 'inactive') {
                $query->where('status', 'inactive');
            }
        }

        if ($request->filled('country')) {
            $query->where('country', $request->get('country'));
        }

        if ($request->filled('city')) {
            $query->where('city', 'LIKE', "%{$request->get('city')}%");
        }

        if ($request->filled('recent')) {
            $days = (int) $request->get('recent');
            $query->where('created_at', '>=', Carbon::now()->subDays($days));
        }

        $query->orderBy('created_at', 'desc');
        $agencies = $query->paginate(15);

        // 🔒 STATISTIQUES : Seulement pour cet admin
        $stats = [
            'total' => Agency::where('created_by', Auth::id())->count(),
            'active' => Agency::where('created_by', Auth::id())->where('status', 'active')->count(),
            'inactive' => Agency::where('created_by', Auth::id())->where('status', 'inactive')->count(),
            'recent' => Agency::where('created_by', Auth::id())->where('created_at', '>=', Carbon::now()->subDays(7))->count(),
        ];

        return view('agency.agence', compact('agencies', 'stats'));
    }

    /**
     * Formulaire de création (pas de changement)
     */
    public function create()
    {
        return view('agency.agence-create');
    }

    /**
     * Créer une agence (pas de changement - déjà correct)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:agencies,name',
            'phone' => 'required|string|max:20',
            'address_1' => 'required|string|max:255',
            'address_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
        ], [
            'name.required' => 'Le nom de l\'agence est obligatoire.',
            'name.unique' => 'Ce nom d\'agence existe déjà.',
            'phone.required' => 'Le téléphone est obligatoire.',
            'address_1.required' => 'L\'adresse principale est obligatoire.',
            'city.required' => 'La ville est obligatoire.',
            'country.required' => 'Le pays est obligatoire.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $agency = Agency::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'address_1' => $request->address_1,
                'address_2' => $request->address_2,
                'city' => $request->city,
                'country' => $request->country,
                'status' => 'active',
                'created_by' => Auth::id(), // ✅ Déjà correct
            ]);

            return redirect()->route('agency.agence')
                ->with('success', " Agence \"{$agency->name}\" créée avec succès !");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la création de l\'agence : ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * ✅ CORRIGÉ : Vérifier que l'admin a créé cette agence
     */
    public function show(Agency $agency)
    {
        // 🔒 VÉRIFICATION : L'admin connecté a-t-il créé cette agence ?
        if ($agency->created_by !== Auth::id()) {
            abort(403, 'Vous ne pouvez pas voir cette agence.');
        }

        return view('agencies.show', compact('agency'));
    }

    /**
     * ✅ CORRIGÉ : Vérifier l'autorisation pour éditer
     */
    public function edit(Agency $agency)
    {
        if ($agency->created_by !== Auth::id()) {
            abort(403, 'Vous ne pouvez pas modifier cette agence.');
        }

           return view('agency.agence-edit', compact('agency'));    }

    /**
     * ✅ CORRIGÉ : Vérifier l'autorisation pour mettre à jour
     */
    public function update(Request $request, Agency $agency)
    {
        if ($agency->created_by !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas modifier cette agence.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:agencies,name,' . $agency->id,
            'phone' => 'required|string|max:20',
            'address_1' => 'required|string|max:255',
            'address_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $agency->update($request->only([
                'name', 'phone', 'address_1', 'address_2', 'city', 'country'
            ]));

            return redirect()->route('agency.agence')
                ->with('success', " Agence \"{$agency->name}\" mise à jour avec succès !");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * ✅ CORRIGÉ : Vérifier l'autorisation pour supprimer
     */
    public function destroy(Agency $agency)
    {
        if ($agency->created_by !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas supprimer cette agence.'
            ], 403);
        }

        try {
            $agencyName = $agency->name;
            $agency->delete();

            return response()->json([
                'success' => true,
                'message' => " Agence \"{$agencyName}\" supprimée avec succès !"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ CORRIGÉ : Activer seulement ses propres agences
     */
    public function activate(Agency $agency)
    {
        if ($agency->created_by !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas activer cette agence.'
            ], 403);
        }

        try {
            $agency->update(['status' => 'active']);

            return response()->json([
                'success' => true,
                'message' => "✅ Agence \"{$agency->name}\" activée avec succès !"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'activation : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ CORRIGÉ : Désactiver seulement ses propres agences
     */
    public function deactivate(Agency $agency)
    {
        if ($agency->created_by !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas désactiver cette agence.'
            ], 403);
        }

        try {
            $agency->update(['status' => 'inactive']);

            return response()->json([
                'success' => true,
                'message' => "⏸️ Agence \"{$agency->name}\" désactivée avec succès !"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la désactivation : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ CORRIGÉ : Détails seulement pour ses propres agences
     */
    public function details(Agency $agency)
    {
        if ($agency->created_by !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas voir cette agence.'
            ], 403);
        }

        try {
            $agency->load('creator');

            $agencyData = [
                'id' => $agency->id,
                'name' => $agency->name,
                'phone' => $agency->phone,
                'address_1' => $agency->address_1,
                'address_2' => $agency->address_2,
                'city' => $agency->city,
                'country' => $agency->country,
                'status' => $agency->status,
                'status_name' => $agency->status === 'active' ? 'Active' : 'Inactive',
                'status_badge_color' => $agency->status === 'active' ? 'success' : 'warning',
                'full_address' => $this->getFullAddress($agency),
                'created_at' => $agency->created_at->format('d/m/Y à H:i'),
                'created_at_iso' => $agency->created_at->toISOString(),
                'updated_at' => $agency->updated_at->format('d/m/Y à H:i'),
                'age_formatted' => $this->getAgencyAge($agency->created_at),
                'creator_name' => $agency->creator ? $agency->creator->username : 'Système',
                'notes' => $agency->notes ?? 'Aucune note disponible',
            ];

            return response()->json([
                'success' => true,
                'agency' => $agencyData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des détails : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ CORRIGÉ : Activation en masse seulement pour ses agences
     */
    public function bulkActivate()
    {
        try {
            $count = Agency::where('created_by', Auth::id())
                           ->where('status', 'inactive')
                           ->update(['status' => 'active']);

            return response()->json([
                'success' => true,
                'message' => "✅ {$count} de vos agence(s) activée(s) avec succès !"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'activation en masse : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ CORRIGÉ : Suppression en masse seulement pour ses agences
     */
    public function bulkDelete(Request $request)
    {
        try {
            $agencyIds = $request->input('agency_ids', []);
            
            if (empty($agencyIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune agence sélectionnée pour la suppression.'
                ], 400);
            }

            // 🔒 SÉCURITÉ : Vérifier que toutes les agences appartiennent à l'admin
            $count = Agency::whereIn('id', $agencyIds)
                          ->where('created_by', Auth::id())
                          ->delete();

            return response()->json([
                'success' => true,
                'message' => " {$count} de vos agence(s) supprimée(s) avec succès !"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression en masse : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ CORRIGÉ : Export seulement des agences de l'admin
     */
    public function export()
    {
        try {
            $agencies = Agency::where('created_by', Auth::id())
                             ->with('creator')
                             ->get();
            
            $filename = 'mes_agences_' . date('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($agencies) {
                $file = fopen('php://output', 'w');
                
                // BOM pour UTF-8
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                
                // En-têtes CSV
                fputcsv($file, [
                    'ID',
                    'Nom',
                    'Téléphone',
                    'Adresse 1',
                    'Adresse 2',
                    'Ville',
                    'Pays',
                    'Statut',
                    'Date de création'
                ], ';');
                
                // Données
                foreach ($agencies as $agency) {
                    fputcsv($file, [
                        $agency->id,
                        $agency->name,
                        $agency->phone,
                        $agency->address_1,
                        $agency->address_2 ?: '',
                        $agency->city,
                        $agency->country,
                        $agency->status === 'active' ? 'Active' : 'Inactive',
                        $agency->created_at->format('d/m/Y H:i:s')
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

    // Méthodes utilitaires (pas de changement)
    private function getFullAddress(Agency $agency)
    {
        $parts = array_filter([
            $agency->address_1,
            $agency->address_2,
            $agency->city,
            $agency->country
        ]);

        return implode(', ', $parts);
    }

    private function getAgencyAge($createdAt)
    {
        try {
            $diff = $createdAt->diff(now());
            
            if ($diff->y > 0) {
                return $diff->y . ' an(s)';
            } elseif ($diff->m > 0) {
                return $diff->m . ' mois';
            } elseif ($diff->d > 0) {
                return $diff->d . ' jour(s)';
            } elseif ($diff->h > 0) {
                return $diff->h . ' heure(s)';
            } else {
                return 'Moins d\'une heure';
            }
        } catch (\Exception $e) {
            return 'Calcul impossible';
        }
    }
}