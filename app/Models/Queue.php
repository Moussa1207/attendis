<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class Queue extends Model
{
    use HasFactory;

    /**
     * ✅ ATTRIBUTS REMPLISSABLES - MODIFIÉ pour resolu tinyint
     */
    protected $fillable = [
        'id_agence',
        'letter_of_service',
        'service_id',
        'prenom',
        'telephone',
        'commentaire',
        'date',
        'heure_d_enregistrement',
        'heure_prise_en_charge',
        'heure_de_fin',
        'heure_transfert',
        'conseiller_client_id',
        'conseiller_transfert',
        'resolu', // ✅ MODIFIÉ : maintenant tinyint (0/1)
        'commentaire_resolution',
        'transferer',
        'debut',
        'numero_ticket',
        'position_file',
        'temps_attente_estime',
        'statut_global',
        'historique',
        'created_by_ip',
        'notes_internes',
    ];

    /**
     * ✅ CASTS MODIFIÉS pour resolu tinyint
     */
    protected $casts = [
        'date' => 'date',
        'historique' => 'json',
        'position_file' => 'integer',
        'temps_attente_estime' => 'integer',
        'resolu' => 'integer', // ✅ NOUVEAU : cast en integer (0/1)
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * ✅ RELATIONS ELOQUENT (inchangées)
     */
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function agence()
    {
        return $this->belongsTo(Agency::class, 'id_agence');
    }

    public function conseillerClient()
    {
        return $this->belongsTo(User::class, 'conseiller_client_id');
    }

    public function conseillerTransfert()
    {
        return $this->belongsTo(User::class, 'conseiller_transfert');
    }

    /**
     * ✅ SCOPES POUR REQUÊTES (inchangés)
     */
    public function scopeToday($query)
    {
        return $query->whereDate('date', today());
    }

    public function scopeEnAttente($query)
    {
        return $query->where('statut_global', 'en_attente');
    }

    public function scopeEnCours($query)
    {
        return $query->where('statut_global', 'en_cours');
    }

    public function scopeTermine($query)
    {
        return $query->where('statut_global', 'termine');
    }

    public function scopeForService($query, $serviceId)
    {
        return $query->where('service_id', $serviceId);
    }

    public function scopeOrderByArrival($query)
    {
        return $query->orderBy('created_at', 'asc');
    }

    /**
     * ✅ NOUVELLES MÉTHODES pour resolu tinyint
     */
    public function isResolu(): bool
    {
        return $this->resolu === 1;
    }

    public function isNonResolu(): bool
    {
        return $this->resolu === 0;
    }

    public function getResoluLibelle(): string
    {
        return match($this->resolu) {
            1 => 'Résolu',
            0 => 'Non résolu',
            default => 'Inconnu'
        };
    }

    /**
     * 🆕 GÉNÉRATION AUTOMATIQUE DU NUMÉRO DE TICKET - PAR SERVICE AVEC FILE CHRONOLOGIQUE
     */
    public static function generateTicketNumber($serviceId, $date = null): string
    {
        $date = $date ?: today();

        $service = Service::find($serviceId);
        if (!$service) {
            throw new \Exception('Service introuvable pour génération ticket');
        }

        $serviceTicketCount = self::where('service_id', $serviceId)
                                 ->where('date', $date)
                                 ->count();

        $nextServiceNumber = $serviceTicketCount + 1;

        return $service->letter_of_service . str_pad($nextServiceNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * 🆕 CALCUL DE LA POSITION DANS LA FILE UNIQUE CHRONOLOGIQUE
     */
    public static function calculateQueuePosition($date = null): int
    {
        $date = $date ?: today();
        
        $currentPosition = self::where('date', $date)
                              ->where('statut_global', 'en_attente')
                              ->count();

        return $currentPosition + 1;
    }

    /**
     * ✅ ESTIMATION DU TEMPS D'ATTENTE
     */
    public static function estimateWaitingTime($position = null): int
    {
        $adminConfiguredTime = \App\Models\Setting::getDefaultWaitingTimeMinutes();
        
        if ($position === null) {
            $position = self::calculateQueuePosition();
        }

        $waitingCount = max(0, $position - 1);
        $estimatedTime = $waitingCount * $adminConfiguredTime;
        
        return max(0, min(300, $estimatedTime));
    }

    /**
     * 🆕 CRÉATION D'UN NOUVEAU TICKET - LOGIQUE FIFO CHRONOLOGIQUE
     */
    public static function createTicket(array $data): self
    {
        try {
            DB::beginTransaction();

            if (!isset($data['service_id']) || !isset($data['prenom']) || !isset($data['telephone'])) {
                throw new \Exception('Données obligatoires manquantes');
            }

            $serviceId = $data['service_id'];
            $agenceId = $data['id_agence'] ?? null;
            $date = today();

            $service = Service::find($serviceId);
            if (!$service) {
                throw new \Exception('Service introuvable');
            }

            if (!$service->isActive()) {
                throw new \Exception('Service actuellement fermé');
            }

            $ticketNumber = self::generateTicketNumber($serviceId, $date);
            $position = self::calculateQueuePosition($date);
            $estimatedTime = self::estimateWaitingTime($position);

            $currentTime = now()->format('H:i:s');

            $ticket = self::create([
                'id_agence' => $agenceId,
                'letter_of_service' => $service->letter_of_service,
                'service_id' => $serviceId,
                'prenom' => trim($data['prenom']),
                'telephone' => trim($data['telephone']),
                'commentaire' => isset($data['commentaire']) ? trim($data['commentaire']) : null,
                'date' => $date,
                'heure_d_enregistrement' => $currentTime,
                'numero_ticket' => $ticketNumber,
                'position_file' => $position,
                'temps_attente_estime' => $estimatedTime,
                'statut_global' => 'en_attente',
                'resolu' => 1, // ✅ MODIFIÉ : Par défaut résolu (1)
                'transferer' => 'No',
                'debut' => 'No',
                'created_by_ip' => request()->ip() ?? null,
                'historique' => [
                    [
                        'action' => 'creation',
                        'timestamp' => now()->toISOString(),
                        'details' => 'Ticket créé - File chronologique avec resolu tinyint'
                    ]
                ]
            ]);

            DB::commit();

            Log::info('Nouveau ticket créé avec resolu tinyint', [
                'ticket_id' => $ticket->id,
                'numero_ticket' => $ticket->numero_ticket,
                'service_name' => $service->nom,
                'client_name' => $ticket->prenom,
                'resolu_default' => $ticket->resolu, // Log du nouveau format
                'position_chronologique' => $ticket->position_file,
            ]);

            return $ticket;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création ticket avec resolu tinyint', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * ✅ MÉTHODES D'ÉTAT (inchangées)
     */
    public function isEnAttente(): bool
    {
        return $this->statut_global === 'en_attente';
    }

    public function isEnCours(): bool
    {
        return $this->statut_global === 'en_cours';
    }

    public function isTermine(): bool
    {
        return $this->statut_global === 'termine';
    }

    public function isTransfere(): bool
    {
        return $this->transferer === 'Yes';
    }

    /**
     * ✅ PRISE EN CHARGE (inchangé - automatisation fonctionnelle)
     */
    public function priseEnCharge($conseillerId): bool
    {
        try {
            $currentTime = now()->format('H:i:s');
            
            $this->update([
                'conseiller_client_id' => $conseillerId, // ✅ Auto: ID conseiller
                'heure_prise_en_charge' => $currentTime, // ✅ Auto: Heure d'appel
                'statut_global' => 'en_cours',
                'debut' => 'Yes',
                'historique' => array_merge($this->historique ?? [], [
                    [
                        'action' => 'prise_en_charge',
                        'timestamp' => now()->toISOString(),
                        'conseiller_id' => $conseillerId
                    ]
                ])
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Erreur prise en charge ticket', [
                'ticket_id' => $this->id,
                'conseiller_id' => $conseillerId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * ✅ TERMINER - MODIFIÉ pour supporter resolu tinyint et commentaire obligatoire
     */
    public function terminer($resolu = 1, $commentaire = null): bool
    {
        try {
            $currentTime = now()->format('H:i:s');
            
            // ✅ VALIDATION : Commentaire obligatoire pour les refus
            if ($resolu === 0 && empty($commentaire)) {
                throw new \Exception('Commentaire obligatoire pour les tickets non résolus');
            }
            
            $this->update([
                'heure_de_fin' => $currentTime, // ✅ Auto: Heure de fin
                'statut_global' => 'termine',
                'resolu' => (int)$resolu, // ✅ MODIFIÉ : tinyint (0/1)
                'commentaire_resolution' => $commentaire,
                'historique' => array_merge($this->historique ?? [], [
                    [
                        'action' => 'terminer',
                        'timestamp' => now()->toISOString(),
                        'resolu' => $resolu,
                        'resolu_libelle' => $resolu === 1 ? 'Résolu' : 'Non résolu',
                        'commentaire' => $commentaire
                    ]
                ])
            ]);

            Log::info('Ticket terminé avec nouveau format resolu', [
                'ticket_id' => $this->id,
                'numero_ticket' => $this->numero_ticket,
                'resolu' => $resolu,
                'resolu_libelle' => $this->getResoluLibelle(),
                'has_comment' => !empty($commentaire)
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Erreur finalisation ticket', [
                'ticket_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * 🆕 DONNÉES POUR L'API/FRONTEND - MODIFIÉ pour resolu tinyint
     */
    public function toTicketArray(): array
    {
        return [
            'id' => $this->id,
            'numero_ticket' => $this->numero_ticket,
            'service' => $this->service ? $this->service->nom : 'Service inconnu',
            'service_letter' => $this->letter_of_service,
            'agence_id' => $this->id_agence,
            'client_name' => $this->prenom,
            'prenom' => $this->prenom, // ✅ Champ principal pour les noms
            'telephone' => $this->telephone,
            'commentaire' => $this->commentaire,
            'date' => $this->date->format('d/m/Y'),
            'heure' => $this->formatHeureEnregistrement(),
            'heure_d_enregistrement' => $this->heure_d_enregistrement, // ✅ Pour calcul temps réel
            'position' => $this->position_file,
            'temps_attente_estime' => $this->temps_attente_estime,
            'statut' => $this->statut_global,
            'statut_libelle' => $this->getStatutLibelle(),
            'resolu' => $this->resolu, // ✅ NOUVEAU : 0/1
            'resolu_libelle' => $this->getResoluLibelle(), // ✅ NOUVEAU : "Résolu"/"Non résolu"
            'commentaire_resolution' => $this->commentaire_resolution,
            'is_en_attente' => $this->isEnAttente(),
            'is_en_cours' => $this->isEnCours(),
            'is_termine' => $this->isTermine(),
            'is_resolu' => $this->isResolu(), // ✅ NOUVEAU
            'is_non_resolu' => $this->isNonResolu(), // ✅ NOUVEAU
            'conseiller' => $this->conseillerClient ? $this->conseillerClient->username : null,
            'heure_prise_en_charge' => $this->heure_prise_en_charge, // ✅ Auto-rempli
            'heure_de_fin' => $this->heure_de_fin, // ✅ Auto-rempli
            'created_at' => $this->created_at->format('d/m/Y H:i:s'),
            'queue_type' => 'service_numbering_chronological_processing',
            'arrival_order' => $this->created_at->format('H:i:s')
        ];
    }

    /**
     * ✅ MÉTHODE D'AIDE POUR FORMATTER L'HEURE (inchangée)
     */
    private function formatHeureEnregistrement(): string
    {
        if (!$this->heure_d_enregistrement) {
            return '--:--';
        }
        
        if (is_string($this->heure_d_enregistrement)) {
            return substr($this->heure_d_enregistrement, 0, 5);
        }
        
        try {
            return Carbon::parse($this->heure_d_enregistrement)->format('H:i');
        } catch (\Exception $e) {
            return '--:--';
        }
    }

    /**
     * ✅ LIBELLÉ DU STATUT (inchangé)
     */
    public function getStatutLibelle(): string
    {
        return match($this->statut_global) {
            'en_attente' => 'En attente',
            'en_cours' => 'En cours de traitement',
            'termine' => 'Terminé',
            'transfere' => 'Transféré',
            default => 'Statut inconnu'
        };
    }

    /**
     * 🔄 OBTENIR LA LISTE D'ATTENTE CHRONOLOGIQUE (FIFO) - inchangé
     */
    public static function getChronologicalQueue($date = null): \Illuminate\Support\Collection
    {
        $date = $date ?: today();

        return self::where('date', $date)
                   ->where('statut_global', 'en_attente')
                   ->orderBy('created_at', 'asc')
                   ->with(['service', 'agence'])
                   ->get();
    }

    /**
     * 🔄 OBTENIR LE PROCHAIN TICKET À TRAITER GLOBALEMENT - inchangé
     */
    public static function getNextTicketGlobal($date = null)
    {
        $date = $date ?: today();

        return self::where('date', $date)
                   ->where('statut_global', 'en_attente')
                   ->orderBy('created_at', 'asc')
                   ->first();
    }

    /**
     * 🔄 OBTENIR LES PROCHAINS TICKETS D'UN SERVICE - inchangé
     */
    public static function getServiceQueueChronological($serviceId, $date = null): \Illuminate\Support\Collection
    {
        $date = $date ?: today();

        return self::where('service_id', $serviceId)
                   ->where('date', $date)
                   ->where('statut_global', 'en_attente')
                   ->orderBy('created_at', 'asc')
                   ->with(['service', 'agence'])
                   ->get();
    }

    /**
     * 🔄 STATISTIQUES DU SERVICE - MODIFIÉES pour resolu tinyint
     */
    public static function getServiceStats($serviceId, $date = null): array
    {
        $date = $date ?: today();

        $baseStats = [
            'total_tickets' => self::where('service_id', $serviceId)->where('date', $date)->count(),
            'en_attente' => self::where('service_id', $serviceId)->where('date', $date)->where('statut_global', 'en_attente')->count(),
            'en_cours' => self::where('service_id', $serviceId)->where('date', $date)->where('statut_global', 'en_cours')->count(),
            'termines' => self::where('service_id', $serviceId)->where('date', $date)->where('statut_global', 'termine')->count(),
            // ✅ NOUVELLES STATS avec resolu tinyint
            'resolus' => self::where('service_id', $serviceId)->where('date', $date)->where('resolu', 1)->count(),
            'non_resolus' => self::where('service_id', $serviceId)->where('date', $date)->where('resolu', 0)->count(),
            'temps_attente_moyen' => self::where('service_id', $serviceId)->where('date', $date)->avg('temps_attente_estime') ?? 0,
            'dernier_ticket' => self::where('service_id', $serviceId)->where('date', $date)->orderBy('created_at', 'desc')->first(),
        ];

        $prochains_tickets = self::where('service_id', $serviceId)
                                ->where('date', $date)
                                ->where('statut_global', 'en_attente')
                                ->orderBy('created_at', 'asc')
                                ->limit(5)
                                ->get(['numero_ticket', 'created_at', 'heure_d_enregistrement']);

        return array_merge($baseStats, [
            'file_chronologique_info' => [
                'position_globale_actuelle' => self::calculateQueuePosition($date),
                'total_attente_globale' => self::where('date', $date)->where('statut_global', 'en_attente')->count(),
                'temps_attente_configure' => \App\Models\Setting::getDefaultWaitingTimeMinutes(),
                'prochains_tickets' => $prochains_tickets->map(function($ticket) {
                    return [
                        'numero_ticket' => $ticket->numero_ticket,
                        'heure_arrivee' => $ticket->heure_d_enregistrement ?: $ticket->created_at->format('H:i:s'),
                        'created_at' => $ticket->created_at->format('H:i:s')
                    ];
                })->toArray(),
                'ordre_traitement' => 'Premier arrivé, premier servi (FIFO)',
                // ✅ NOUVELLES INFOS resolu
                'resolution_stats' => [
                    'taux_resolution' => $baseStats['total_tickets'] > 0 ? round(($baseStats['resolus'] / $baseStats['total_tickets']) * 100, 2) : 0,
                    'tickets_resolus' => $baseStats['resolus'],
                    'tickets_non_resolus' => $baseStats['non_resolus']
                ]
            ]
        ]);
    }

    /**
     * 🆕 STATISTIQUES GLOBALES - MODIFIÉES pour resolu tinyint
     */
    public static function getGlobalQueueStats($date = null): array
    {
        $date = $date ?: today();

        $totalToday = self::where('date', $date)->count();
        $enAttente = self::where('date', $date)->where('statut_global', 'en_attente')->count();
        $enCours = self::where('date', $date)->where('statut_global', 'en_cours')->count();
        $termines = self::where('date', $date)->where('statut_global', 'termine')->count();
        $resolus = self::where('date', $date)->where('resolu', 1)->count(); // ✅ NOUVEAU
        $nonResolus = self::where('date', $date)->where('resolu', 0)->count(); // ✅ NOUVEAU

        return [
            'date' => $date->format('d/m/Y'),
            'file_chronologique_service_numbering' => [
                'total_tickets_aujourd_hui' => $totalToday,
                'en_attente' => $enAttente,
                'en_cours' => $enCours,
                'termines' => $termines,
                'resolus' => $resolus, // ✅ NOUVEAU
                'non_resolus' => $nonResolus, // ✅ NOUVEAU
                'taux_resolution' => $totalToday > 0 ? round(($resolus / $totalToday) * 100, 2) : 0, // ✅ NOUVEAU
                'prochaine_position' => self::calculateQueuePosition($date),
                'temps_attente_configure' => \App\Models\Setting::getDefaultWaitingTimeMinutes(),
                'temps_attente_estime_prochain' => self::estimateWaitingTime(),
                'dernier_numero_genere' => self::where('date', $date)
                                              ->orderBy('created_at', 'desc')
                                              ->first()?->numero_ticket ?? 'Aucun',
                'principe' => 'Numérotation par service, traitement chronologique avec résolution binaire'
            ],
            'repartition_par_service' => self::where('date', $date)
                                           ->join('services', 'queues.service_id', '=', 'services.id')
                                           ->selectRaw('services.nom as service_name, services.letter_of_service, COUNT(*) as tickets_count, SUM(resolu) as resolus_count')
                                           ->groupBy('services.id', 'services.nom', 'services.letter_of_service')
                                           ->get()
                                           ->toArray(),
            'sequence_chronologique_today' => self::where('date', $date)
                                                 ->orderBy('created_at', 'asc')
                                                 ->limit(20)
                                                 ->get(['numero_ticket', 'created_at', 'heure_d_enregistrement', 'resolu'])
                                                 ->map(function($ticket) {
                                                     return [
                                                         'numero' => $ticket->numero_ticket,
                                                         'heure' => $ticket->heure_d_enregistrement ?: $ticket->created_at->format('H:i:s'),
                                                         'resolu' => $ticket->resolu,
                                                         'resolu_libelle' => $ticket->resolu === 1 ? 'Résolu' : 'Non résolu'
                                                     ];
                                                 })
                                                 ->toArray(),
            'ordre_traitement_actuel' => self::where('date', $date)
                                            ->where('statut_global', 'en_attente')
                                            ->orderBy('created_at', 'asc')
                                            ->limit(10)
                                            ->with('service:id,nom,letter_of_service')
                                            ->get()
                                            ->map(function($ticket) {
                                        return [
                                            'numero_ticket' => $ticket->numero_ticket,
                                            'service_name' => $ticket->service->nom ?? 'N/A',
                                            'service_letter' => $ticket->service->letter_of_service ?? 'N/A',
                                            'heure_arrivee' => $ticket->heure_d_enregistrement ?: $ticket->created_at->format('H:i:s'),
                                            'rang_chronologique' => 'Ordre d\'arrivée avec résolution binaire'
                                        ];
                                    })
                                    ->toArray()
        ];
    }

    /**
     * ✅ NETTOYAGE AUTOMATIQUE DES ANCIENS TICKETS (inchangé)
     */
    public static function cleanOldTickets($daysToKeep = 30): int
    {
        $cutoffDate = now()->subDays($daysToKeep);
        
        $deletedCount = self::where('date', '<', $cutoffDate)
                           ->where('statut_global', 'termine')
                           ->delete();

        Log::info('Nettoyage automatique des tickets', [
            'tickets_supprimés' => $deletedCount,
            'cutoff_date' => $cutoffDate->format('Y-m-d')
        ]);
            
        return $deletedCount;
    }

    /**
     * ✅ BOOT METHOD - MODIFIÉ pour resolu tinyint par défaut
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($queue) {
            if (empty($queue->date)) {
                $queue->date = today();
            }
            
            if (empty($queue->heure_d_enregistrement)) {
                $queue->heure_d_enregistrement = now()->format('H:i:s');
            }

            if (empty($queue->statut_global)) {
                $queue->statut_global = 'en_attente';
            }

            // ✅ NOUVEAU : resolu par défaut à 1 (résolu)
            if (!isset($queue->resolu)) {
                $queue->resolu = 1;
            }
        });

        static::created(function ($queue) {
            Log::info('Ticket créé avec nouveau format resolu', [
                'id' => $queue->id,
                'numero_ticket' => $queue->numero_ticket,
                'service' => $queue->service->nom ?? 'N/A',
                'service_letter' => $queue->letter_of_service,
                'agence_id' => $queue->id_agence,
                'position_chronologique' => $queue->position_file,
                'heure_arrivee' => $queue->heure_d_enregistrement,
                'resolu_default' => $queue->resolu, // ✅ Log du nouveau format
                'ordre_concept' => 'Numérotation par service, traitement chronologique avec résolution binaire'
            ]);
        });
    }
}