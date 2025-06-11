<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'username', 'email', 'password', 'mobile_number', 'company', 'user_type_id', 'status_id',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ===============================================
    // RELATIONS
    // ===============================================

    /**
     * Relation avec le model UserType
     */
    public function userType()
    {
        return $this->belongsTo(UserType::class);
    }

    /**
     * Relation avec le model Status
     */
    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * Relation : Utilisateurs créés par cet admin
     */
    public function createdUsers()
    {
        return $this->hasMany(AdministratorUser::class, 'administrator_id');
    }

    /**
     * Relation : Admin qui a créé cet utilisateur
     */
    public function createdBy()
    {
        return $this->hasOne(AdministratorUser::class, 'user_id');
    }

    // ===============================================
    // MÉTHODES UTILITAIRES - TYPE ET STATUT
    // ===============================================

    /**
     * Vérifier si l'utilisateur est administrateur
     */
    public function isAdmin(): bool
    {
        return $this->user_type_id === 1;
    }

    /**
     * NOUVEAU : Vérifier si l'utilisateur est Poste Ecran
     */
    public function isEcranUser(): bool
    {
        return $this->user_type_id === 2;
    }

    /**
     * NOUVEAU : Vérifier si l'utilisateur est Poste Accueil
     */
    public function isAccueilUser(): bool
    {
        return $this->user_type_id === 3;
    }

    /**
     * NOUVEAU : Vérifier si l'utilisateur est Poste Conseiller
     */
    public function isConseillerUser(): bool
    {
        return $this->user_type_id === 4;
    }

    /**
     * NOUVEAU : Vérifier si l'utilisateur est un utilisateur normal (pas admin)
     */
    public function isNormalUser(): bool
    {
        return in_array($this->user_type_id, [2, 3, 4]);
    }

    /**
     * Vérifier si l'utilisateur est actif
     */
    public function isActive(): bool
    {
        return $this->status_id === 2;
    }

    /**
     * Vérifier si l'utilisateur est inactif
     */
    public function isInactive(): bool
    {
        return $this->status_id === 1;
    }

    /**
     * Vérifier si l'utilisateur est suspendu
     */
    public function isSuspended(): bool
    {
        return $this->status_id === 3;
    }

    // ===============================================
    // MÉTHODES POUR CHANGER LE STATUT
    // ===============================================

    /**
     * Activer l'utilisateur
     */
    public function activate(): bool
    {
        return $this->update(['status_id' => 2]);
    }

    /**
     * Désactiver l'utilisateur
     */
    public function deactivate(): bool
    {
        return $this->update(['status_id' => 1]);
    }

    /**
     * Suspendre l'utilisateur
     */
    public function suspend(): bool
{
    try {
        // ✅ MISE À JOUR FORCÉE EN BASE DE DONNÉES
        $result = $this->update(['status_id' => 3]);
        
        if ($result) {
            // ✅ RECHARGER DEPUIS LA BASE POUR VÉRIFICATION
            $this->refresh();
            
            // ✅ LOG DE SUCCÈS
            \Log::info('User suspended successfully', [
                'user_id' => $this->id,
                'username' => $this->username,
                'old_status' => 'Before suspension',
                'new_status_id' => $this->status_id,
                'new_status_name' => $this->getStatusName(),
                'is_suspended' => $this->isSuspended(),
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);
            
            return true;
        }
        
        return false;
        
    } catch (\Exception $e) {
        \Log::error('Error suspending user', [
            'user_id' => $this->id,
            'username' => $this->username,
            'error' => $e->getMessage()
        ]);
        
        return false;
    }
}

    // ===============================================
    // NOUVELLES MÉTHODES POUR LE CHANGEMENT DE MOT DE PASSE OBLIGATOIRE
    // ===============================================

    /**
     * Vérifier si l'utilisateur doit changer son mot de passe
     */
    public function mustChangePassword(): bool
    {
        // Si l'utilisateur a été créé par un admin et n'a pas encore changé son mot de passe
        $adminRelation = $this->createdBy;
        return $adminRelation && $adminRelation->password_reset_required;
    }

    /**
     * Marquer que l'utilisateur a changé son mot de passe
     */
    public function markPasswordChanged(): bool
    {
        $adminRelation = $this->createdBy;
        if ($adminRelation) {
            $adminRelation->markPasswordChanged();
            return true;
        }
        return false;
    }

    // ===============================================
    // MÉTHODES AMÉLIORÉES POUR OBTENIR LES NOMS ET INFORMATIONS
    // ===============================================

    /**
     * AMÉLIORÉ : Obtenir le nom du type d'utilisateur
     */
    public function getTypeName(): string
    {
        return match($this->user_type_id) {
            1 => 'Administrateur',
            2 => 'Poste Ecran',
            3 => 'Poste Accueil',
            4 => 'Poste Conseiller',
            default => $this->userType->name ?? 'Non défini'
        };
    }

    /**
     * AMÉLIORÉ : Obtenir l'icône selon le type avec fallback
     */
    public function getTypeIcon(): string
    {
        return match($this->user_type_id) {
            1 => 'shield',      // Administrateur
            2 => 'monitor',     // Poste Ecran
            3 => 'home',        // Poste Accueil
            4 => 'users',       // Poste Conseiller
            default => 'user'
        };
    }

    /**
     * NOUVEAU : Obtenir la couleur du badge selon le type
     */
    public function getTypeBadgeColor(): string
    {
        return match($this->user_type_id) {
            1 => 'primary',     // Administrateur (bleu)
            2 => 'info',        // Poste Ecran (cyan)
            3 => 'success',     // Poste Accueil (vert)
            4 => 'warning',     // Poste Conseiller (orange)
            default => 'secondary'
        };
    }

    /**
     * NOUVEAU : Obtenir le rôle du formulaire depuis le type
     */
    public function getUserRole(): string
    {
        return match($this->user_type_id) {
            1 => 'admin',
            2 => 'ecran',
            3 => 'accueil', 
            4 => 'conseiller',
            default => 'unknown'
        };
    }

    /**
     * NOUVEAU : Obtenir la description courte du type
     */
    public function getTypeShortDescription(): string
    {
        return match($this->user_type_id) {
            1 => 'Admin système',
            2 => 'Interface écran',
            3 => 'Réception',
            4 => 'Support client',
            default => 'Utilisateur'
        };
    }

    /**
     * NOUVEAU : Obtenir l'emoji du type
     */
    public function getTypeEmoji(): string
    {
        return match($this->user_type_id) {
            1 => '🛡️',  // Administrateur
            2 => '🖥️',  // Poste Ecran
            3 => '🏢',  // Poste Accueil
            4 => '👥',  // Poste Conseiller
            default => '👤'
        };
    }

    /**
     * Obtenir le nom du statut
     */
    public function getStatusName(): string
    {
        return match($this->status_id) {
            1 => 'Inactif',
            2 => 'Actif',
            3 => 'Suspendu',
            default => $this->status->name ?? 'Non défini'
        };
    }

    /**
     * AMÉLIORÉ : Obtenir la couleur du badge selon le statut
     */
    public function getStatusBadgeColor(): string
    {
        return match($this->status_id) {
            1 => 'warning',   // Inactif (orange)
            2 => 'success',   // Actif (vert)
            3 => 'danger',    // Suspendu (rouge)
            default => 'secondary'
        };
    }

    /**
     * NOUVEAU : Obtenir les informations complètes du type
     */
    public function getTypeInfo(): array
    {
        return [
            'id' => $this->user_type_id,
            'name' => $this->getTypeName(),
            'short_description' => $this->getTypeShortDescription(),
            'role' => $this->getUserRole(),
            'icon' => $this->getTypeIcon(),
            'badge_color' => $this->getTypeBadgeColor(),
            'emoji' => $this->getTypeEmoji(),
            'is_admin' => $this->isAdmin(),
            'is_normal_user' => $this->isNormalUser(),
        ];
    }

    /**
     * NOUVEAU : Obtenir les informations complètes du statut
     */
    public function getStatusInfo(): array
    {
        return [
            'id' => $this->status_id,
            'name' => $this->getStatusName(),
            'badge_color' => $this->getStatusBadgeColor(),
            'is_active' => $this->isActive(),
            'is_inactive' => $this->isInactive(),
            'is_suspended' => $this->isSuspended(),
        ];
    }

    // ===============================================
    // MÉTHODES DE TRAÇABILITÉ
    // ===============================================

    /**
     * Obtenir l'administrateur qui a créé cet utilisateur
     */
    public function getCreator(): ?User
    {
        return AdministratorUser::getUserCreator($this->id);
    }

    /**
     * Obtenir tous les utilisateurs créés par cet admin
     */
    public function getCreatedUsers()
    {
        return AdministratorUser::getUsersCreatedBy($this->id);
    }

    /**
     * Vérifier si cet admin a créé des utilisateurs
     */
    public function hasCreatedUsers(): bool
    {
        return $this->createdUsers()->count() > 0;
    }

    /**
     * Vérifier si cet utilisateur a été créé par un admin
     */
    public function wasCreatedByAdmin(): bool
    {
        return $this->createdBy()->exists();
    }

    /**
     * AMÉLIORÉ : Obtenir les informations de création
     */
    public function getCreationInfo(): ?array
    {
        $creator = $this->getCreator();
        return $creator ? [
            'created_by' => $creator->username,
            'created_by_id' => $creator->id,
            'created_by_email' => $creator->email,
            'created_by_company' => $creator->company,
            'created_by_type' => $creator->getTypeName(),
            'created_at' => $this->created_at,
            'created_at_formatted' => $this->created_at->format('d/m/Y à H:i'),
            'created_at_human' => $this->created_at->diffForHumans(),
            'creation_method' => $this->createdBy->creation_method ?? 'manual',
            'creation_notes' => $this->createdBy->creation_notes ?? null,
        ] : null;
    }

    // ===============================================
    // MÉTHODES DE SÉCURITÉ ET VALIDATION
    // ===============================================

    /**
     * Vérifier si cet utilisateur peut être supprimé
     */
    public function canBeDeleted(): bool
    {
        // Ne pas supprimer le dernier admin actif
        if ($this->isAdmin()) {
            $activeAdmins = self::where('user_type_id', 1)->where('status_id', 2)->count();
            return $activeAdmins > 1;
        }
        
        return true;
    }

    /**
     * Vérifier si cet utilisateur peut être suspendu
     */
    public function canBeSuspended(): bool
    {
        // Ne pas suspendre le dernier admin actif
        if ($this->isAdmin() && $this->isActive()) {
            $activeAdmins = self::where('user_type_id', 1)->where('status_id', 2)->count();
            return $activeAdmins > 1;
        }
        
        return true;
    }

    /**
     * NOUVEAU : Vérifier si l'utilisateur peut changer de type
     */
    public function canChangeType(): bool
    {
        // Un admin ne peut pas perdre ses privilèges s'il est le dernier admin actif
        if ($this->isAdmin()) {
            $activeAdmins = self::where('user_type_id', 1)->where('status_id', 2)->count();
            return $activeAdmins > 1;
        }
        
        return true;
    }

    // ===============================================
    // GESTION DES MOTS DE PASSE TEMPORAIRES
    // ===============================================

    /**
     * Générer un mot de passe temporaire sécurisé
     * UTILISÉ par UserManagementController
     */
    public static function generateSecureTemporaryPassword(int $length = 12): string
    {
        // Caractères pour le mot de passe
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $symbols = '@#$%&*!?';
        
        // Assurer au moins un caractère de chaque type
        $password = '';
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $symbols[random_int(0, strlen($symbols) - 1)];
        
        // Compléter avec des caractères aléatoires
        $allChars = $lowercase . $uppercase . $numbers . $symbols;
        for ($i = 4; $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }
        
        // Mélanger le mot de passe
        return str_shuffle($password);
    }

    /**
     * Vérifier si l'utilisateur nécessite un reset password
     */
    public function requiresPasswordReset(): bool
    {
        $relation = $this->createdBy;
        return $relation && $relation->requiresPasswordReset();
    }

    // ===============================================
    // SCOPES POUR LES REQUÊTES
    // ===============================================

    /**
     * Scope pour les utilisateurs actifs
     */
    public function scopeActive($query)
    {
        return $query->where('status_id', 2);
    }

    /**
     * Scope pour les utilisateurs inactifs
     */
    public function scopeInactive($query)
    {
        return $query->where('status_id', 1);
    }

    /**
     * Scope pour les utilisateurs suspendus
     */
    public function scopeSuspended($query)
    {
        return $query->where('status_id', 3);
    }

    /**
     * Scope pour les administrateurs
     */
    public function scopeAdmins($query)
    {
        return $query->where('user_type_id', 1);
    }

    /**
     * NOUVEAU : Scope pour les postes écran
     */
    public function scopeEcranUsers($query)
    {
        return $query->where('user_type_id', 2);
    }

    /**
     * NOUVEAU : Scope pour les postes accueil
     */
    public function scopeAccueilUsers($query)
    {
        return $query->where('user_type_id', 3);
    }

    /**
     * NOUVEAU : Scope pour les postes conseillers
     */
    public function scopeConseillerUsers($query)
    {
        return $query->where('user_type_id', 4);
    }

    /**
     * AMÉLIORÉ : Scope pour les utilisateurs normaux (tous sauf admin)
     */
    public function scopeNormalUsers($query)
    {
        return $query->whereIn('user_type_id', [2, 3, 4]);
    }

    /**
     * Legacy - Garde la compatibilité avec l'ancien code
     */
    public function scopeUsers($query)
    {
        return $query->normalUsers();
    }

    /**
     * NOUVEAU : Scope pour filtrer par type de rôle
     */
    public function scopeByRole($query, string $role)
    {
        $roleMapping = [
            'admin' => 1,
            'ecran' => 2,
            'accueil' => 3,
            'conseiller' => 4,
        ];
        
        if (isset($roleMapping[$role])) {
            return $query->where('user_type_id', $roleMapping[$role]);
        }
        
        return $query;
    }

    /**
     * NOUVEAU : Scope pour filtrer par multiple rôles
     */
    public function scopeByRoles($query, array $roles)
    {
        $roleMapping = [
            'admin' => 1,
            'ecran' => 2,
            'accueil' => 3,
            'conseiller' => 4,
        ];
        
        $typeIds = [];
        foreach ($roles as $role) {
            if (isset($roleMapping[$role])) {
                $typeIds[] = $roleMapping[$role];
            }
        }
        
        if (!empty($typeIds)) {
            return $query->whereIn('user_type_id', $typeIds);
        }
        
        return $query;
    }

    /**
     * AMÉLIORÉ : Scope pour recherche par terme (avec company et type)
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('username', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
              ->orWhere('mobile_number', 'like', "%{$term}%")
              ->orWhere('company', 'like', "%{$term}%")
              ->orWhereHas('userType', function($subQuery) use ($term) {
                  $subQuery->where('name', 'like', "%{$term}%");
              })
              ->orWhereHas('status', function($subQuery) use ($term) {
                  $subQuery->where('name', 'like', "%{$term}%");
              });
        });
    }

    /**
     * NOUVEAU : Scope pour les utilisateurs créés récemment
     */
    public function scopeRecentlyCreated($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * NOUVEAU : Scope pour les utilisateurs nécessitant un reset password
     */
    public function scopeNeedingPasswordReset($query)
    {
        return $query->whereHas('createdBy', function($q) {
            $q->where('password_reset_required', true);
        });
    }

    // ===============================================
    // MÉTHODES STATIQUES UTILITAIRES
    // ===============================================

    /**
     * NOUVEAU : Obtenir les statistiques générales des utilisateurs
     */
    public static function getGlobalStats(): array
    {
        return [
            'total_users' => self::count(),
            'active_users' => self::active()->count(),
            'inactive_users' => self::inactive()->count(),
            'suspended_users' => self::suspended()->count(),
            'admin_users' => self::admins()->count(),
            'ecran_users' => self::ecranUsers()->count(),
            'accueil_users' => self::accueilUsers()->count(),
            'conseiller_users' => self::conseillerUsers()->count(),
            'recent_users' => self::recentlyCreated()->count(),
            'users_needing_password_reset' => self::needingPasswordReset()->count(),
        ];
    }

    /**
     * NOUVEAU : Obtenir les utilisateurs par type avec statistiques
     */
    public static function getStatsByType(): array
    {
        $types = [
            1 => 'admin',
            2 => 'ecran', 
            3 => 'accueil',
            4 => 'conseiller'
        ];

        $stats = [];
        foreach ($types as $typeId => $roleName) {
            $stats[$roleName] = [
                'total' => self::where('user_type_id', $typeId)->count(),
                'active' => self::where('user_type_id', $typeId)->active()->count(),
                'inactive' => self::where('user_type_id', $typeId)->inactive()->count(),
                'suspended' => self::where('user_type_id', $typeId)->suspended()->count(),
            ];
        }

        return $stats;
    }

    /**
     * NOUVEAU : Rechercher des utilisateurs avec filtres avancés
     */
    public static function advancedSearch(array $filters = []): \Illuminate\Database\Eloquent\Builder
    {
        $query = self::with(['userType', 'status', 'createdBy']);

        // Filtrer par terme de recherche
        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Filtrer par rôle
        if (!empty($filters['role'])) {
            $query->byRole($filters['role']);
        }

        // Filtrer par rôles multiples
        if (!empty($filters['roles']) && is_array($filters['roles'])) {
            $query->byRoles($filters['roles']);
        }

        // Filtrer par statut
        if (!empty($filters['status'])) {
            $statusMap = [
                'active' => 2,
                'inactive' => 1,
                'suspended' => 3
            ];
            if (isset($statusMap[$filters['status']])) {
                $query->where('status_id', $statusMap[$filters['status']]);
            }
        }

        // Filtrer par créateur
        if (!empty($filters['created_by'])) {
            $query->whereHas('createdBy', function($q) use ($filters) {
                $q->where('administrator_id', $filters['created_by']);
            });
        }

        // Filtrer par période de création
        if (!empty($filters['created_after'])) {
            $query->where('created_at', '>=', $filters['created_after']);
        }

        if (!empty($filters['created_before'])) {
            $query->where('created_at', '<=', $filters['created_before']);
        }

        return $query;
    }
}