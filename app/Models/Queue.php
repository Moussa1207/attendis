<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class Queue extends Model

{
    public const STATUT_EN_ATTENTE = 'en_attente';
public const STATUT_EN_COURS   = 'en_cours';
public const STATUT_TERMINE    = 'termine';

public const TRANSFER_IN   = 'new';       // reçu chez le destinataire (prioritaire)
public const TRANSFER_OUT  = 'transfere'; // forme canonique (ASCII, sans accent)
public const TRANSFER_NONE = 'no';

// Normalise toute valeur entrante/sortante du champ `transferer`
private static function normalizeTransferFlag($v): string
{
    $v = strtolower(trim((string)$v));
    return match ($v) {
        'new', 'reçu', 'recu'                       => self::TRANSFER_IN,
        'transfere', 'transféré', 'transferé', 'yes'=> self::TRANSFER_OUT,
        'no', 'non', ''                              => self::TRANSFER_NONE,
        default                                      => self::TRANSFER_NONE,
    };
}

// Mutator Eloquent : dès qu’on assigne $model->transferer = ..., on normalise
public function setTransfererAttribute($value): void
{
    $this->attributes['transferer'] = self::normalizeTransferFlag($value);
}

    use HasFactory;


    /**
     * ✅ ATTRIBUTS REMPLISSABLES
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
        'resolu', // tinyint (0/1)
        'commentaire_resolution',
        'transferer', // "new", "transferé", "No"
        'debut',
        'numero_ticket',
        'position_file',
        'temps_attente_estime',
        'statut_global',
        'historique',
        'created_by_ip',
        'notes_internes',
        'transfer_reason',
        'transfer_notes',
    ];

    /**
     * ✅ CASTS
     */
    protected $casts = [
        'date' => 'date',
        'historique' => 'json',
        'position_file' => 'integer',
        'temps_attente_estime' => 'integer',
        'resolu' => 'integer', // 0/1
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * ✅ RELATIONS
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
     * ✅ SCOPES
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

    // 🆕 Scopes système collaboratif
    public function scopeTransferredToMe($query)
    {
        return $query->where('transferer', 'new');
    }

    public function scopeTransferredByMe($query, $userId)
    {
        // Un ticket "envoyé par moi" = j'apparais comme expéditeur.
   return $query->where('conseiller_transfert', $userId);
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
     * ✅ resolu tinyint
     */
    public function isResolu(): bool
    {
        return (int)$this->resolu === 1;
    }

    public function isNonResolu(): bool
    {
        return (int)$this->resolu === 0;
    }

    public function getResoluLibelle(): string
    {
        return match((int)$this->resolu) {
            1 => 'Résolu',
            0 => 'Non résolu',
            default => 'Inconnu'
        };
    }

    /**
     * 🆕 Système collaboratif
     */
    public function isTransferredToMe(): bool
    {
        return self::normalizeTransferFlag($this->transferer) === self::TRANSFER_IN;
    }

    public function isTransferredByMe($userId): bool
    {
        return (int)$this->conseiller_transfert === (int)$userId;
    }

    public function isNormalTicket(): bool
    {
       $flag = self::normalizeTransferFlag($this->transferer);
       return $this->transferer === null || $flag === self::TRANSFER_NONE;
    }

    public function hasTransferPriority(): bool
    {
        return self::normalizeTransferFlag($this->transferer) === self::TRANSFER_IN;
    }

    public function getTransferStatus(): string
    {
        return match (self::normalizeTransferFlag($this->transferer)) {
       self::TRANSFER_IN   => 'Reçu par transfert',
      self::TRANSFER_OUT  => 'Transféré par moi', // conservé si jamais tu l’utilises plus tard
        default             => 'Normal',
   };
    }

    public function getTransferPriorityLevel(): string
    {
        $flag = self::normalizeTransferFlag($this->transferer);
   return match ($flag) {
      self::TRANSFER_IN  => 'high',
      self::TRANSFER_OUT => 'blocked',
      default            => 'normal',
   };
    }

    /**
     * 🆕 GÉNÉRATION AUTOMATIQUE DU NUMÉRO (par service et par jour)
     * - utilise MAX du suffixe pour être robuste (pas COUNT)
     * - supporte un préfixe de longueur > 1 (letter_of_service)
     */
    public static function generateTicketNumber($serviceId, $date = null): string
    {
        $date = $date ?: today();

        $service = Service::find($serviceId);
        if (!$service) {
            throw new \Exception('Service introuvable pour génération ticket');
        }

        $prefix = (string) $service->letter_of_service;
        $offset = strlen($prefix) + 1; // début du suffixe numérique
        $lastNumber = self::where('service_id', $serviceId)
            ->whereDate('date', $date)
            ->where('numero_ticket', 'LIKE', $prefix . '%')
            ->selectRaw("MAX(CAST(SUBSTRING(numero_ticket, {$offset}) AS UNSIGNED)) as max_num")
            ->value('max_num');

        $next = ((int)$lastNumber) + 1; // null => 0
        return $prefix . str_pad($next, 3, '0', STR_PAD_LEFT);
    }

    /**
     * 🆕 POSITION DANS LA FILE (globale jour)
     */
    public static function calculateQueuePosition($date = null): int
    {
        $date = $date ?: today();
        $currentPosition = self::whereDate('date', $date)
                              ->where('statut_global', 'en_attente')
                              ->count();
        return $currentPosition + 1;
    }

    /**
     * ✅ ESTIMATION DU TEMPS D'ATTENTE
     */
    public static function estimateWaitingTime($position = null, $hasPriority = false): int
    {
        $adminConfiguredTime = \App\Models\Setting::getDefaultWaitingTimeMinutes();

        if ($position === null) {
            $position = self::calculateQueuePosition();
        }

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
     * 🆕 CRÉATION D'UN NOUVEAU TICKET (sécurisée)
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

            // 🔒 Verrouille la plage (service + date) pour éviter un doublon de numéro
            DB::table('queues')
                ->where('service_id', $serviceId)
                ->whereDate('date', $date)
                ->lockForUpdate()
                ->get();

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
                'resolu' => 0,
                'transferer' => 'No',
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

            Log::info('Nouveau ticket créé', [
                'ticket_id' => $ticket->id,
                'numero_ticket' => $ticket->numero_ticket,
                'service_name' => $service->nom,
            ]);

            return $ticket;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création ticket', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * ✅ ÉTATS
     */
    public function isEnAttente(): bool { return $this->statut_global === 'en_attente'; }
    public function isEnCours(): bool { return $this->statut_global === 'en_cours'; }
    public function isTermine(): bool { return $this->statut_global === 'termine'; }
    public function isTransfere(): bool { 
        $flag = self::normalizeTransferFlag($this->transferer);
        return in_array($flag, [self::TRANSFER_IN, self::TRANSFER_OUT], true);
     }

    /**
     * ✅ PRISE EN CHARGE
     * — protège les tickets transférés "new" réservés à un autre conseiller
     */
    public function priseEnCharge($conseillerId): bool
    {
        try {
            // Exclusivité pour les tickets transférés en priorité
            if ($this->hasTransferPriority() && $this->conseiller_client_id && (int)$this->conseiller_client_id !== (int)$conseillerId) {
                throw new \Exception('Ticket réservé à un autre conseiller.');
            }

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
                        'conseiller_id' => $conseillerId,
                        'transfer_context' => [
                            'was_transferred' => $this->isTransferredToMe(),
                            'transfer_status' => $this->transferer,
                            'priority_level' => $this->getTransferPriorityLevel()
                        ]
                    ]
                ])
            ]);

            Log::info('Ticket pris en charge', [
                'ticket_id' => $this->id,
                'numero_ticket' => $this->numero_ticket,
                'conseiller_id' => $conseillerId,
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
     * ✅ TERMINER (Traiter/Refuser) — sort de la file
     */
    public function terminer($resolu = 1, $commentaire = null): bool
    {
        try {
            $currentTime = now()->format('H:i:s');

            if ((int)$resolu === 0 && empty($commentaire)) {
                throw new \Exception('Commentaire obligatoire pour les tickets non résolus');
            }

            $this->update([
                'heure_de_fin' => $currentTime,
                'statut_global' => 'termine',
                'resolu' => (int)$resolu,
                'commentaire_resolution' => $commentaire,

                // 🧹 Nettoyage file — empêche toute réapparition
                'transferer' => 'No',
                'position_file' => null,
            ]);

            $this->update([
                'historique' => array_merge($this->historique ?? [], [
                    [
                        'action' => 'terminer',
                        'timestamp' => now()->toISOString(),
                        'resolu' => (int)$resolu,
                        'resolu_libelle' => (int)$resolu === 1 ? 'Résolu' : 'Non résolu',
                        'commentaire' => $commentaire,
                        'collaborative_context' => [
                            'was_transferred' => $this->isTransferredToMe(),
                            'transfer_status' => $this->transferer,
                            'transferred_by' => $this->conseiller_transfert
                        ]
                    ]
                ])
            ]);

            Log::info('Ticket terminé', [
                'ticket_id' => $this->id,
                'numero_ticket' => $this->numero_ticket,
                'resolu' => (int)$resolu,
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
     * 🆕 Données API/Frontend
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
            'prenom' => $this->prenom,
            'telephone' => $this->telephone,
            'commentaire' => $this->commentaire,
            'date' => $this->date?->format('d/m/Y'),
            'heure' => $this->formatHeureEnregistrement(),
            'heure_d_enregistrement' => $this->heure_d_enregistrement,
            'position' => $this->position_file,
            'temps_attente_estime' => $this->temps_attente_estime,
            'statut' => $this->statut_global,
            'statut_libelle' => $this->getStatutLibelle(),
            'resolu' => $this->resolu,
            'resolu_libelle' => $this->getResoluLibelle(),
            'commentaire_resolution' => $this->commentaire_resolution,
            'is_en_attente' => $this->isEnAttente(),
            'is_en_cours' => $this->isEnCours(),
            'is_termine' => $this->isTermine(),
            'is_resolu' => $this->isResolu(),
            'is_non_resolu' => $this->isNonResolu(),
            'conseiller' => $this->conseillerClient ? $this->conseillerClient->username : null,
            'heure_prise_en_charge' => $this->heure_prise_en_charge,
            'heure_de_fin' => $this->heure_de_fin,
            'created_at' => $this->created_at?->format('d/m/Y H:i:s'),
            'created_at_iso' => $this->created_at?->toISOString(),
            'transferer' => $this->transferer,
            'is_transferred_to_me' => $this->isTransferredToMe(),
            'has_transfer_priority' => $this->hasTransferPriority(),

            'queue_type' => 'collaborative_service_numbering_chronological_processing',
            'arrival_order' => $this->created_at?->format('H:i:s')
        ];
    }

    public function toTicketArrayWithTransfer(): array
    {
        $basicArray = $this->toTicketArray();

        $transferInfo = [
            'transfer_status' => $this->transferer,
            'transfer_status_label' => $this->getTransferStatus(),
            'priority_level' => $this->getTransferPriorityLevel(),
            'is_transferred_to_me' => $this->isTransferredToMe(),
            'is_transferred_by_me' => false,
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
     * ✅ Format heure
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
     * ✅ Libellé du statut
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
     * 🔄 FILE CHRONOLOGIQUE (priorité transferts)
     */
    public static function getChronologicalQueueWithPriority($date = null): \Illuminate\Support\Collection
    {
        $date = $date ?: today();

        $priorityTickets = self::whereDate('date', $date)
                              ->where('statut_global', 'en_attente')
                              ->where('transferer', 'new')
                              ->orderBy('created_at', 'asc')
                              ->with(['service', 'agence', 'conseillerTransfert'])
                              ->get();

        $normalTickets = self::whereDate('date', $date)
                            ->where('statut_global', 'en_attente')
                            ->where(function($query) {
                                $query->whereNull('transferer')
                                      ->orWhere('transferer', 'No')
                                      ->orWhere('transferer', 'no');
                            })
                            ->orderBy('created_at', 'asc')
                            ->with(['service', 'agence'])
                            ->get();

        $priorityTickets->each(function($ticket) {
            $ticket->collaborative_priority = 'high';
            $ticket->priority_reason = 'Reçu par transfert';
        });

        $normalTickets->each(function($ticket) {
            $ticket->collaborative_priority = 'normal';
            $ticket->priority_reason = 'FIFO chronologique';
        });

        return $priorityTickets->concat($normalTickets);
    }

    /**
     * 🔄 PROCHAIN TICKET COLLABORATIF (global)
     */
    public static function getNextTicketCollaborative($date = null)
    {
        $date = $date ?: today();

        $nextTicket = self::whereDate('date', $date)
                         ->where('statut_global', 'en_attente')
                         ->where('transferer', 'new')
                         ->orderBy('created_at', 'asc')
                         ->first();

        if ($nextTicket) {
            $nextTicket->selection_reason = 'Priorité transfert';
            return $nextTicket;
        }

        $nextTicket = self::whereDate('date', $date)
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
     * 🔄 FILE PAR SERVICE (priorité transferts)
     */
    public static function getServiceQueueCollaborative($serviceId, $date = null): \Illuminate\Support\Collection
    {
        $date = $date ?: today();

        $priorityTickets = self::where('service_id', $serviceId)
                              ->whereDate('date', $date)
                              ->where('statut_global', 'en_attente')
                              ->where('transferer', 'new')
                              ->orderBy('created_at', 'asc')
                              ->with(['service', 'agence', 'conseillerTransfert'])
                              ->get();

        $normalTickets = self::where('service_id', $serviceId)
                            ->whereDate('date', $date)
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
     * 🔄 STATS SERVICE
     */
    public static function getServiceStats($serviceId, $date = null): array
    {
        $date = $date ?: today();

        $baseStats = [
            'total_tickets' => self::where('service_id', $serviceId)->whereDate('date', $date)->count(),
            'en_attente' => self::where('service_id', $serviceId)->whereDate('date', $date)->where('statut_global', 'en_attente')->count(),
            'en_cours' => self::where('service_id', $serviceId)->whereDate('date', $date)->where('statut_global', 'en_cours')->count(),
            'termines' => self::where('service_id', $serviceId)->whereDate('date', $date)->where('statut_global', 'termine')->count(),

            'resolus' => self::where('service_id', $serviceId)->whereDate('date', $date)->where('resolu', 1)->count(),
            'non_resolus' => self::where('service_id', $serviceId)->whereDate('date', $date)->where('resolu', 0)->count(),

            'tickets_transferred_in' => self::where('service_id', $serviceId)->whereDate('date', $date)->where('transferer', 'new')->count(),
            'tickets_transferred_out' => self::where('service_id', $serviceId)->whereDate('date', $date)->whereNotNull('conseiller_transfert')->count(),
            'normal_tickets' => self::where('service_id', $serviceId)->whereDate('date', $date)->normalTickets()->count(),

            'temps_attente_moyen' => self::where('service_id', $serviceId)->whereDate('date', $date)->avg('temps_attente_estime') ?? 0,
            'dernier_ticket' => self::where('service_id', $serviceId)->whereDate('date', $date)->orderBy('created_at', 'desc')->first(),
        ];

        $prochains_tickets = self::getServiceQueueCollaborative($serviceId, $date)->take(5);

        return array_merge($baseStats, [
            'file_chronologique_collaborative' => [
                'position_globale_actuelle' => self::calculateQueuePosition($date),
                'total_attente_globale' => self::whereDate('date', $date)->where('statut_global', 'en_attente')->count(),
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
                'resolution_stats' => [
                    'taux_resolution' => $baseStats['total_tickets'] > 0 ? round(($baseStats['resolus'] / $baseStats['total_tickets']) * 100, 2) : 0,
                    'tickets_resolus' => $baseStats['resolus'],
                    'tickets_non_resolus' => $baseStats['non_resolus']
                ],
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
     * 🆕 STATS GLOBALES
     */
    public static function getGlobalQueueStats($date = null): array
    {
        $date = $date ?: today();

        $totalToday = self::whereDate('date', $date)->count();
        $enAttente = self::whereDate('date', $date)->where('statut_global', 'en_attente')->count();
        $enCours = self::whereDate('date', $date)->where('statut_global', 'en_cours')->count();
        $termines = self::whereDate('date', $date)->where('statut_global', 'termine')->count();
        $resolus = self::whereDate('date', $date)->where('resolu', 1)->count();
        $nonResolus = self::whereDate('date', $date)->where('resolu', 0)->count();

        $transfersReceived = self::whereDate('date', $date)->where('transferer', 'new')->count();
        $transfersSent = self::whereDate('date', $date)->whereNotNull('conseiller_transfert')->count();
        $normalTickets = self::whereDate('date', $date)->normalTickets()->count();

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

                'transfers_received' => $transfersReceived,
                'transfers_sent' => $transfersSent,
                'normal_tickets' => $normalTickets,
                'collaboration_rate' => $totalToday > 0 ? round((($transfersReceived + $transfersSent) / $totalToday) * 100, 2) : 0,

                'prochaine_position' => self::calculateQueuePosition($date),
                'temps_attente_configure' => \App\Models\Setting::getDefaultWaitingTimeMinutes(),
                'temps_attente_estime_prochain' => self::estimateWaitingTime(),
                'dernier_numero_genere' => self::whereDate('date', $date)
                                              ->orderBy('created_at', 'desc')
                                              ->first()?->numero_ticket ?? 'Aucun',
                'principe' => 'Numérotation par service, traitement chronologique avec priorité transferts et résolution binaire'
            ],
            'repartition_par_service' => self::whereDate('date', $date)
                                           ->join('services', 'queues.service_id', '=', 'services.id')
                                           ->selectRaw('
                                               services.nom as service_name, 
                                               services.letter_of_service, 
                                               COUNT(*) as tickets_count, 
                                               SUM(resolu) as resolus_count,
                                               SUM(CASE WHEN transferer = "new" THEN 1 ELSE 0 END) as transfers_received,
                                               SUM(CASE WHEN conseiller_transfert IS NOT NULL THEN 1 ELSE 0 END) as transfers_sent
                                           ')
                                           ->groupBy('services.id', 'services.nom', 'services.letter_of_service')
                                           ->get()
                                           ->toArray(),
            'sequence_chronologique_collaborative' => self::whereDate('date', $date)
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

    public function getTransfererAttribute($value)
{
    return self::normalizeTransferFlag($value);
}

    /**
     * ✅ NETTOYAGE AUTOMATIQUE
     */
    public static function cleanOldTickets($daysToKeep = 30): int
    {
        $cutoffDate = now()->subDays($daysToKeep);

        $deletedCount = self::where('date', '<', $cutoffDate)
                           ->where('statut_global', 'termine')
                           ->delete();

        Log::info('Nettoyage automatique des tickets', [
            'tickets_supprimés' => $deletedCount,
            'cutoff_date' => $cutoffDate->format('Y-m-d'),
        ]);

        return $deletedCount;
    }

    /**
     * ✅ BOOT : valeurs par défaut
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

            if (!isset($queue->resolu)) {
                $queue->resolu = 0;
            }

            if (empty($queue->transferer)) {
                $queue->transferer = 'No';
            }
        });

        static::created(function ($queue) {
            Log::info('Ticket créé', [
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
            ]);
        });
    }

    /**
     * 🔄 TRANSFERT COLLABORATIF (sécurisé pour changement de service)
     */
    public function transferToCollaborative($newServiceId = null, $newAdvisorId = null, $reason = null, $notes = null, $fromAdvisorId = null): bool
    {
        try {
            $currentTime = now()->format('H:i:s');
            $transferDate = now()->toISOString();

            if (!$newServiceId && !$newAdvisorId) {
                throw new \Exception('Au moins un service ou un conseiller de destination doit être spécifié');
            }

            if (!$reason || trim($reason) === '') {
                throw new \Exception('Le motif du transfert est obligatoire');
            }

            if ($this->statut_global !== 'en_cours') {
                throw new \Exception('Seuls les tickets en cours peuvent être transférés');
            }

            $newService = null;
            if ($newServiceId) {
                $newService = \App\Models\Service::where('id', $newServiceId)
                                                ->where('statut', 'actif')
                                                ->first();
                if (!$newService) {
                    throw new \Exception('Service de destination non trouvé ou inactif');
                }
            }

            $newAdvisor = null;
            if ($newAdvisorId) {
                $newAdvisor = \App\Models\User::where('id', $newAdvisorId)
                                             ->where('user_type_id', 4)
                                             ->where('status_id', 2)
                                             ->first();
                if (!$newAdvisor) {
                    throw new \Exception('Conseiller de destination non trouvé ou inactif');
                }

                $advisorBusy = self::where('conseiller_client_id', $newAdvisorId)
                                  ->whereDate('date', today())
                                  ->where('statut_global', 'en_cours')
                                  ->exists();
                if ($advisorBusy) {
                    throw new \Exception('Le conseiller de destination a déjà un ticket en cours');
                }
            }

            $oldAdvisorId = $this->conseiller_client_id;
            $oldServiceId = $this->service_id;

            $updateData = [
                'heure_transfert' => $currentTime,
                'transferer' => 'new', // priorité chez destinataire
                'statut_global' => 'en_attente',
                'resolu' => 0,
                'commentaire_resolution' => null,
                'heure_de_fin' => null,
                'heure_prise_en_charge' => null,
                'conseiller_transfert' => $oldAdvisorId,
                'transfer_reason' => trim($reason),
                'transfer_notes' => $notes ? trim($notes) : null,
                'updated_at' => now()
            ];

            // Service de destination -> régénérer un numéro (sécurisé)
            if ($newServiceId) {
                $updateData['service_id'] = $newServiceId;
                $updateData['letter_of_service'] = $newService->letter_of_service;

                if ($oldServiceId !== $newServiceId) {
                    // 🔒 verrou avant nouvelle numérotation
                    DB::table('queues')
                        ->where('service_id', $newServiceId)
                        ->whereDate('date', $this->date)
                        ->lockForUpdate()
                        ->get();

                    $newTicketNumber = self::generateTicketNumber($newServiceId, $this->date);
                    $updateData['numero_ticket'] = $newTicketNumber;
                }
            }

            if ($newAdvisorId) {
                $updateData['conseiller_client_id'] = $newAdvisorId;
            } else {
                $updateData['conseiller_client_id'] = null; // libéré dans la file générale (mais "new")
            }

            // Repositionnement dans la sous-file prioritaire "new"
            $priorityPosition = self::whereDate('date', $this->date)
                                   ->where('statut_global', 'en_attente')
                                   ->where('transferer', 'new')
                                   ->count() + 1;

            $updateData['position_file'] = $priorityPosition;

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

            $success = $this->update($updateData);
            if (!$success) {
                throw new \Exception('Échec de la mise à jour du ticket');
            }

            Log::info('Ticket transféré (collaboratif)', [
                'ticket_id' => $this->id,
                'numero_ticket' => $this->numero_ticket,
                'new_numero_ticket' => $updateData['numero_ticket'] ?? $this->numero_ticket,
                'from_service_id' => $oldServiceId,
                'to_service_id' => $newServiceId,
                'to_advisor_id' => $newAdvisorId,
                'priority_position' => $priorityPosition,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Erreur transfert collaboratif', [
                'ticket_id' => $this->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    private function determineTransferType($newServiceId = null, $newAdvisorId = null): string
    {
        if ($newServiceId && $newAdvisorId) return 'collaborative_service_and_advisor';
        if ($newServiceId) return 'collaborative_service_only';
        if ($newAdvisorId) return 'collaborative_advisor_only';
        return 'unknown';
    }

    public function wasTransferred(): bool
    {
        $flag = self::normalizeTransferFlag($this->transferer);
        return in_array($flag, [self::TRANSFER_IN, self::TRANSFER_OUT], true);
    }

    public function getCollaborativeTransferHistory(): array
    {
        if (!$this->historique) return [];
        return array_filter($this->historique, function($entry) {
            return isset($entry['action']) && in_array($entry['action'], ['transfer', 'collaborative_transfer'], true);
        });
    }

    public function getTransferredFrom()
    {
        if (!$this->wasTransferred() || !$this->conseiller_transfert) {
            return null;
        }
        return \App\Models\User::find($this->conseiller_transfert);
    }

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
 // Ticket en cours pour un conseiller (fiable après refresh)
public static function getCurrentTicketForAdvisor(int $advisorId, $date = null): ?self
{
    $date = $date ?: today();

    //  Cas nominal
    $ticket = self::whereDate('date', $date)
        ->where('conseiller_client_id', $advisorId)
        ->where('statut_global', self::STATUT_EN_COURS)
        ->whereNull('heure_de_fin')                       // pas fini
        ->orderByDesc('heure_prise_en_charge')            // le + récent
        ->first();

    if ($ticket) return $ticket;

    //  Fallback tolérant (héritage) : si un ancien code a oublié le statut
    $ticket = self::whereDate('date', $date)
        ->where('conseiller_client_id', $advisorId)
        ->where('debut', 'Yes')                           // démarré
        ->whereNull('heure_de_fin')
        ->orderByDesc('updated_at')
        ->first();

    return $ticket ?: null;
}

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
