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
     * ✅ ATTRIBUTS REMPLISSABLES - MODIFIÉ pour resolu tinyint et système collaboratif
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
        'transferer', // ✅ COLLABORATIF : "new", "transferé", "No"
        'debut',
        'numero_ticket',
        'position_file',
        'temps_attente_estime',
        'statut_global',
        'historique',
        'created_by_ip',
        'notes_internes',
        'transfer_reason', // 🆕 NOUVEAU : Motif du transfert
        'transfer_notes',  // 🆕 NOUVEAU : Notes du transfert
    ];

    /**
     * ✅ CASTS MODIFIÉS pour resolu tinyint et système collaboratif
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
     * ✅ RELATIONS ELOQUENT - AMÉLIORÉES avec transfert collaboratif
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
     * ✅ SCOPES POUR REQUÊTES - AMÉLIORÉS avec système collaboratif
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

    // 🆕 NOUVEAU : Scopes pour système collaboratif
    public function scopeTransferredToMe($query)
    {
        return $query->where('transferer', 'new');
    }

    public function scopeTransferredByMe($query, $userId)
    {
        return $query->where('conseiller_transfert', $userId)->where('transferer', 'transferé');
    }

    public function scopeNormalTickets($query)
    {
        return $query->where(function($q) {
            $q->whereNull('transferer')
              ->orWhere('transferer', 'No')
              ->orWhere('transferer', 'no');
        });
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
     * 🆕 NOUVELLES MÉTHODES pour système collaboratif
     */
    public function isTransferredToMe(): bool
    {
        return $this->transferer === 'new';
    }

    public function isTransferredByMe($userId): bool
    {
        return $this->conseiller_transfert == $userId && $this->transferer === 'transferé';
    }

    public function isNormalTicket(): bool
    {
        return in_array($this->transferer, [null, 'No', 'no']);
    }

    public function hasTransferPriority(): bool
    {
        return $this->transferer === 'new';
    }

    public function getTransferStatus(): string
    {
        return match($this->transferer) {
            'new' => 'Reçu par transfert',
            'transferé' => 'Transféré par moi',
            default => 'Normal'
        };
    }

    public function getTransferPriorityLevel(): string
    {
        return match($this->transferer) {
            'new' => 'high',
            'transferé' => 'blocked',
            default => 'normal'
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
     * 🆕 CALCUL DE LA POSITION DANS LA FILE UNIQUE CHRONOLOGIQUE COLLABORATIVE
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
     * ✅ ESTIMATION DU TEMPS D'ATTENTE AVEC PRIORITÉ COLLABORATIVE
     */
    public static function estimateWaitingTime($position = null, $hasPriority = false): int
    {
        $adminConfiguredTime = \App\Models\Setting::getDefaultWaitingTimeMinutes();
        
        if ($position === null) {
            $position = self::calculateQueuePosition();
        }

        // 🆕 Les tickets avec priorité transfert passent devant
        if ($hasPriority) {
            $priorityTickets = self::today()->enAttente()->transferredToMe()->count();
            $waitingCount = max(0, $priorityTickets - 1);
        } else {
            $waitingCount = max(0, $position - 1);
        }
        
        $estimatedTime = $waitingCount * $adminConfiguredTime;
        
        return max(0, min(300, $estimatedTime));
    }

    /**
     * 🆕 CRÉATION D'UN NOUVEAU TICKET - LOGIQUE FIFO CHRONOLOGIQUE COLLABORATIVE
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
                'transferer' => 'No', // ✅ COLLABORATIF : Ticket normal par défaut
                'debut' => 'No',
                'created_by_ip' => request()->ip() ?? null,
                'historique' => [
                    [
                        'action' => 'creation',
                        'timestamp' => now()->toISOString(),
                        'details' => 'Ticket créé - File chronologique collaborative avec resolu tinyint',
                        'collaborative_system' => 'active'
                    ]
                ]
            ]);

            DB::commit();

            Log::info('Nouveau ticket créé avec système collaboratif', [
                'ticket_id' => $ticket->id,
                'numero_ticket' => $ticket->numero_ticket,
                'service_name' => $service->nom,
                'client_name' => $ticket->prenom,
                'resolu_default' => $ticket->resolu,
                'transfer_status' => $ticket->transferer,
                'position_chronologique' => $ticket->position_file,
                'collaborative_system' => 'active'
            ]);

            return $ticket;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création ticket avec système collaboratif', [
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
        return in_array($this->transferer, ['new', 'transferé']);
    }

    /**
     * ✅ PRISE EN CHARGE - AMÉLIORÉE avec système collaboratif
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
                        'conseiller_id' => $conseillerId,
                        'transfer_context' => [
                            'was_transferred' => $this->isTransferredToMe(),
                            'transfer_status' => $this->transferer,
                            'priority_level' => $this->getTransferPriorityLevel()
                        ]
                    ]
                ])
            ]);

            Log::info('Ticket pris en charge avec contexte collaboratif', [
                'ticket_id' => $this->id,
                'numero_ticket' => $this->numero_ticket,
                'conseiller_id' => $conseillerId,
                'was_transferred' => $this->isTransferredToMe(),
                'transfer_status' => $this->transferer,
                'priority_taken' => $this->hasTransferPriority()
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Erreur prise en charge ticket collaboratif', [
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
                        'commentaire' => $commentaire,
                        'collaborative_context' => [
                            'was_transferred' => $this->isTransferredToMe(),
                            'transfer_status' => $this->transferer,
                            'transferred_by' => $this->conseiller_transfert
                        ]
                    ]
                ])
            ]);

            Log::info('Ticket terminé avec système collaboratif', [
                'ticket_id' => $this->id,
                'numero_ticket' => $this->numero_ticket,
                'resolu' => $resolu,
                'resolu_libelle' => $this->getResoluLibelle(),
                'has_comment' => !empty($commentaire),
                'was_transferred' => $this->isTransferredToMe(),
                'transfer_status' => $this->transferer,
                'collaborative_completion' => true
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Erreur finalisation ticket collaboratif', [
                'ticket_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * 🆕 DONNÉES POUR L'API/FRONTEND - MODIFIÉ pour resolu tinyint et système collaboratif
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
            
            // 🆕 NOUVEAU : Informations de transfert basiques
            'transferer' => $this->transferer,
            'is_transferred_to_me' => $this->isTransferredToMe(),
            'has_transfer_priority' => $this->hasTransferPriority(),
            
            'queue_type' => 'collaborative_service_numbering_chronological_processing',
            'arrival_order' => $this->created_at->format('H:i:s')
        ];
    }

    /**
     * 🆕 NOUVEAU : Données enrichies avec informations complètes de transfert collaboratif
     */
    public function toTicketArrayWithTransfer(): array
    {
        $basicArray = $this->toTicketArray();
        
        // ✅ Enrichir avec informations complètes de transfert
        $transferInfo = [
            'transfer_status' => $this->transferer,
            'transfer_status_label' => $this->getTransferStatus(),
            'priority_level' => $this->getTransferPriorityLevel(),
            'is_transferred_to_me' => $this->isTransferredToMe(),
            'is_transferred_by_me' => false, // Sera défini par le contrôleur
            'is_normal_ticket' => $this->isNormalTicket(),
            'has_transfer_priority' => $this->hasTransferPriority(),
            'transferred_by_id' => $this->conseiller_transfert,
            'transferred_by_name' => $this->conseillerTransfert ? $this->conseillerTransfert->username : null,
            'transferred_by_email' => $this->conseillerTransfert ? $this->conseillerTransfert->email : null,
            'transfer_reason' => $this->transfer_reason,
            'transfer_notes' => $this->transfer_notes,
            'transfer_time' => $this->heure_transfert,
            'collaborative_system' => [
                'active' => true,
                'type' => 'fifo_with_transfer_priority',
                'priority_rules' => 'Tickets "new" prioritaires, puis FIFO chronologique'
            ]
        ];

        return array_merge($basicArray, ['transfer_info' => $transferInfo]);
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
     * 🔄 OBTENIR LA LISTE D'ATTENTE CHRONOLOGIQUE COLLABORATIVE (FIFO AVEC PRIORITÉ)
     */
    public static function getChronologicalQueueWithPriority($date = null): \Illuminate\Support\Collection
    {
        $date = $date ?: today();

        // 🎯 PRIORITÉ 1 : Les tickets "new" (transférés) en premier par ordre chronologique
        $priorityTickets = self::where('date', $date)
                              ->where('statut_global', 'en_attente')
                              ->where('transferer', 'new')
                              ->orderBy('created_at', 'asc')
                              ->with(['service', 'agence', 'conseillerTransfert'])
                              ->get();

        // 🎯 PRIORITÉ 2 : Les tickets normaux par ordre chronologique
        $normalTickets = self::where('date', $date)
                            ->where('statut_global', 'en_attente')
                            ->where(function($query) {
                                $query->whereNull('transferer')
                                      ->orWhere('transferer', 'No')
                                      ->orWhere('transferer', 'no');
                            })
                            ->orderBy('created_at', 'asc')
                            ->with(['service', 'agence'])
                            ->get();

        // 🆕 Marquer chaque ticket avec sa priorité
        $priorityTickets->each(function($ticket) {
            $ticket->collaborative_priority = 'high';
            $ticket->priority_reason = 'Reçu par transfert';
        });

        $normalTickets->each(function($ticket) {
            $ticket->collaborative_priority = 'normal';
            $ticket->priority_reason = 'FIFO chronologique';
        });

        // 🔄 Fusionner : priorité puis normal
        return $priorityTickets->concat($normalTickets);
    }

    /**
     * 🔄 OBTENIR LE PROCHAIN TICKET À TRAITER AVEC PRIORITÉ COLLABORATIVE
     */
    public static function getNextTicketCollaborative($date = null)
    {
        $date = $date ?: today();

        // 🎯 PRIORITÉ 1 : Chercher d'abord un ticket "new"
        $nextTicket = self::where('date', $date)
                         ->where('statut_global', 'en_attente')
                         ->where('transferer', 'new')
                         ->orderBy('created_at', 'asc')
                         ->first();

        if ($nextTicket) {
            $nextTicket->selection_reason = 'Priorité transfert';
            return $nextTicket;
        }

        // 🎯 PRIORITÉ 2 : Si pas de "new", prendre le premier normal
        $nextTicket = self::where('date', $date)
                         ->where('statut_global', 'en_attente')
                         ->where(function($query) {
                             $query->whereNull('transferer')
                                   ->orWhere('transferer', 'No')
                                   ->orWhere('transferer', 'no');
                         })
                         ->orderBy('created_at', 'asc')
                         ->first();

        if ($nextTicket) {
            $nextTicket->selection_reason = 'FIFO chronologique';
        }

        return $nextTicket;
    }

    /**
     * 🔄 OBTENIR LES PROCHAINS TICKETS D'UN SERVICE AVEC PRIORITÉ COLLABORATIVE
     */
    public static function getServiceQueueCollaborative($serviceId, $date = null): \Illuminate\Support\Collection
    {
        $date = $date ?: today();

        // 🎯 Même logique que getChronologicalQueueWithPriority mais filtré par service
        $priorityTickets = self::where('service_id', $serviceId)
                              ->where('date', $date)
                              ->where('statut_global', 'en_attente')
                              ->where('transferer', 'new')
                              ->orderBy('created_at', 'asc')
                              ->with(['service', 'agence', 'conseillerTransfert'])
                              ->get();

        $normalTickets = self::where('service_id', $serviceId)
                            ->where('date', $date)
                            ->where('statut_global', 'en_attente')
                            ->where(function($query) {
                                $query->whereNull('transferer')
                                      ->orWhere('transferer', 'No')
                                      ->orWhere('transferer', 'no');
                            })
                            ->orderBy('created_at', 'asc')
                            ->with(['service', 'agence'])
                            ->get();

        return $priorityTickets->concat($normalTickets);
    }

    /**
     * 🔄 STATISTIQUES DU SERVICE - MODIFIÉES pour resolu tinyint et transferts collaboratifs
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
            
            // 🆕 NOUVEAU : Statistiques de transfert collaboratif
            'tickets_transferred_in' => self::where('service_id', $serviceId)->where('date', $date)->where('transferer', 'new')->count(),
            'tickets_transferred_out' => self::where('service_id', $serviceId)->where('date', $date)->where('transferer', 'transferé')->count(),
            'normal_tickets' => self::where('service_id', $serviceId)->where('date', $date)->normalTickets()->count(),
            
            'temps_attente_moyen' => self::where('service_id', $serviceId)->where('date', $date)->avg('temps_attente_estime') ?? 0,
            'dernier_ticket' => self::where('service_id', $serviceId)->where('date', $date)->orderBy('created_at', 'desc')->first(),
        ];

        // 🆕 Prochains tickets avec priorité collaborative
        $prochains_tickets = self::getServiceQueueCollaborative($serviceId, $date)->take(5);

        return array_merge($baseStats, [
            'file_chronologique_collaborative' => [
                'position_globale_actuelle' => self::calculateQueuePosition($date),
                'total_attente_globale' => self::where('date', $date)->where('statut_global', 'en_attente')->count(),
                'tickets_prioritaires' => $baseStats['tickets_transferred_in'],
                'tickets_normaux' => $baseStats['normal_tickets'],
                'temps_attente_configure' => \App\Models\Setting::getDefaultWaitingTimeMinutes(),
                'prochains_tickets' => $prochains_tickets->map(function($ticket) {
                    return [
                        'numero_ticket' => $ticket->numero_ticket,
                        'heure_arrivee' => $ticket->heure_d_enregistrement ?: $ticket->created_at->format('H:i:s'),
                        'created_at' => $ticket->created_at->format('H:i:s'),
                        'priority' => $ticket->hasTransferPriority() ? 'high' : 'normal',
                        'transfer_status' => $ticket->transferer,
                        'transferred_by' => $ticket->conseillerTransfert ? $ticket->conseillerTransfert->username : null
                    ];
                })->toArray(),
                'ordre_traitement' => 'Priorité transferts puis FIFO chronologique',
                
                // ✅ NOUVELLES INFOS resolu avec transferts
                'resolution_stats' => [
                    'taux_resolution' => $baseStats['total_tickets'] > 0 ? round(($baseStats['resolus'] / $baseStats['total_tickets']) * 100, 2) : 0,
                    'tickets_resolus' => $baseStats['resolus'],
                    'tickets_non_resolus' => $baseStats['non_resolus']
                ],
                
                // 🆕 NOUVEAU : Stats collaboratives
                'collaborative_stats' => [
                    'transfers_received' => $baseStats['tickets_transferred_in'],
                    'transfers_sent' => $baseStats['tickets_transferred_out'],
                    'collaboration_rate' => $baseStats['total_tickets'] > 0 ? 
                        round((($baseStats['tickets_transferred_in'] + $baseStats['tickets_transferred_out']) / $baseStats['total_tickets']) * 100, 2) : 0
                ]
            ]
        ]);
    }

    /**
     * 🆕 STATISTIQUES GLOBALES - MODIFIÉES pour resolu tinyint et système collaboratif
     */
    public static function getGlobalQueueStats($date = null): array
    {
        $date = $date ?: today();

        $totalToday = self::where('date', $date)->count();
        $enAttente = self::where('date', $date)->where('statut_global', 'en_attente')->count();
        $enCours = self::where('date', $date)->where('statut_global', 'en_cours')->count();
        $termines = self::where('date', $date)->where('statut_global', 'termine')->count();
        $resolus = self::where('date', $date)->where('resolu', 1)->count();
        $nonResolus = self::where('date', $date)->where('resolu', 0)->count();
        
        // 🆕 NOUVEAU : Stats de transfert collaboratif
        $transfersReceived = self::where('date', $date)->where('transferer', 'new')->count();
        $transfersSent = self::where('date', $date)->where('transferer', 'transferé')->count();
        $normalTickets = self::where('date', $date)->normalTickets()->count();

        return [
            'date' => $date->format('d/m/Y'),
            'file_chronologique_collaborative_service_numbering' => [
                'total_tickets_aujourd_hui' => $totalToday,
                'en_attente' => $enAttente,
                'en_cours' => $enCours,
                'termines' => $termines,
                'resolus' => $resolus,
                'non_resolus' => $nonResolus,
                'taux_resolution' => $totalToday > 0 ? round(($resolus / $totalToday) * 100, 2) : 0,
                
                // 🆕 NOUVEAU : Stats collaboratives globales
                'transfers_received' => $transfersReceived,
                'transfers_sent' => $transfersSent,
                'normal_tickets' => $normalTickets,
                'collaboration_rate' => $totalToday > 0 ? round((($transfersReceived + $transfersSent) / $totalToday) * 100, 2) : 0,
                
                'prochaine_position' => self::calculateQueuePosition($date),
                'temps_attente_configure' => \App\Models\Setting::getDefaultWaitingTimeMinutes(),
                'temps_attente_estime_prochain' => self::estimateWaitingTime(),
                'dernier_numero_genere' => self::where('date', $date)
                                              ->orderBy('created_at', 'desc')
                                              ->first()?->numero_ticket ?? 'Aucun',
                'principe' => 'Numérotation par service, traitement chronologique avec priorité transferts et résolution binaire'
            ],
            'repartition_par_service' => self::where('date', $date)
                                           ->join('services', 'queues.service_id', '=', 'services.id')
                                           ->selectRaw('
                                               services.nom as service_name, 
                                               services.letter_of_service, 
                                               COUNT(*) as tickets_count, 
                                               SUM(resolu) as resolus_count,
                                               SUM(CASE WHEN transferer = "new" THEN 1 ELSE 0 END) as transfers_received,
                                               SUM(CASE WHEN transferer = "transferé" THEN 1 ELSE 0 END) as transfers_sent
                                           ')
                                           ->groupBy('services.id', 'services.nom', 'services.letter_of_service')
                                           ->get()
                                           ->toArray(),
            'sequence_chronologique_collaborative' => self::where('date', $date)
                                                        ->orderBy('created_at', 'asc')
                                                        ->limit(20)
                                                        ->get(['numero_ticket', 'created_at', 'heure_d_enregistrement', 'resolu', 'transferer'])
                                                        ->map(function($ticket) {
                                                            return [
                                                                'numero' => $ticket->numero_ticket,
                                                                'heure' => $ticket->heure_d_enregistrement ?: $ticket->created_at->format('H:i:s'),
                                                                'resolu' => $ticket->resolu,
                                                                'resolu_libelle' => $ticket->resolu === 1 ? 'Résolu' : 'Non résolu',
                                                                'transfer_status' => $ticket->transferer,
                                                                'priority' => $ticket->transferer === 'new' ? 'high' : 'normal'
                                                            ];
                                                        })
                                                        ->toArray(),
            'ordre_traitement_collaboratif' => self::getChronologicalQueueWithPriority($date)
                                                   ->take(10)
                                                   ->map(function($ticket) {
                                                       return [
                                                           'numero_ticket' => $ticket->numero_ticket,
                                                           'service_name' => $ticket->service->nom ?? 'N/A',
                                                           'service_letter' => $ticket->service->letter_of_service ?? 'N/A',
                                                           'heure_arrivee' => $ticket->heure_d_enregistrement ?: $ticket->created_at->format('H:i:s'),
                                                           'priority_level' => $ticket->collaborative_priority ?? 'normal',
                                                           'priority_reason' => $ticket->priority_reason ?? 'FIFO',
                                                           'transfer_status' => $ticket->transferer,
                                                           'transferred_by' => $ticket->conseillerTransfert ? $ticket->conseillerTransfert->username : null,
                                                           'rang_collaboratif' => 'Ordre avec priorité transfert puis chronologique'
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

        Log::info('Nettoyage automatique des tickets collaboratifs', [
            'tickets_supprimés' => $deletedCount,
            'cutoff_date' => $cutoffDate->format('Y-m-d'),
            'collaborative_system' => 'maintained'
        ]);
            
        return $deletedCount;
    }

    /**
     * ✅ BOOT METHOD - MODIFIÉ pour resolu tinyint par défaut et système collaboratif
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
            
            // 🆕 NOUVEAU : transferer par défaut à "No" (ticket normal)
            if (empty($queue->transferer)) {
                $queue->transferer = 'No';
            }
        });

        static::created(function ($queue) {
            Log::info('Ticket créé avec système collaboratif', [
                'id' => $queue->id,
                'numero_ticket' => $queue->numero_ticket,
                'service' => $queue->service->nom ?? 'N/A',
                'service_letter' => $queue->letter_of_service,
                'agence_id' => $queue->id_agence,
                'position_chronologique' => $queue->position_file,
                'heure_arrivee' => $queue->heure_d_enregistrement,
                'resolu_default' => $queue->resolu,
                'transfer_status' => $queue->transferer,
                'priority_level' => $queue->getTransferPriorityLevel(),
                'ordre_concept' => 'Numérotation par service, traitement chronologique collaborative avec résolution binaire',
                'collaborative_system' => 'active'
            ]);
        });
    }

    /**
     * 🔄 TRANSFÉRER LE TICKET VERS UN AUTRE SERVICE ET/OU CONSEILLER - VERSION COLLABORATIVE
     */
    public function transferToCollaborative($newServiceId = null, $newAdvisorId = null, $reason = null, $notes = null, $fromAdvisorId = null): bool
    {
        try {
            $currentTime = now()->format('H:i:s');
            $transferDate = now()->toISOString();
            
            // 🔍 VALIDATION DES PARAMÈTRES
            if (!$newServiceId && !$newAdvisorId) {
                throw new \Exception('Au moins un service ou un conseiller de destination doit être spécifié');
            }

            if (!$reason || trim($reason) === '') {
                throw new \Exception('Le motif du transfert est obligatoire');
            }

            // 🔍 VÉRIFIER QUE LE TICKET EST EN COURS
            if ($this->statut_global !== 'en_cours') {
                throw new \Exception('Seuls les tickets en cours peuvent être transférés');
            }

            // 🔍 VALIDATION DU SERVICE DE DESTINATION
            $newService = null;
            if ($newServiceId) {
                $newService = \App\Models\Service::where('id', $newServiceId)
                                                ->where('statut', 'actif')
                                                ->first();
                
                if (!$newService) {
                    throw new \Exception('Service de destination non trouvé ou inactif');
                }
            }

            // 🔍 VALIDATION DU CONSEILLER DE DESTINATION
            $newAdvisor = null;
            if ($newAdvisorId) {
                $newAdvisor = \App\Models\User::where('id', $newAdvisorId)
                                             ->where('user_type_id', 4) // Type conseiller
                                             ->where('status_id', 2) // Actif
                                             ->first();
                
                if (!$newAdvisor) {
                    throw new \Exception('Conseiller de destination non trouvé ou inactif');
                }

                // Vérifier que le conseiller n'a pas déjà un ticket en cours
                $advisorBusy = self::where('conseiller_client_id', $newAdvisorId)
                                  ->whereDate('date', today())
                                  ->where('statut_global', 'en_cours')
                                  ->exists();

                if ($advisorBusy) {
                    throw new \Exception('Le conseiller de destination a déjà un ticket en cours');
                }
            }

            // 🔄 SAUVEGARDER L'ANCIEN CONSEILLER POUR HISTORIQUE
            $oldAdvisorId = $this->conseiller_client_id;
            $oldServiceId = $this->service_id;

            // 🔄 PRÉPARER LES NOUVELLES DONNÉES COLLABORATIVES
            $updateData = [
                'heure_transfert' => $currentTime,
                'transferer' => 'new', // ✅ COLLABORATIF : Le destinataire recevra un ticket "new" (priorité)
                'statut_global' => 'en_attente', // ✅ RETOUR EN FILE D'ATTENTE avec priorité
                'resolu' => 1, // ✅ RESET à résolu par défaut pour nouveau traitement
                'commentaire_resolution' => null, // ✅ RESET commentaire résolution
                'heure_de_fin' => null, // ✅ RESET heure de fin
                'heure_prise_en_charge' => null, // ✅ RESET heure prise en charge
                'conseiller_transfert' => $oldAdvisorId, // ✅ Sauvegarder qui a transféré
                'transfer_reason' => trim($reason), // 🆕 NOUVEAU : Motif du transfert
                'transfer_notes' => $notes ? trim($notes) : null, // 🆕 NOUVEAU : Notes du transfert
                'updated_at' => now()
            ];

            // 🎯 MISE À JOUR DU SERVICE SI SPÉCIFIÉ
            if ($newServiceId) {
                $updateData['service_id'] = $newServiceId;
                $updateData['letter_of_service'] = $newService->letter_of_service;
                
                // 🆕 GÉNÉRER UN NOUVEAU NUMÉRO DE TICKET SI CHANGEMENT DE SERVICE
                if ($oldServiceId !== $newServiceId) {
                    $newTicketNumber = self::generateTicketNumber($newServiceId, $this->date);
                    $updateData['numero_ticket'] = $newTicketNumber;
                }
            }

            // 🎯 MISE À JOUR DU CONSEILLER SI SPÉCIFIÉ
            if ($newAdvisorId) {
                $updateData['conseiller_client_id'] = $newAdvisorId;
            } else {
                // Si pas de nouveau conseiller spécifié, libérer le ticket (file d'attente générale)
                $updateData['conseiller_client_id'] = null;
            }

            // 🔄 RECALCULER LA POSITION DANS LA FILE (priorité transfert)
            $priorityPosition = self::where('date', $this->date)
                                   ->where('statut_global', 'en_attente')
                                   ->where('transferer', 'new')
                                   ->count() + 1;
            
            $updateData['position_file'] = $priorityPosition;

            // 🆕 ENRICHIR L'HISTORIQUE AVEC DÉTAILS DU TRANSFERT COLLABORATIF
            $currentHistory = $this->historique ?? [];
            $transferHistoryEntry = [
                'action' => 'collaborative_transfer',
                'timestamp' => $transferDate,
                'from_advisor_id' => $oldAdvisorId,
                'from_advisor' => $fromAdvisorId ? \App\Models\User::find($fromAdvisorId)->username : 'Inconnu',
                'to_service_id' => $newServiceId,
                'to_service' => $newService ? $newService->nom : null,
                'to_advisor_id' => $newAdvisorId,
                'to_advisor' => $newAdvisor ? $newAdvisor->username : null,
                'old_ticket_number' => $this->numero_ticket,
                'new_ticket_number' => $updateData['numero_ticket'] ?? $this->numero_ticket,
                'reason' => trim($reason),
                'notes' => $notes ? trim($notes) : null,
                'transfer_type' => $this->determineTransferType($newServiceId, $newAdvisorId),
                'new_priority_position' => $priorityPosition,
                'status_change' => 'en_cours → en_attente (transfert collaboratif avec priorité)',
                'collaborative_features' => [
                    'priority_granted' => true,
                    'transfer_status' => 'new',
                    'system_type' => 'collaborative_fifo'
                ]
            ];

            $updateData['historique'] = array_merge($currentHistory, [$transferHistoryEntry]);

            // 🔄 EFFECTUER LA MISE À JOUR
            $success = $this->update($updateData);

            if (!$success) {
                throw new \Exception('Échec de la mise à jour du ticket');
            }

            // 🔄 MARQUER L'ANCIEN TICKET COMME TRANSFÉRÉ PAR LE CONSEILLER ACTUEL
            // NOTE: Cette partie peut nécessiter une table séparée pour un suivi plus précis
            
            // 🎯 LOG DÉTAILLÉ DU TRANSFERT COLLABORATIF
            Log::info('Ticket transféré avec système collaboratif - détails complets', [
                'ticket_id' => $this->id,
                'numero_ticket' => $this->numero_ticket,
                'new_numero_ticket' => $updateData['numero_ticket'] ?? $this->numero_ticket,
                'from_advisor_id' => $oldAdvisorId,
                'to_advisor_id' => $newAdvisorId,
                'from_service_id' => $oldServiceId,
                'to_service_id' => $newServiceId,
                'transfer_reason' => $reason,
                'transfer_notes' => $notes,
                'new_priority_position' => $priorityPosition,
                'transfer_time' => $currentTime,
                'transfer_type' => $transferHistoryEntry['transfer_type'],
                'collaborative_system' => 'active',
                'priority_status' => 'Le ticket aura maintenant le statut "new" (priorité absolue)',
                'destination_will_see' => 'Ticket avec priorité maximale dans leur file'
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Erreur lors du transfert collaboratif de ticket', [
                'ticket_id' => $this->id,
                'numero_ticket' => $this->numero_ticket,
                'new_service_id' => $newServiceId,
                'new_advisor_id' => $newAdvisorId,
                'from_advisor_id' => $fromAdvisorId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'collaborative_system' => 'error_during_transfer'
            ]);
            
            return false;
        }
    }

    /**
     * 🔍 DÉTERMINER LE TYPE DE TRANSFERT
     */
    private function determineTransferType($newServiceId = null, $newAdvisorId = null): string
    {
        if ($newServiceId && $newAdvisorId) {
            return 'collaborative_service_and_advisor';
        } elseif ($newServiceId) {
            return 'collaborative_service_only';
        } elseif ($newAdvisorId) {
            return 'collaborative_advisor_only';
        } else {
            return 'unknown';
        }
    }

    /**
     * 🔍 VÉRIFIER SI LE TICKET A ÉTÉ TRANSFÉRÉ
     */
    public function wasTransferred(): bool
    {
        return in_array($this->transferer, ['new', 'transferé']);
    }

    /**
     * 🔍 OBTENIR L'HISTORIQUE DES TRANSFERTS COLLABORATIFS
     */
    public function getCollaborativeTransferHistory(): array
    {
        if (!$this->historique) {
            return [];
        }

        return array_filter($this->historique, function($entry) {
            return isset($entry['action']) && in_array($entry['action'], ['transfer', 'collaborative_transfer']);
        });
    }

    /**
     * 🔍 OBTENIR LE CONSEILLER QUI A TRANSFÉRÉ LE TICKET
     */
    public function getTransferredFrom()
    {
        if (!$this->wasTransferred() || !$this->conseiller_transfert) {
            return null;
        }

        return \App\Models\User::find($this->conseiller_transfert);
    }

    /**
     * 🆕 NOUVEAU : Obtenir les statistiques de collaboration pour un conseiller
     */
    public static function getCollaborativeStatsForAdvisor($advisorId, $date = null): array
    {
        $date = $date ?: today();

        return [
            'tickets_received_today' => self::where('conseiller_client_id', $advisorId)
                                           ->whereDate('date', $date)
                                           ->where('transferer', 'new')
                                           ->count(),
            
            'tickets_sent_today' => self::where('conseiller_transfert', $advisorId)
                                       ->whereDate('date', $date)
                                       ->where('transferer', 'transferé')
                                       ->count(),
            
            'tickets_resolved_from_transfers' => self::where('conseiller_client_id', $advisorId)
                                                    ->whereDate('date', $date)
                                                    ->where('transferer', 'new')
                                                    ->where('statut_global', 'termine')
                                                    ->where('resolu', 1)
                                                    ->count(),
            
            'collaboration_score' => self::where('conseiller_client_id', $advisorId)
                                         ->whereDate('date', $date)
                                         ->where('transferer', 'new')
                                         ->count() +
                                    self::where('conseiller_transfert', $advisorId)
                                         ->whereDate('date', $date)
                                         ->where('transferer', 'transferé')
                                         ->count()
        ];
    }

    /**
     * 🆕 NOUVEAU : Obtenir les tickets avec priorité pour un service donné
     */
    public static function getPriorityTicketsForService($serviceId, $date = null)
    {
        $date = $date ?: today();
        
        return self::where('service_id', $serviceId)
                   ->whereDate('date', $date)
                   ->where('statut_global', 'en_attente')
                   ->where('transferer', 'new')
                   ->orderBy('created_at', 'asc')
                   ->with(['conseillerTransfert:id,username,email'])
                   ->get();
    }
}