<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserType extends Model
{
    protected $fillable = ['name', 'description'];
   
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
   
    // ===============================================
    // RELATIONS
    // ===============================================
    
    /**
     * Relation avec les utilisateurs
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
   
    // ===============================================
    // MÉTHODES UTILITAIRES POUR TOUS LES TYPES
    // ===============================================
    
    /**
     * Obtenir le type Administrateur (ID 1)
     */
    public static function getAdminType()
    {
        return self::find(1);
    }
   
    /**
     * Obtenir le type Poste Ecran (ID 2)
     */
    public static function getEcranType()
    {
        return self::find(2);
    }
   
    /**
     * Obtenir le type Poste Accueil (ID 3)
     */
    public static function getAccueilType()
    {
        return self::find(3);
    }
   
    /**
     * Obtenir le type Poste Conseiller (ID 4)
     */
    public static function getConseillerType()
    {
        return self::find(4);
    }

    /**
     * NOUVEAU : Obtenir le type utilisateur normal (legacy - ID 2)
     * Garde la compatibilité avec l'ancien code
     */
    public static function getUserType()
    {
        return self::find(2); // Maintenant "Poste Ecran"
    }

    /**
     * NOUVEAU : Mapper les rôles du formulaire vers les IDs
     */
    public static function getRoleMapping(): array
    {
        return [
            'admin' => 1,       // Administrateur
            'ecran' => 2,       // Poste Ecran
            'accueil' => 3,     // Poste Accueil
            'conseiller' => 4,  // Poste Conseiller
        ];
    }

    /**
     * NOUVEAU : Obtenir l'ID du type depuis le rôle
     */
    public static function getTypeIdFromRole(string $role): ?int
    {
        $mapping = self::getRoleMapping();
        return $mapping[$role] ?? null;
    }

    /**
     * NOUVEAU : Obtenir le rôle depuis l'ID du type
     */
    public static function getRoleFromTypeId(int $typeId): ?string
    {
        $mapping = array_flip(self::getRoleMapping());
        return $mapping[$typeId] ?? null;
    }

    /**
     * NOUVEAU : Obtenir l'icône selon le type
     */
    public function getIcon(): string
    {
        return match($this->id) {
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
    public function getBadgeColor(): string
    {
        return match($this->id) {
            1 => 'primary',     // Administrateur (bleu)
            2 => 'info',        // Poste Ecran (cyan)
            3 => 'success',     // Poste Accueil (vert)
            4 => 'warning',     // Poste Conseiller (orange)
            default => 'secondary'
        };
    }

    /**
     * NOUVEAU : Obtenir la description courte du type
     */
    public function getShortDescription(): string
    {
        return match($this->id) {
            1 => 'Admin système',
            2 => 'Interface écran',
            3 => 'Réception',
            4 => 'Support client',
            default => 'Utilisateur'
        };
    }

    /**
     * NOUVEAU : Obtenir l'emoji représentatif
     */
    public function getEmoji(): string
    {
        return match($this->id) {
            1 => '🛡️',  // Administrateur
            2 => '🖥️',  // Poste Ecran
            3 => '🏢',  // Poste Accueil
            4 => '👥',  // Poste Conseiller
            default => '👤'
        };
    }
   
    // ===============================================
    // MÉTHODES DE VÉRIFICATION
    // ===============================================
    
    /**
     * Vérifier si c'est le type administrateur
     */
    public function isAdminType(): bool
    {
        return $this->id === 1;
    }
   
    /**
     * Vérifier si c'est le type poste écran
     */
    public function isEcranType(): bool
    {
        return $this->id === 2;
    }

    /**
     * Vérifier si c'est le type poste accueil
     */
    public function isAccueilType(): bool
    {
        return $this->id === 3;
    }

    /**
     * Vérifier si c'est le type poste conseiller
     */
    public function isConseillerType(): bool
    {
        return $this->id === 4;
    }

    /**
     * Vérifier si c'est un type utilisateur normal (pas admin)
     */
    public function isNormalUserType(): bool
    {
        return in_array($this->id, [2, 3, 4]);
    }

    /**
     * Legacy - Garde la compatibilité avec l'ancien code
     */
    public function isUserType(): bool
    {
        return $this->isNormalUserType();
    }
   
    // ===============================================
    // MÉTHODES STATISTIQUES
    // ===============================================
    
    /**
     * Obtenir tous les utilisateurs de ce type
     */
    public function getUsersCount(): int
    {
        return $this->users()->count();
    }
   
    /**
     * Obtenir les utilisateurs actifs de ce type
     */
    public function getActiveUsersCount(): int
    {
        return $this->users()->where('status_id', 2)->count();
    }

    /**
     * Obtenir les utilisateurs inactifs de ce type
     */
    public function getInactiveUsersCount(): int
    {
        return $this->users()->where('status_id', 1)->count();
    }

    /**
     * Obtenir les utilisateurs suspendus de ce type
     */
    public function getSuspendedUsersCount(): int
    {
        return $this->users()->where('status_id', 3)->count();
    }
   
    /**
     * Obtenir les statistiques complètes de ce type
     */
    public function getStats(): array
    {
        return [
            'total_users' => $this->getUsersCount(),
            'active_users' => $this->getActiveUsersCount(),
            'inactive_users' => $this->getInactiveUsersCount(),
            'suspended_users' => $this->getSuspendedUsersCount(),
            'icon' => $this->getIcon(),
            'badge_color' => $this->getBadgeColor(),
            'short_description' => $this->getShortDescription(),
            'emoji' => $this->getEmoji(),
        ];
    }

    // ===============================================
    // MÉTHODES STATIQUES UTILITAIRES
    // ===============================================

    /**
     * Obtenir tous les types avec leurs statistiques
     */
    public static function getAllWithStats(): array
    {
        return self::all()->map(function($type) {
            return array_merge([
                'id' => $type->id,
                'name' => $type->name,
                'description' => $type->description,
            ], $type->getStats());
        })->toArray();
    }

    /**
     * Obtenir la liste des types pour les select/dropdown
     */
    public static function getSelectOptions(bool $includeAdmin = false): array
    {
        $query = self::query();
        
        if (!$includeAdmin) {
            $query->where('id', '!=', 1); // Exclure les admins
        }
        
        return $query->pluck('name', 'id')->toArray();
    }

    /**
     * Obtenir la liste des rôles pour les formulaires (sans admin)
     */
    public static function getRoleSelectOptions(): array
    {
        return [
            'ecran' => '🖥️ Poste Ecran',
            'accueil' => '🏢 Poste Accueil',
            'conseiller' => '👥 Poste Conseiller',
        ];
    }

    /**
     * Obtenir la liste complète des rôles (avec admin)
     */
    public static function getAllRoleSelectOptions(): array
    {
        return [
            'admin' => '🛡️ Administrateur',
            'ecran' => '🖥️ Poste Ecran',
            'accueil' => '🏢 Poste Accueil',
            'conseiller' => '👥 Poste Conseiller',
        ];
    }

    /**
     * Obtenir les informations détaillées d'un rôle
     */
    public static function getRoleInfo(string $role): ?array
    {
        $rolesInfo = [
            'admin' => [
                'name' => 'Administrateur',
                'description' => 'Accès complet au système avec tous les privilèges',
                'icon' => 'shield',
                'badge_color' => 'primary',
                'emoji' => '🛡️'
            ],
            'ecran' => [
                'name' => 'Poste Ecran',
                'description' => 'Interface utilisateur pour affichage et consultation',
                'icon' => 'monitor',
                'badge_color' => 'info',
                'emoji' => '🖥️'
            ],
            'accueil' => [
                'name' => 'Poste Accueil',
                'description' => 'Réception et orientation des visiteurs',
                'icon' => 'home',
                'badge_color' => 'success',
                'emoji' => '🏢'
            ],
            'conseiller' => [
                'name' => 'Poste Conseiller',
                'description' => 'Support et assistance client',
                'icon' => 'users',
                'badge_color' => 'warning',
                'emoji' => '👥'
            ]
        ];

        return $rolesInfo[$role] ?? null;
    }

    /**
     * Rechercher un type par nom (insensible à la casse)
     */
    public static function findByName(string $name): ?self
    {
        return self::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();
    }

    /**
     * Obtenir les types les plus utilisés
     */
    public static function getMostUsedTypes(int $limit = 5): array
    {
        return self::withCount('users')
                   ->orderBy('users_count', 'desc')
                   ->limit($limit)
                   ->get()
                   ->map(function($type) {
                       return [
                           'id' => $type->id,
                           'name' => $type->name,
                           'users_count' => $type->users_count,
                           'icon' => $type->getIcon(),
                           'badge_color' => $type->getBadgeColor()
                       ];
                   })
                   ->toArray();
    }
}