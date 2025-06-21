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
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Agency::query();

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

        // Filtres
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

        // Filtre récent
        if ($request->filled('recent')) {
            $days = (int) $request->get('recent');
            $query->where('created_at', '>=', Carbon::now()->subDays($days));
        }

        // Trier par date de création (plus récent en premier)
        $query->orderBy('created_at', 'desc');

        // Pagination
        $agencies = $query->paginate(15);

        // Statistiques
        $stats = [
            'total' => Agency::count(),
            'active' => Agency::where('status', 'active')->count(),
            'inactive' => Agency::where('status', 'inactive')->count(),
            'recent' => Agency::where('created_at', '>=', Carbon::now()->subDays(7))->count(),
        ];

        return view('agency.agence', compact('agencies', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('agency.agence-create');
    }

    /**
     * Store a newly created resource in storage.
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
                'status' => 'active', // Par défaut active
                'created_by' => Auth::id(),
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
     * Display the specified resource.
     */
    public function show(Agency $agency)
    {
        return view('agencies.show', compact('agency'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Agency $agency)
    {
        return view('agencies.edit', compact('agency'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Agency $agency)
    {
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
     * Remove the specified resource from storage.
     */
    public function destroy(Agency $agency)
    {
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
     * Activate an agency.
     */
    public function activate(Agency $agency)
    {
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
     * Deactivate an agency.
     */
    public function deactivate(Agency $agency)
    {
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
     * Get agency details for modal.
     */
    public function details(Agency $agency)
    {
        try {
            // Charger les relations nécessaires
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
     * Bulk activate agencies.
     */
    public function bulkActivate()
    {
        try {
            $count = Agency::where('status', 'inactive')->update(['status' => 'active']);

            return response()->json([
                'success' => true,
                'message' => "✅ {$count} agence(s) activée(s) avec succès !"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'activation en masse : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete agencies.
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

            $count = Agency::whereIn('id', $agencyIds)->delete();

            return response()->json([
                'success' => true,
                'message' => " {$count} agence(s) supprimée(s) avec succès !"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression en masse : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export agencies to Excel.
     */
    public function export()
    {
        try {
            // Ici vous pouvez implémenter l'export Excel
            // Pour l'instant, on simule un téléchargement
            return response()->json([
                'success' => true,
                'message' => 'Export des agences en cours...'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'export : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper: Get full address.
     */
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

    /**
     * Helper: Get agency age.
     */
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