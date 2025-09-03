<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

// Modèles
use App\Models\Queue;
use App\Models\Service;
use App\Models\Agency;
use App\Models\AdministratorUser;
use App\Models\User;

class HistoryController extends Controller
{
    // Page
    public function index()
    {
        return view('layouts.history');
    }

    // API JSON avec pagination fixée à 8 éléments
    public function tickets(Request $request)
    {
        $admin = Auth::user();

        // Fallback sécurisé pour la valeur "transféré"
        $TRANSFER_IN = defined(Queue::class.'::TRANSFER_IN') ? Queue::TRANSFER_IN : 'new';

        // Services du périmètre de l'admin connecté
        $serviceIds = Service::where('created_by', $admin->id)->pluck('id');
        
        // Configuration de pagination FIXÉE à 8 éléments
        $per = 8; // Toujours 8 éléments par page
        $page = max((int)$request->get('page', 1), 1); // Au minimum page 1

        // Relations
        $with = ['service'];
        $advisorRel = null;
        if (method_exists(Queue::class, 'advisor')) {
            $advisorRel = 'advisor';
        } elseif (method_exists(Queue::class, 'conseillerClient')) {
            $advisorRel = 'conseillerClient';
        }
        if ($advisorRel) {
            $with[] = $advisorRel . '.agency';
        }

        // Requête principale avec pagination
        $q = Queue::whereIn('service_id', $serviceIds)
            ->with($with)
            ->orderBy('created_at', 'desc');

        // Filtres dates
        if ($df = $request->get('date_from')) $q->whereDate('date', '>=', $df);
        if ($dt = $request->get('date_to'))   $q->whereDate('date', '<=', $dt);

        // Filtre statut (accepte ancien et nouveau naming)
        if ($status = $request->get('status')) {
            switch ($status) {
                case 'termine':
                case 'treated':
                    $q->where('statut_global', 'termine');
                    break;

                case 'refuse':
                case 'refused':
                    $q->where('statut_global', 'termine')->where('resolu', 0);
                    break;

                case 'transfere':
                case 'shared':
                    $q->where('transferer', $TRANSFER_IN);
                    break;

                case 'en_cours':
                    $q->where('statut_global', 'en_cours');
                    break;

                case 'en_attente':
                    $q->where('statut_global', 'en_attente');
                    break;
            }
        }

        // Filtre résolu (0/1)
        $resolu = $request->get('resolu', '');
        if ($resolu !== '' && $resolu !== null) {
            $q->where('resolu', (int) $resolu);
        }

        // Filtre service
        if ($sid = $request->get('service_id')) $q->where('service_id', $sid);

        // Filtre agence
        if ($agencyId = $request->get('agency_id')) {
            if ($advisorRel) {
                $q->whereHas($advisorRel . '.agency', function ($a) use ($agencyId) {
                    $a->where('id', $agencyId);
                });
            } elseif (Schema::hasColumn('queues', 'agency_id')) {
                $q->where('agency_id', $agencyId);
            }
        }

        // Recherche
        if ($search = trim($request->get('search', ''))) {
            $q->where(function ($qq) use ($search, $advisorRel) {
                $qq->where('numero_ticket', 'like', "%$search%")
                   ->orWhere('prenom', 'like', "%$search%")
                   ->orWhere('telephone', 'like', "%$search%");
                if ($advisorRel) {
                    $qq->orWhereHas($advisorRel, function ($aa) use ($search) {
                        $aa->where('username', 'like', "%$search%");
                    });
                }
            });
        }

        // PAGINATION LARAVEL - FIXÉE À 8 ÉLÉMENTS
        $paginatedResults = $q->paginate($per, ['*'], 'page', $page);
        
        // Log pour debug
        \Log::info('Pagination debug (8 éléments fixes)', [
            'requested_page' => $page,
            'per_page_fixed' => $per,
            'total_items' => $paginatedResults->total(),
            'current_page' => $paginatedResults->currentPage(),
            'last_page' => $paginatedResults->lastPage(),
            'items_count' => $paginatedResults->count()
        ]);

        // Mapping des données
        $data = $paginatedResults->getCollection()->map(function ($t) use ($advisorRel, $TRANSFER_IN) {
            $treated    = $t->statut_global === 'termine';
            $refused    = $treated && (int)$t->resolu === 0;
            $transfere  = ($t->transferer === $TRANSFER_IN);

            // treated_at
            $treatedAt = null;
            if ($treated && !empty($t->heure_de_fin)) {
                $dateBase = $t->date ?: ($t->created_at ? $t->created_at->format('Y-m-d') : now()->toDateString());
                try {
                    $treatedAt = Carbon::parse($dateBase.' '.$t->heure_de_fin)->format('d/m/Y H:i');
                } catch (\Throwable $e) {
                    $treatedAt = optional($t->updated_at)->format('d/m/Y H:i');
                }
            } elseif ($treated && $t->updated_at) {
                $treatedAt = $t->updated_at->format('d/m/Y H:i');
            }

            $refusedAt     = $refused ? $treatedAt : null;
            $transferredAt = $transfere ? optional($t->updated_at)->format('d/m/Y H:i') : null;

            // prise_en_charge_at
            $priseEnCharge = null;
            if (!empty($t->heure_prise_en_charge)) {
                $dateBase = $t->date ?: ($t->created_at ? $t->created_at->format('Y-m-d') : now()->toDateString());
                try {
                    $priseEnCharge = Carbon::parse($dateBase.' '.$t->heure_prise_en_charge)->format('d/m/Y H:i');
                } catch (\Throwable $e) {
                    $priseEnCharge = optional($t->created_at)->format('d/m/Y H:i');
                }
            } elseif ($treatedAt) {
                $priseEnCharge = $treatedAt;
            } elseif ($transferredAt) {
                $priseEnCharge = $transferredAt;
            } else {
                $priseEnCharge = optional($t->created_at)->format('d/m/Y H:i');
            }

            // Conseiller + agence
            $advisor = null;
            if ($advisorRel && $t->{$advisorRel}) {
                $advisor = [
                    'id'       => $t->{$advisorRel}->id,
                    'username' => $t->{$advisorRel}->username,
                    'agency'   => $t->{$advisorRel}->agency ? [
                        'id'   => $t->{$advisorRel}->agency->id,
                        'name' => $t->{$advisorRel}->agency->name,
                    ] : null,
                ];
            }

            return [
                'id'                 => $t->id,
                'code'               => $t->numero_ticket,
                'created_at'         => optional($t->created_at)->format('d/m/Y H:i'),
                'prise_en_charge_at' => $priseEnCharge,
                'treated_at'         => $treatedAt,
                'refused_at'         => $refusedAt,
                'transferred_at'     => $transferredAt,

                'statut_global'      => $t->statut_global,
                'resolu'             => (int) $t->resolu,
                'transferer'         => $t->transferer,

                'service'            => $t->service ? [
                    'id'   => $t->service->id,
                    'name' => $t->service->nom,
                ] : null,

                'advisor'            => $advisor,
                'agency'             => $t->agency ?? null,
                'has_details'        => true,
            ];
        });

        // Filtres (services + agences)
        $services = Service::where('created_by', $admin->id)
            ->select('id', 'nom as name')
            ->orderBy('nom')
            ->get();

        $myUserIds = AdministratorUser::where('administrator_id', $admin->id)->pluck('user_id')->toArray();
        $agencies = Agency::whereHas('users', function ($query) use ($myUserIds) {
                $query->whereIn('id', $myUserIds);
            })
            ->orWhere('created_by', $admin->id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        // Retour JSON avec métadonnées de pagination correctes
        return response()->json([
            'success' => true,
            'filters' => [
                'services' => $services,
                'agencies' => $agencies,
            ],
            'data' => $data,
            'meta' => [
                'current_page' => $paginatedResults->currentPage(),
                'last_page'    => $paginatedResults->lastPage(),
                'per_page'     => 8, // Toujours 8
                'total'        => $paginatedResults->total(),
                'from'         => $paginatedResults->firstItem(),
                'to'           => $paginatedResults->lastItem(),
                // Info de debug utile
                'debug' => [
                    'requested_page' => $page,
                    'fixed_per_page' => 8,
                    'has_more_pages' => $paginatedResults->hasMorePages(),
                    'on_first_page' => $paginatedResults->onFirstPage(),
                    'service_ids_count' => count($serviceIds)
                ]
            ],
        ]);
    }

    /**
     * Récupérer les détails complets d'un ticket
     */
    public function ticketDetails(Request $request, $ticketId)
    {
        try {
            $admin = Auth::user();
            $serviceIds = Service::where('created_by', $admin->id)->pluck('id');

            // Récupérer le ticket avec toutes ses relations
            $ticket = Queue::whereIn('service_id', $serviceIds)
                          ->where('id', $ticketId)
                          ->with([
                              'service:id,nom,letter_of_service',
                              'conseillerClient:id,username,email',
                              'conseillerTransfert:id,username,email',
                              'agence:id,name'
                          ])
                          ->first();

            if (!$ticket) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ticket non trouvé ou non autorisé'
                ], 404);
            }

            // Informations de base
            $ticketDetails = [
                'id' => $ticket->id,
                'numero_ticket' => $ticket->numero_ticket,
                'service' => [
                    'id' => $ticket->service->id,
                    'nom' => $ticket->service->nom,
                    'letter' => $ticket->service->letter_of_service,
                ],
                'client_info' => [
                    'prenom' => $ticket->prenom,
                    'telephone' => $ticket->telephone,
                    'commentaire_initial' => $ticket->commentaire,
                ],
                'dates' => [
                    'created_at' => $ticket->created_at ? $ticket->created_at->format('d/m/Y H:i:s') : null,
                    'date_ticket' => $ticket->date ? $ticket->date->format('d/m/Y') : null,
                    'heure_enregistrement' => $ticket->heure_d_enregistrement,
                    'heure_prise_en_charge' => $ticket->heure_prise_en_charge,
                    'heure_de_fin' => $ticket->heure_de_fin,
                    'heure_transfert' => $ticket->heure_transfert,
                ],
                'statut' => [
                    'statut_global' => $ticket->statut_global,
                    'statut_libelle' => $ticket->getStatutLibelle(),
                    'resolu' => $ticket->resolu,
                    'resolu_libelle' => $ticket->getResoluLibelle(),
                    'commentaire_resolution' => $ticket->commentaire_resolution,
                ],
                'file_info' => [
                    'position_file' => $ticket->position_file,
                    'temps_attente_estime' => $ticket->temps_attente_estime,
                    'debut' => $ticket->debut,
                ],
                'transfer_info' => [
                    'transferer' => $ticket->transferer,
                    'transfer_status' => $ticket->getTransferStatus(),
                    'transfer_reason' => $ticket->transfer_reason,
                    'transfer_notes' => $ticket->transfer_notes,
                    'is_transferred_to_me' => $ticket->isTransferredToMe(),
                    'priority_level' => $ticket->getTransferPriorityLevel(),
                ],
                'conseillers' => [
                    'conseiller_principal' => $ticket->conseillerClient ? [
                        'id' => $ticket->conseillerClient->id,
                        'username' => $ticket->conseillerClient->username,
                        'email' => $ticket->conseillerClient->email,
                    ] : null,
                    'conseiller_transfert' => $ticket->conseillerTransfert ? [
                        'id' => $ticket->conseillerTransfert->id,
                        'username' => $ticket->conseillerTransfert->username,
                        'email' => $ticket->conseillerTransfert->email,
                    ] : null,
                ],
                'agence' => $ticket->agence ? [
                    'id' => $ticket->agence->id,
                    'name' => $ticket->agence->name,
                ] : null,
            ];

            // Timeline chronologique basée sur l'historique JSON
            $timeline = [];
            
            // 1. Création
            $timeline[] = [
                'action' => 'creation',
                'icon' => 'plus-circle',
                'title' => 'Ticket créé',
                'description' => 'Création du ticket #' . $ticket->numero_ticket,
                'timestamp' => $ticket->created_at ? $ticket->created_at->format('d/m/Y H:i:s') : 'Date inconnue',
                'details' => [
                    'Service' => $ticket->service->nom,
                    'Client' => $ticket->prenom,
                    'Téléphone' => $ticket->telephone,
                    'Commentaire' => $ticket->commentaire ?: 'Aucun',
                    'Position initiale' => $ticket->position_file ?: 'Non définie',
                ],
                'status' => 'success',
            ];

            // 2. Prise en charge
            if ($ticket->heure_prise_en_charge && $ticket->conseillerClient) {
                $timeline[] = [
                    'action' => 'prise_en_charge',
                    'icon' => 'user-check',
                    'title' => 'Pris en charge',
                    'description' => 'Appelé par ' . $ticket->conseillerClient->username,
                    'timestamp' => $ticket->heure_prise_en_charge,
                    'details' => [
                        'Conseiller' => $ticket->conseillerClient->username,
                        'Email conseiller' => $ticket->conseillerClient->email,
                        'Heure d\'appel' => $ticket->heure_prise_en_charge,
                        'Statut' => 'En cours de traitement',
                    ],
                    'status' => 'info',
                ];
            }

            // 3. Transfert (si applicable)
            if ($ticket->transferer && $ticket->transferer !== 'No' && $ticket->conseillerTransfert) {
                $transferIcon = $ticket->transferer === 'new' ? 'arrow-down-circle' : 'arrow-up-circle';
                $transferTitle = $ticket->transferer === 'new' ? 'Reçu par transfert' : 'Transféré';
                
                $timeline[] = [
                    'action' => 'transfer',
                    'icon' => $transferIcon,
                    'title' => $transferTitle,
                    'description' => $ticket->transferer === 'new' 
                        ? 'Reçu de ' . $ticket->conseillerTransfert->username
                        : 'Transféré vers ' . $ticket->conseillerTransfert->username,
                    'timestamp' => $ticket->heure_transfert ?: 'Heure inconnue',
                    'details' => [
                        'Type transfert' => $ticket->getTransferStatus(),
                        'Conseiller origine' => $ticket->conseillerTransfert->username,
                        'Raison' => $ticket->transfer_reason ?: 'Non précisée',
                        'Notes' => $ticket->transfer_notes ?: 'Aucune',
                        'Priorité accordée' => $ticket->transferer === 'new' ? 'Oui' : 'Non',
                    ],
                    'status' => $ticket->transferer === 'new' ? 'warning' : 'secondary',
                ];
            }

            // 4. Finalisation
            if ($ticket->statut_global === 'termine') {
                $finalizationIcon = $ticket->resolu === 1 ? 'check-circle' : 'x-circle';
                $finalizationTitle = $ticket->resolu === 1 ? 'Traité avec succès' : 'Refusé';
                $finalizationStatus = $ticket->resolu === 1 ? 'success' : 'danger';
                
                $timeline[] = [
                    'action' => 'finalization',
                    'icon' => $finalizationIcon,
                    'title' => $finalizationTitle,
                    'description' => $ticket->getResoluLibelle() . ' par ' . ($ticket->conseillerClient->username ?? 'Conseiller inconnu'),
                    'timestamp' => $ticket->heure_de_fin ?: 'Heure inconnue',
                    'details' => [
                        'Résolution' => $ticket->getResoluLibelle(),
                        'Conseiller final' => $ticket->conseillerClient->username ?? 'Inconnu',
                        'Heure finalisation' => $ticket->heure_de_fin ?: 'Non définie',
                        'Commentaire' => $ticket->commentaire_resolution ?: 'Aucun commentaire',
                    ],
                    'status' => $finalizationStatus,
                ];
            }

            // Historique JSON enrichi (si disponible)
            $historiqueJson = [];
            if ($ticket->historique) {
                try {
                    $historiqueJson = is_string($ticket->historique) 
                        ? json_decode($ticket->historique, true) 
                        : $ticket->historique;
                    
                    if (!is_array($historiqueJson)) {
                        $historiqueJson = [];
                    }
                } catch (\Exception $e) {
                    $historiqueJson = [];
                }
            }

            // Calculs de durées
            $durations = [
                'temps_attente' => null,
                'temps_traitement' => null,
                'temps_total' => null,
            ];

            if ($ticket->created_at && $ticket->heure_prise_en_charge) {
                try {
                    $created = $ticket->created_at;
                    $taken = Carbon::parse($ticket->date->format('Y-m-d') . ' ' . $ticket->heure_prise_en_charge);
                    $durations['temps_attente'] = $created->diffInMinutes($taken) . ' minutes';
                } catch (\Exception $e) {
                    $durations['temps_attente'] = 'Calcul impossible';
                }
            }

            if ($ticket->heure_prise_en_charge && $ticket->heure_de_fin) {
                try {
                    $taken = Carbon::parse($ticket->date->format('Y-m-d') . ' ' . $ticket->heure_prise_en_charge);
                    $finished = Carbon::parse($ticket->date->format('Y-m-d') . ' ' . $ticket->heure_de_fin);
                    $durations['temps_traitement'] = $taken->diffInMinutes($finished) . ' minutes';
                } catch (\Exception $e) {
                    $durations['temps_traitement'] = 'Calcul impossible';
                }
            }

            if ($ticket->created_at && $ticket->heure_de_fin) {
                try {
                    $created = $ticket->created_at;
                    $finished = Carbon::parse($ticket->date->format('Y-m-d') . ' ' . $ticket->heure_de_fin);
                    $durations['temps_total'] = $created->diffInMinutes($finished) . ' minutes';
                } catch (\Exception $e) {
                    $durations['temps_total'] = 'Calcul impossible';
                }
            }

            return response()->json([
                'success' => true,
                'ticket' => $ticketDetails,
                'timeline' => $timeline,
                'historique_json' => $historiqueJson,
                'durations' => $durations,
                'collaborative_info' => [
                    'was_transferred' => $ticket->transferer && $ticket->transferer !== 'No',
                    'transfer_type' => $ticket->getTransferStatus(),
                    'has_priority' => $ticket->hasTransferPriority(),
                    'system_type' => 'collaborative_fifo_chronological',
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur récupération détails ticket', [
                'ticket_id' => $ticketId,
                'admin_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des détails : ' . $e->getMessage()
            ], 500);
        }
    }

    // Export CSV avec pagination correcte
    public function export(Request $request)
    {
        // Fallback sécurisé pour la valeur "transféré"
        $TRANSFER_IN = defined(Queue::class.'::TRANSFER_IN') ? Queue::TRANSFER_IN : 'new';

        $req2 = Request::create(
            route('history.tickets'),
            'GET',
            array_merge($request->all(), ['per_page' => 100000])
        );
        $json = app()->handle($req2)->getContent();
        $payload = json_decode($json, true);
        $rows = $payload['data'] ?? [];

        $headers = [
            'Content-Type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="historique_tickets.csv"',
        ];

        return response()->stream(function () use ($rows, $TRANSFER_IN) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['Code','Pris en charge le','Statut','Service','Agence','Conseiller'], ';');

            foreach ($rows as $t) {
                // Statut pour export
                $status = '—';
                if (!empty($t['refused_at']) || (isset($t['statut_global']) && $t['statut_global'] === 'termine' && isset($t['resolu']) && (int)$t['resolu'] === 0)) {
                    $status = 'Refusé';
                } elseif (!empty($t['transferred_at']) || (isset($t['transferer']) && $t['transferer'] === $TRANSFER_IN)) {
                    $status = 'Transféré';
                } elseif (!empty($t['treated_at']) || (isset($t['statut_global']) && $t['statut_global'] === 'termine')) {
                    $status = 'Traité';
                }

                // Agence
                $agencyName = '—';
                if (isset($t['advisor']['agency']['name'])) {
                    $agencyName = $t['advisor']['agency']['name'];
                } elseif (isset($t['agency']['name'])) {
                    $agencyName = $t['agency']['name'];
                }

                fputcsv($out, [
                    $t['code'] ?? ('#'.($t['id'] ?? '')),
                    $t['prise_en_charge_at'] ?? ($t['created_at'] ?? ''),
                    $status,
                    $t['service']['name'] ?? '',
                    $agencyName,
                    $t['advisor']['username'] ?? '',
                ], ';');
            }
            fclose($out);
        }, 200, $headers);
    }
}