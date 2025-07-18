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
     * ✅ ATTRIBUTS REMPLISSABLES (colonne 'numero' supprimée)
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
        'resolu',
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
     * ✅ CASTS CORRIGÉS (colonne 'numero' supprimée)
     */
    protected $casts = [
        'date' => 'date',
        'historique' => 'json',
        'position_file' => 'integer',
        'temps_attente_estime' => 'integer',
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
     * ✅ SCOPES POUR REQUÊTES (suppression du scope OrderByNumero)
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

    // 🆕 NOUVEAU SCOPE : Ordonner par ordre chronologique d'arrivée (FIFO)
    public function scopeOrderByArrival($query)
    {
        return $query->orderBy('created_at', 'asc');
    }

    /**
     * 🆕 GÉNÉRATION AUTOMATIQUE DU NUMÉRO DE TICKET - PAR SERVICE AVEC FILE CHRONOLOGIQUE
     * Format : Lettre du service + compteur du service (C001, C002, E001, E002...)
     * Mais ordre de traitement basé sur l'heure d'arrivée (FIFO chronologique)
     */
    public static function generateTicketNumber($serviceId, $date = null): string
    {
        $date = $date ?: today();

        // Récupérer la lettre du service
        $service = Service::find($serviceId);
        if (!$service) {
            throw new \Exception('Service introuvable pour génération ticket');
        }

        // 🎯 LOGIQUE CORRIGÉE : Compter les tickets de CE SERVICE pour cette date
        $serviceTicketCount = self::where('service_id', $serviceId)
                                 ->where('date', $date)
                                 ->count();

        // Prochain numéro pour ce service
        $nextServiceNumber = $serviceTicketCount + 1;

        // Format : Lettre service + compteur service
        // Exemples : C001, C002, E001, E002, B001... (chaque service a ses numéros)
        return $service->letter_of_service . str_pad($nextServiceNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * 🆕 CALCUL DE LA POSITION DANS LA FILE UNIQUE CHRONOLOGIQUE
     * Position basée sur l'ordre d'arrivée (created_at)
     */
    public static function calculateQueuePosition($date = null): int
    {
        $date = $date ?: today();
        
        // Position globale dans la file chronologique
        // Compter TOUS les tickets en attente de TOUS les services pour cette date
        $currentPosition = self::where('date', $date)
                              ->where('statut_global', 'en_attente')
                              ->count();

        return $currentPosition + 1;
    }

    /**
     * ✅ ESTIMATION DU TEMPS D'ATTENTE - INCHANGÉE (basée sur config admin)
     */
    public static function estimateWaitingTime($position = null): int
    {
        // Récupération du temps configuré par l'admin
        $adminConfiguredTime = \App\Models\Setting::getDefaultWaitingTimeMinutes();
        
        if ($position === null) {
            $position = self::calculateQueuePosition();
        }

        // Calcul basé sur le temps configuré par l'admin
        $waitingCount = max(0, $position - 1);
        $estimatedTime = $waitingCount * $adminConfiguredTime;
        
        // Minimum 0 minute, maximum 300 minutes (5h)
        return max(0, min(300, $estimatedTime));
    }

    /**
     * 🆕 CRÉATION D'UN NOUVEAU TICKET - LOGIQUE FIFO CHRONOLOGIQUE
     */
    public static function createTicket(array $data): self
    {
        try {
            DB::beginTransaction();

            // Validation des données requises
            if (!isset($data['service_id']) || !isset($data['prenom']) || !isset($data['telephone'])) {
                throw new \Exception('Données obligatoires manquantes');
            }

            $serviceId = $data['service_id'];
            $agenceId = $data['id_agence'] ?? null;
            $date = today();

            // Vérifier que le service existe et est actif
            $service = Service::find($serviceId);
            if (!$service) {
                throw new \Exception('Service introuvable');
            }

            if (!$service->isActive()) {
                throw new \Exception('Service actuellement fermé');
            }

            // 🆕 GÉNÉRATION CORRIGÉE : Numéro par service + position globale chronologique
            $ticketNumber = self::generateTicketNumber($serviceId, $date);
            $position = self::calculateQueuePosition($date);
            $estimatedTime = self::estimateWaitingTime($position);

            $currentTime = now()->format('H:i:s');

            // Créer le ticket avec la logique FIFO chronologique
            $ticket = self::create([
                'id_agence' => $agenceId,
                'letter_of_service' => $service->letter_of_service,
                'service_id' => $serviceId,
                'prenom' => trim($data['prenom']),
                'telephone' => trim($data['telephone']),
                'commentaire' => isset($data['commentaire']) ? trim($data['commentaire']) : null,
                'date' => $date,
                'heure_d_enregistrement' => $currentTime,
                'numero_ticket' => $ticketNumber, // Ex: A001, B001, A002, C001...
                'position_file' => $position, // Position chronologique globale
                'temps_attente_estime' => $estimatedTime, // Basé sur config admin
                'statut_global' => 'en_attente',
                'resolu' => 'En cours',
                'transferer' => 'No',
                'debut' => 'No',
                'created_by_ip' => request()->ip() ?? null,
                'historique' => [
                    [
                        'action' => 'creation',
                        'timestamp' => now()->toISOString(),
                        'details' => 'Ticket créé - Numérotation par service avec file chronologique unique'
                    ]
                ]
            ]);

            DB::commit();

            Log::info('Nouveau ticket créé - Numérotation par service avec file chronologique', [
                'ticket_id' => $ticket->id,
                'numero_ticket' => $ticket->numero_ticket,
                'service_name' => $service->nom,
                'service_letter' => $service->letter_of_service,
                'agence_id' => $agenceId,
                'client_name' => $ticket->prenom,
                'position_chronologique' => $ticket->position_file,
                'temps_attente_admin' => $estimatedTime,
                'order_concept' => 'Numérotation par service, traitement chronologique'
            ]);

            return $ticket;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création ticket - Numérotation par service avec file chronologique', [
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
     * ✅ ACTIONS SUR LE TICKET (inchangées)
     */
    public function priseEnCharge($conseillerId): bool
    {
        try {
            $currentTime = now()->format('H:i:s');
            
            $this->update([
                'conseiller_client_id' => $conseillerId,
                'heure_prise_en_charge' => $currentTime,
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

    public function terminer($resolu = 'Yes', $commentaire = null): bool
    {
        try {
            $currentTime = now()->format('H:i:s');
            
            $this->update([
                'heure_de_fin' => $currentTime,
                'statut_global' => 'termine',
                'resolu' => $resolu,
                'commentaire_resolution' => $commentaire,
                'historique' => array_merge($this->historique ?? [], [
                    [
                        'action' => 'terminer',
                        'timestamp' => now()->toISOString(),
                        'resolu' => $resolu,
                        'commentaire' => $commentaire
                    ]
                ])
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
     * 🆕 DONNÉES POUR L'API/FRONTEND - SANS NUMERO
     */
    public function toTicketArray(): array
    {
        return [
            'id' => $this->id,
            'numero_ticket' => $this->numero_ticket, // Format : C001, C002, E001, E002... (par service)
            'service' => $this->service ? $this->service->nom : 'Service inconnu',
            'service_letter' => $this->letter_of_service,
            'agence_id' => $this->id_agence,
            'client_name' => $this->prenom,
            'telephone' => $this->telephone,
            'commentaire' => $this->commentaire,
            'date' => $this->date->format('d/m/Y'),
            'heure' => $this->formatHeureEnregistrement(),
            'position' => $this->position_file, // Position chronologique globale
            'temps_attente_estime' => $this->temps_attente_estime,
            'statut' => $this->statut_global,
            'statut_libelle' => $this->getStatutLibelle(),
            'is_en_attente' => $this->isEnAttente(),
            'is_en_cours' => $this->isEnCours(),
            'is_termine' => $this->isTermine(),
            'conseiller' => $this->conseillerClient ? $this->conseillerClient->username : null,
            'created_at' => $this->created_at->format('d/m/Y H:i:s'),
            'queue_type' => 'service_numbering_chronological_processing', // Numérotation par service, traitement chronologique
            'arrival_order' => $this->created_at->format('H:i:s') // Heure d'arrivée pour référence
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
            return substr($this->heure_d_enregistrement, 0, 5); // H:i
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
     * 🔄 OBTENIR LA LISTE D'ATTENTE CHRONOLOGIQUE (FIFO)
     * Ordre chronologique global - tous services confondus
     */
    public static function getChronologicalQueue($date = null): \Illuminate\Support\Collection
    {
        $date = $date ?: today();

        return self::where('date', $date)
                   ->where('statut_global', 'en_attente')
                   ->orderBy('created_at', 'asc') // 🎯 ORDRE CHRONOLOGIQUE FIFO
                   ->with(['service', 'agence'])
                   ->get();
    }

    /**
     * 🔄 OBTENIR LE PROCHAIN TICKET À TRAITER GLOBALEMENT
     * Le plus ancien ticket en attente (tous services confondus)
     */
    public static function getNextTicketGlobal($date = null)
    {
        $date = $date ?: today();

        return self::where('date', $date)
                   ->where('statut_global', 'en_attente')
                   ->orderBy('created_at', 'asc') // Premier arrivé
                   ->first();
    }

    /**
     * 🔄 OBTENIR LES PROCHAINS TICKETS D'UN SERVICE (ordre chronologique)
     */
    public static function getServiceQueueChronological($serviceId, $date = null): \Illuminate\Support\Collection
    {
        $date = $date ?: today();

        return self::where('service_id', $serviceId)
                   ->where('date', $date)
                   ->where('statut_global', 'en_attente')
                   ->orderBy('created_at', 'asc') // Ordre chronologique d'arrivée
                   ->with(['service', 'agence'])
                   ->get();
    }

    /**
     * 🔄 STATISTIQUES DU SERVICE - ADAPTÉES SANS NUMERO
     */
    public static function getServiceStats($serviceId, $date = null): array
    {
        $date = $date ?: today();

        $baseStats = [
            'total_tickets' => self::where('service_id', $serviceId)->where('date', $date)->count(),
            'en_attente' => self::where('service_id', $serviceId)->where('date', $date)->where('statut_global', 'en_attente')->count(),
            'en_cours' => self::where('service_id', $serviceId)->where('date', $date)->where('statut_global', 'en_cours')->count(),
            'termines' => self::where('service_id', $serviceId)->where('date', $date)->where('statut_global', 'termine')->count(),
            'temps_attente_moyen' => self::where('service_id', $serviceId)->where('date', $date)->avg('temps_attente_estime') ?? 0,
            'dernier_ticket' => self::where('service_id', $serviceId)->where('date', $date)->orderBy('created_at', 'desc')->first(),
        ];

        // 🆕 STATISTIQUES CHRONOLOGIQUES
        $prochains_tickets = self::where('service_id', $serviceId)
                                ->where('date', $date)
                                ->where('statut_global', 'en_attente')
                                ->orderBy('created_at', 'asc') // Ordre chronologique
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
                'ordre_traitement' => 'Premier arrivé, premier servi (FIFO)'
            ]
        ]);
    }

    /**
     * 🆕 STATISTIQUES GLOBALES DE LA FILE CHRONOLOGIQUE FIFO
     */
    public static function getGlobalQueueStats($date = null): array
    {
        $date = $date ?: today();

        $totalToday = self::where('date', $date)->count();
        $enAttente = self::where('date', $date)->where('statut_global', 'en_attente')->count();
        $enCours = self::where('date', $date)->where('statut_global', 'en_cours')->count();
        $termines = self::where('date', $date)->where('statut_global', 'termine')->count();

        return [
            'date' => $date->format('d/m/Y'),
            'file_chronologique_service_numbering' => [
                'total_tickets_aujourd_hui' => $totalToday,
                'en_attente' => $enAttente,
                'en_cours' => $enCours,
                'termines' => $termines,
                'prochaine_position' => self::calculateQueuePosition($date),
                'temps_attente_configure' => \App\Models\Setting::getDefaultWaitingTimeMinutes(),
                'temps_attente_estime_prochain' => self::estimateWaitingTime(),
                'dernier_numero_genere' => self::where('date', $date)
                                              ->orderBy('created_at', 'desc')
                                              ->first()?->numero_ticket ?? 'Aucun',
                'principe' => 'Numérotation par service, traitement chronologique'
            ],
            'repartition_par_service' => self::where('date', $date)
                                           ->join('services', 'queues.service_id', '=', 'services.id')
                                           ->selectRaw('services.nom as service_name, services.letter_of_service, COUNT(*) as tickets_count')
                                           ->groupBy('services.id', 'services.nom', 'services.letter_of_service')
                                           ->get()
                                           ->toArray(),
            'sequence_chronologique_today' => self::where('date', $date)
                                                 ->orderBy('created_at', 'asc')
                                                 ->limit(20)
                                                 ->get(['numero_ticket', 'created_at', 'heure_d_enregistrement'])
                                                 ->map(function($ticket) {
                                                     return [
                                                         'numero' => $ticket->numero_ticket,
                                                         'heure' => $ticket->heure_d_enregistrement ?: $ticket->created_at->format('H:i:s')
                                                     ];
                                                 })
                                                 ->toArray(),
            // 🆕 APERÇU DE L'ORDRE CHRONOLOGIQUE (numérotation par service)
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
                                                    'rang_chronologique' => 'Ordre d\'arrivée (numérotation par service)'
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
     * ✅ BOOT METHOD POUR LES ÉVÉNEMENTS (modifié sans references au numero)
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
        });

        static::created(function ($queue) {
            Log::info('Ticket créé - Numérotation par service avec traitement chronologique', [
                'id' => $queue->id,
                'numero_ticket' => $queue->numero_ticket, // Ex: C001, C002, E001, E002
                'service' => $queue->service->nom ?? 'N/A',
                'service_letter' => $queue->letter_of_service,
                'agence_id' => $queue->id_agence,
                'position_chronologique' => $queue->position_file,
                'heure_arrivee' => $queue->heure_d_enregistrement,
                'ordre_concept' => 'Numérotation par service, traitement par ordre d\'arrivée'
            ]);
        });
    }
}