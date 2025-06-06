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
        return $this->update(['status_id' => 3]);
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
    // MÉTHODES POUR OBTENIR LES NOMS
    // ===============================================

    /**
     * Obtenir le nom du type d'utilisateur
     */
    public function getTypeName(): string
    {
        return $this->userType->name ?? 'Non défini';
    }

    /**
     * Obtenir le nom du statut
     */
    public function getStatusName(): string
    {
        return $this->status->name ?? 'Non défini';
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
     * Obtenir les informations de création (AMÉLIORÉ avec company)
     */
    public function getCreationInfo(): ?array
    {
        $creator = $this->getCreator();
        return $creator ? [
            'created_by' => $creator->username,
            'created_by_id' => $creator->id,
            'created_by_email' => $creator->email,
            'created_by_company' => $creator->company, // NOUVEAU
            'created_at' => $this->created_at,
            'created_at_formatted' => $this->created_at->format('d/m/Y à H:i'),
            'created_at_human' => $this->created_at->diffForHumans(),
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
     * Obtenir la couleur du badge selon le statut
     */
    public function getStatusBadgeColor(): string
    {
        switch ($this->status_id) {
            case 1: return 'warning'; // Inactif
            case 2: return 'success'; // Actif
            case 3: return 'danger';  // Suspendu
            default: return 'secondary';
        }
    }

    /**
     * Obtenir l'icône selon le type
     */
    public function getTypeIcon(): string
    {
        return $this->isAdmin() ? 'shield' : 'user';
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
     * Scope pour les utilisateurs normaux
     */
    public function scopeUsers($query)
    {
        return $query->where('user_type_id', 2);
    }

    /**
     * Scope pour recherche par terme (AMÉLIORÉ avec company)
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('username', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
              ->orWhere('mobile_number', 'like', "%{$term}%")
              ->orWhere('company', 'like', "%{$term}%"); // NOUVEAU - recherche par entreprise
        });
    }
}