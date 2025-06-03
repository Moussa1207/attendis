<?php
// app/Models/AdministratorUser.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdministratorUser extends Model
{
    protected $table = 'administrator_user';
    
    protected $fillable = [
        'administrator_id',
        'user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ===============================================
    // RELATIONS
    // ===============================================

    /**
     * L'administrateur qui a créé
     */
    public function administrator()
    {
        return $this->belongsTo(User::class, 'administrator_id');
    }

    /**
     * L'utilisateur qui a été créé
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ===============================================
    // MÉTHODES STATIQUES UTILITAIRES
    // ===============================================

    /**
     * Obtenir l'administrateur qui a créé un utilisateur
     */
    public static function getUserCreator($userId): ?User
    {
        $record = self::where('user_id', $userId)->first();
        return $record ? $record->administrator : null;
    }

    /**
     * Obtenir tous les utilisateurs créés par un administrateur
     */
    public static function getUsersCreatedBy($administratorId)
    {
        return self::where('administrator_id', $administratorId)->with('user')->get();
    }

    /**
     * Créer une nouvelle relation administrateur-utilisateur
     */
    public static function createRelation($administratorId, $userId): self
    {
        return self::create([
            'administrator_id' => $administratorId,
            'user_id' => $userId,
        ]);
    }

    /**
     * Vérifier si un utilisateur a été créé par un admin spécifique
     */
    public static function wasCreatedBy($userId, $administratorId): bool
    {
        return self::where('user_id', $userId)
                   ->where('administrator_id', $administratorId)
                   ->exists();
    }

    /**
     * Obtenir les statistiques de création d'un admin
     */
    public static function getAdminCreationStats($administratorId): array
    {
        $relations = self::where('administrator_id', $administratorId)->with('user')->get();
        
        return [
            'total_created' => $relations->count(),
            'active_created' => $relations->where('user.status_id', 2)->count(),
            'inactive_created' => $relations->where('user.status_id', 1)->count(),
            'suspended_created' => $relations->where('user.status_id', 3)->count(),
            'latest_creation' => $relations->sortByDesc('created_at')->first(),
            'creation_rate_this_month' => $relations->where('created_at', '>=', now()->startOfMonth())->count(),
        ];
    }

    /**
     * Obtenir tous les administrateurs et leurs statistiques
     */
    public static function getAllAdminsStats(): array
    {
        $admins = User::admins()->get();
        
        return $admins->map(function($admin) {
            $stats = self::getAdminCreationStats($admin->id);
            return [
                'admin_id' => $admin->id,
                'admin_username' => $admin->username,
                'admin_email' => $admin->email,
                'stats' => $stats
            ];
        })->toArray();
    }

    // ===============================================
    // SCOPES
    // ===============================================

    /**
     * Scope pour les relations créées aujourd'hui
     */
    public function scopeCreatedToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope pour les relations créées cette semaine
     */
    public function scopeCreatedThisWeek($query)
    {
        return $query->where('created_at', '>=', now()->startOfWeek());
    }

    /**
     * Scope pour les relations créées ce mois
     */
    public function scopeCreatedThisMonth($query)
    {
        return $query->where('created_at', '>=', now()->startOfMonth());
    }
}