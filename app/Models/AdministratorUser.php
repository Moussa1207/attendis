<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AdministratorUser extends Model
{
    protected $table = 'administrator_user';
    
    protected $fillable = [
        'administrator_id',
        'user_id',
        'creation_method',
        'creation_notes',
        'password_reset_required',
        'password_reset_sent_at',
    ];

    protected $casts = [
        'password_reset_required' => 'boolean',
        'password_reset_sent_at' => 'datetime',
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
    // MÉTHODES POUR GESTION MOTS DE PASSE
    // ===============================================

    /**
     * Marquer que le reset password a été envoyé
     */
    public function markPasswordResetSent()
    {
        $this->update([
            'password_reset_sent_at' => now(),
            'password_reset_required' => true
        ]);
    }

    /**
     * Marquer que le password a été changé
     */
    public function markPasswordChanged()
    {
        $this->update([
            'password_reset_required' => false
        ]);
    }

    /**
     * Vérifier si le reset password a été envoyé
     */
    public function wasPasswordResetSent(): bool
    {
        return !is_null($this->password_reset_sent_at);
    }

    /**
     * Vérifier si le reset password est requis
     */
    public function requiresPasswordReset(): bool
    {
        return $this->password_reset_required;
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
    public static function createRelation($administratorId, $userId, $options = []): self
    {
        return self::create(array_merge([
            'administrator_id' => $administratorId,
            'user_id' => $userId,
        ], $options));
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
    public static function getStatsForAdmin($administratorId): array
    {
        $relations = self::where('administrator_id', $administratorId)->with('user')->get();
        
        return [
            'total_created' => $relations->count(),
            'active_created' => $relations->filter(function($rel) {
                return $rel->user && $rel->user->status_id === 2;
            })->count(),
            'inactive_created' => $relations->filter(function($rel) {
                return $rel->user && $rel->user->status_id === 1;
            })->count(),
            'suspended_created' => $relations->filter(function($rel) {
                return $rel->user && $rel->user->status_id === 3;
            })->count(),
            'password_reset_required' => $relations->where('password_reset_required', true)->count(),
            'created_today' => $relations->where('created_at', '>=', Carbon::today())->count(),
            'created_this_week' => $relations->where('created_at', '>=', Carbon::now()->startOfWeek())->count(),
            'created_this_month' => $relations->where('created_at', '>=', Carbon::now()->startOfMonth())->count(),
            'latest_creation' => $relations->sortByDesc('created_at')->first(),
        ];
    }

    /**
     * Obtenir tous les administrateurs et leurs statistiques
     */
    public static function getAllAdminsStats(): array
    {
        $admins = User::admins()->get();
        
        return $admins->map(function($admin) {
            $stats = self::getStatsForAdmin($admin->id);
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

    /**
     * Scope pour les utilisateurs créés par un admin spécifique
     */
    public function scopeCreatedBy($query, $administratorId)
    {
        return $query->where('administrator_id', $administratorId);
    }

    /**
     * Scope pour les utilisateurs nécessitant un reset password
     */
    public function scopePasswordResetRequired($query)
    {
        return $query->where('password_reset_required', true);
    }

    /**
     * Scope pour une méthode de création spécifique
     */
    public function scopeByCreationMethod($query, $method)
    {
        return $query->where('creation_method', $method);
    }
}