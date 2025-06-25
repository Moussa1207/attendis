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
        'last_login_at', 'failed_login_attempts', 'last_password_change',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'last_login_at' => 'datetime',
        'last_password_change' => 'datetime',
        'failed_login_attempts' => 'integer',
    ];

    // ===============================================
    // RELATIONS
    // ===============================================

    public function userType()
    {
        return $this->belongsTo(UserType::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function createdUsers()
    {
        return $this->hasMany(AdministratorUser::class, 'administrator_id');
    }

    public function createdBy()
    {
        return $this->hasOne(AdministratorUser::class, 'user_id');
    }

    public function createdServices()
    {
        return $this->hasMany(Service::class, 'created_by');
    }

    // ===============================================
    // MÉTHODES UTILITAIRES - TYPE ET STATUT
    // ===============================================

    public function isAdmin(): bool
    {
        return $this->user_type_id === 1;
    }

    public function isEcranUser(): bool
    {
        return $this->user_type_id === 2;
    }

    public function isAccueilUser(): bool
    {
        return $this->user_type_id === 3;
    }

    public function isConseillerUser(): bool
    {
        return $this->user_type_id === 4;
    }

    public function isNormalUser(): bool
    {
        return in_array($this->user_type_id, [2, 3, 4]);
    }

    public function isActive(): bool
    {
        return $this->status_id === 2;
    }

    public function isInactive(): bool
    {
        return $this->status_id === 1;
    }

    public function isSuspended(): bool
    {
        return $this->status_id === 3;
    }

    // ===============================================
    // MÉTHODES POUR LES SERVICES
    // ===============================================

    public function getCreatedServicesCountAttribute(): int
    {
        return $this->createdServices()->count();
    }

    public function hasCreatedServices(): bool
    {
        return $this->createdServices()->exists();
    }

    public function getActiveCreatedServices()
    {
        return $this->createdServices()->where('statut', 'actif');
    }

    public function getInactiveCreatedServices()
    {
        return $this->createdServices()->where('statut', 'inactif');
    }

    public function getServicesStats(): array
    {
        $services = $this->createdServices();
        
        return [
            'total_services' => $services->count(),
            'active_services' => $services->where('statut', 'actif')->count(),
            'inactive_services' => $services->where('statut', 'inactif')->count(),
            'recent_services' => $services->where('created_at', '>=', now()->subDays(30))->count(),
            'services_this_month' => $services->whereMonth('created_at', now()->month)->count(),
            'services_today' => $services->whereDate('created_at', today())->count(),
        ];
    }

    // ===============================================
    // MÉTHODES POUR CHANGER LE STATUT
    // ===============================================

    public function activate(): bool
    {
        return $this->update(['status_id' => 2]);
    }

    public function deactivate(): bool
    {
        return $this->update(['status_id' => 1]);
    }

    public function suspend(): bool
    {
        try {
            $result = $this->update(['status_id' => 3]);
            
            if ($result) {
                $this->refresh();
                
                \Log::info('User suspended successfully', [
                    'user_id' => $this->id,
                    'username' => $this->username,
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
    // MÉTHODES POUR LE TRACKING DES CONNEXIONS
    // ===============================================

    public function recordSuccessfulLogin(): void
    {
        $this->update([
            'last_login_at' => now(),
            'failed_login_attempts' => 0
        ]);
    }

    public function recordFailedLogin(): void
    {
        $this->increment('failed_login_attempts');
    }

    public function resetFailedLoginAttempts(): void
    {
        $this->update(['failed_login_attempts' => 0]);
    }

    public function recordPasswordChange(): void
    {
        $this->update([
            'last_password_change' => now(),
            'failed_login_attempts' => 0
        ]);
    }

    // ===============================================
    // MÉTHODES POUR LE CHANGEMENT DE MOT DE PASSE OBLIGATOIRE
    // ===============================================

    public function mustChangePassword(): bool
    {
        $adminRelation = $this->createdBy;
        return $adminRelation && $adminRelation->password_reset_required;
    }

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
    // MÉTHODES POUR LES INFORMATIONS UTILISATEUR
    // ===============================================

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

    public function getTypeIcon(): string
    {
        return match($this->user_type_id) {
            1 => 'shield',
            2 => 'monitor',
            3 => 'home',
            4 => 'users',
            default => 'user'
        };
    }

    public function getTypeBadgeColor(): string
    {
        return match($this->user_type_id) {
            1 => 'primary',
            2 => 'info',
            3 => 'success',
            4 => 'warning',
            default => 'secondary'
        };
    }

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

    public function getTypeEmoji(): string
    {
        return match($this->user_type_id) {
            1 => '🛡️',
            2 => '🖥️',
            3 => '🏢',
            4 => '👥',
            default => '👤'
        };
    }

    public function getStatusName(): string
    {
        return match($this->status_id) {
            1 => 'Inactif',
            2 => 'Actif',
            3 => 'Suspendu',
            default => $this->status->name ?? 'Non défini'
        };
    }

    public function getStatusBadgeColor(): string
    {
        return match($this->status_id) {
            1 => 'warning',
            2 => 'success',
            3 => 'danger',
            default => 'secondary'
        };
    }

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
    // MÉTHODES POUR LES INFORMATIONS DE CONNEXION
    // ===============================================

    public function getLastLoginInfo(): array
    {
        return [
            'last_login_at' => $this->last_login_at,
            'last_login_formatted' => $this->last_login_at 
                ? $this->last_login_at->format('d/m/Y à H:i') 
                : 'Jamais connecté',
            'last_login_relative' => $this->last_login_at 
                ? $this->last_login_at->diffForHumans() 
                : 'Jamais',
            'days_since_last_login' => $this->last_login_at 
                ? $this->last_login_at->diffInDays(now()) 
                : null,
        ];
    }

    public function getLoginAttemptsInfo(): array
    {
        return [
            'failed_attempts' => $this->failed_login_attempts ?? 0,
            'is_locked_out' => $this->isLockedOut(),
            'attempts_remaining' => max(0, Setting::getMaxLoginAttempts() - ($this->failed_login_attempts ?? 0)),
            'formatted_attempts' => ($this->failed_login_attempts ?? 0) . ' échec(s) récent(s)',
        ];
    }

    public function getPasswordInfo(): array
    {
        $lastChange = $this->last_password_change ?? $this->created_at;
        $isInitialPassword = !$this->last_password_change || $this->last_password_change->eq($this->created_at);
        
        return [
            'last_password_change' => $lastChange,
            'last_password_change_formatted' => $isInitialPassword 
                ? 'A la création' 
                : $lastChange->format('d/m/Y à H:i'),
            'last_password_change_relative' => $isInitialPassword 
                ? 'Mot de passe initial' 
                : $lastChange->diffForHumans(),
            'days_since_password_change' => $lastChange->diffInDays(now()),
            'is_initial_password' => $isInitialPassword,
            'password_age_warning' => $lastChange->diffInDays(now()) > 90,
        ];
    }

    // ===============================================
    // MÉTHODES DE TRAÇABILITÉ
    // ===============================================

    public function getCreator(): ?User
    {
        return AdministratorUser::getUserCreator($this->id);
    }

    public function getCreatedUsers()
    {
        return AdministratorUser::getUsersCreatedBy($this->id);
    }

    public function hasCreatedUsers(): bool
    {
        return $this->createdUsers()->count() > 0;
    }

    public function wasCreatedByAdmin(): bool
    {
        return $this->createdBy()->exists();
    }

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

    public function canBeDeleted(): bool
    {
        if ($this->isAdmin()) {
            $activeAdmins = self::where('user_type_id', 1)->where('status_id', 2)->count();
            return $activeAdmins > 1;
        }
        
        return true;
    }

    public function canBeSuspended(): bool
    {
        if ($this->isAdmin() && $this->isActive()) {
            $activeAdmins = self::where('user_type_id', 1)->where('status_id', 2)->count();
            return $activeAdmins > 1;
        }
        
        return true;
    }

    public function canChangeType(): bool
    {
        if ($this->isAdmin()) {
            $activeAdmins = self::where('user_type_id', 1)->where('status_id', 2)->count();
            return $activeAdmins > 1;
        }
        
        return true;
    }

    // ===============================================
    // GESTION DES MOTS DE PASSE TEMPORAIRES
    // ===============================================

    public static function generateSecureTemporaryPassword(int $length = 12): string
    {
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $symbols = '@#$%&*!?';
        
        $password = '';
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $symbols[random_int(0, strlen($symbols) - 1)];
        
        $allChars = $lowercase . $uppercase . $numbers . $symbols;
        for ($i = 4; $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }
        
        return str_shuffle($password);
    }

    public function requiresPasswordReset(): bool
    {
        $relation = $this->createdBy;
        return $relation && $relation->requiresPasswordReset();
    }

    // ===============================================
    // SCOPES POUR LES REQUÊTES
    // ===============================================

    public function scopeActive($query)
    {
        return $query->where('status_id', 2);
    }

    public function scopeInactive($query)
    {
        return $query->where('status_id', 1);
    }

    public function scopeSuspended($query)
    {
        return $query->where('status_id', 3);
    }

    public function scopeAdmins($query)
    {
        return $query->where('user_type_id', 1);
    }

    public function scopeEcranUsers($query)
    {
        return $query->where('user_type_id', 2);
    }

    public function scopeAccueilUsers($query)
    {
        return $query->where('user_type_id', 3);
    }

    public function scopeConseillerUsers($query)
    {
        return $query->where('user_type_id', 4);
    }

    public function scopeNormalUsers($query)
    {
        return $query->whereIn('user_type_id', [2, 3, 4]);
    }

    public function scopeWithServices($query)
    {
        return $query->whereHas('createdServices');
    }

    public function scopeWithActiveServices($query)
    {
        return $query->whereHas('createdServices', function($q) {
            $q->where('statut', 'actif');
        });
    }

    public function scopeWithInactiveServices($query)
    {
        return $query->whereHas('createdServices', function($q) {
            $q->where('statut', 'inactif');
        });
    }

    public function scopeWithMinServices($query, int $minCount = 1)
    {
        return $query->whereHas('createdServices', function($q) use ($minCount) {
            $q->havingRaw('COUNT(*) >= ?', [$minCount]);
        });
    }

    public function scopeUsers($query)
    {
        return $query->normalUsers();
    }

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

    public function scopeRecentlyCreated($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeNeedingPasswordReset($query)
    {
        return $query->whereHas('createdBy', function($q) {
            $q->where('password_reset_required', true);
        });
    }

    public function scopeRecentlyLoggedIn($query, int $days = 30)
    {
        return $query->where('last_login_at', '>=', now()->subDays($days));
    }

    public function scopeWithFailedAttempts($query, int $minAttempts = 1)
    {
        return $query->where('failed_login_attempts', '>=', $minAttempts);
    }

    public function scopeForAutoDetection($query)
    {
        if (!Setting::isAutoDetectAdvisorsEnabled()) {
            return $query->whereRaw('1 = 0');
        }
        
        return $query->where('user_type_id', 4)
                     ->where('status_id', 2);
    }

    public function scopeForAutoSessionClosure($query)
    {
        if (!Setting::isAutoSessionClosureEnabled()) {
            return $query->whereRaw('1 = 0');
        }
        
        return $query->where('user_type_id', '!=', 1)
                     ->where('status_id', 2);
    }

    // ===============================================
    // MÉTHODES LIÉES AUX PARAMÈTRES
    // ===============================================

    public function canBeAutoDetected(): bool
    {
        return $this->isConseillerUser() && Setting::isAutoDetectAdvisorsEnabled();
    }

    public function shouldAutoAssignAllServices(): bool
    {
        return $this->isConseillerUser() && Setting::isAutoAssignServicesEnabled();
    }

    public function shouldCloseSessionNow(): bool
    {
        return Setting::shouldCloseSessionsNow();
    }

    public function getMaxAllowedSessions(): int
    {
        if ($this->isAdmin()) {
            return Setting::getMaxConcurrentSessions() + 2;
        }
        
        return Setting::getMaxConcurrentSessions();
    }

    /**
     * Vérifier si le compte est verrouillé selon les paramètres système
     */
    public function isLockedOut(): bool
    {
        $maxAttempts = Setting::getMaxLoginAttempts();
        return $this->failed_login_attempts >= $maxAttempts;
    }

    public function getLockoutTimeRemaining(): ?int
    {
        if (!$this->isLockedOut()) {
            return null;
        }
        
        $lockoutDuration = Setting::getLockoutDurationMinutes();
        $lastFailedAttempt = $this->updated_at;
        
        $unlockTime = $lastFailedAttempt->addMinutes($lockoutDuration);
        $now = now();
        
        if ($now >= $unlockTime) {
            $this->update(['failed_login_attempts' => 0]);
            return 0;
        }
        
        return $now->diffInMinutes($unlockTime);
    }

    public function getSecurityInfo(): array
    {
        $settings = Setting::getSecuritySettings();
        
        return [
            'failed_attempts' => $this->failed_login_attempts ?? 0,
            'max_attempts' => $settings['max_login_attempts'],
            'is_locked' => $this->isLockedOut(),
            'lockout_remaining' => $this->getLockoutTimeRemaining(),
            'last_login' => $this->last_login_at,
            'max_sessions' => $this->getMaxAllowedSessions(),
            'session_timeout' => $settings['session_timeout']
        ];
    }

    public function getRequiredActions(): array
    {
        $actions = [];
        
        if ($this->mustChangePassword()) {
            $actions[] = [
                'type' => 'password_change',
                'message' => 'Changement de mot de passe requis',
                'priority' => 'high'
            ];
        }
        
        if ($this->shouldCloseSessionNow() && !$this->isAdmin()) {
            $actions[] = [
                'type' => 'session_closure',
                'message' => 'Votre session va se fermer automatiquement',
                'priority' => 'medium'
            ];
        }
        
        if ($this->failed_login_attempts > 0) {
            $remaining = Setting::getMaxLoginAttempts() - $this->failed_login_attempts;
            if ($remaining <= 2) {
                $actions[] = [
                    'type' => 'security_warning',
                    'message' => "Attention: {$remaining} tentative(s) restante(s)",
                    'priority' => 'warning'
                ];
            }
        }
        
        return $actions;
    }

    public function applyLoginSettings(): void
    {
        if ($this->canBeAutoDetected()) {
            $this->markAsAvailable();
            
            if ($this->shouldAutoAssignAllServices()) {
                $this->assignAllActiveServices();
            }
        }
    }

    public function markAsAvailable(): void
    {
        \Log::info('Advisor marked as available via auto-detection', [
            'user_id' => $this->id,
            'username' => $this->username,
            'type' => $this->getTypeName()
        ]);
    }

    public function assignAllActiveServices(): void
    {
        try {
            $activeServices = \App\Models\Service::where('statut', 'actif')->get();
            
            \Log::info('All active services auto-assigned to advisor', [
                'user_id' => $this->id,
                'username' => $this->username,
                'services_count' => $activeServices->count()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error auto-assigning services', [
                'user_id' => $this->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function recordSuccessfulLoginWithSettings(): void
    {
        $this->update([
            'last_login_at' => now(),
            'failed_login_attempts' => 0,
            'last_login_ip' => request()->ip(),
            'last_user_agent' => request()->userAgent(),
        ]);
        
        $this->applyLoginSettings();
    }

    public function recordFailedLoginWithSettings(): void
    {
        $this->increment('failed_login_attempts');
        
        $maxAttempts = Setting::getMaxLoginAttempts();
        
        \Log::warning('Failed login attempt with settings', [
            'user_id' => $this->id,
            'username' => $this->username,
            'failed_attempts' => $this->failed_login_attempts,
            'max_attempts' => $maxAttempts,
            'will_be_locked' => $this->failed_login_attempts >= $maxAttempts,
            'ip' => request()->ip()
        ]);
    }

    // ===============================================
    // MÉTHODES STATIQUES UTILITAIRES
    // ===============================================

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
            'recently_logged_in' => self::recentlyLoggedIn()->count(),
            'with_failed_attempts' => self::withFailedAttempts()->count(),
            'total_services' => \App\Models\Service::count(),
            'active_services' => \App\Models\Service::where('statut', 'actif')->count(),
            'inactive_services' => \App\Models\Service::where('statut', 'inactif')->count(),
            'recent_services' => \App\Models\Service::where('created_at', '>=', now()->subDays(30))->count(),
            'services_created_today' => \App\Models\Service::whereDate('created_at', today())->count(),
            'services_created_this_month' => \App\Models\Service::whereMonth('created_at', now()->month)->count(),
            'users_with_services' => self::whereHas('createdServices')->count(),
            'admins_with_services' => self::admins()->whereHas('createdServices')->count(),
        ];
    }

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

    public static function advancedSearch(array $filters = []): \Illuminate\Database\Eloquent\Builder
    {
        $query = self::with(['userType', 'status', 'createdBy']);

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (!empty($filters['role'])) {
            $query->byRole($filters['role']);
        }

        if (!empty($filters['roles']) && is_array($filters['roles'])) {
            $query->byRoles($filters['roles']);
        }

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

        if (!empty($filters['created_by'])) {
            $query->whereHas('createdBy', function($q) use ($filters) {
                $q->where('administrator_id', $filters['created_by']);
            });
        }

        if (!empty($filters['created_after'])) {
            $query->where('created_at', '>=', $filters['created_after']);
        }

        if (!empty($filters['created_before'])) {
            $query->where('created_at', '<=', $filters['created_before']);
        }

        return $query;
    }

    public function getDetailedServicesStats(): array
    {
        if (!$this->isAdmin()) {
            return [];
        }

        $services = $this->createdServices();
        $baseStats = $this->getServicesStats();
        
        $oldestService = $services->oldest('created_at')->first();
        $newestService = $services->latest('created_at')->first();
        
        return array_merge($baseStats, [
            'oldest_service' => $oldestService ? [
                'nom' => $oldestService->nom,
                'code' => $oldestService->code,
                'created_at' => $oldestService->created_at->format('d/m/Y'),
                'days_ago' => $oldestService->created_at->diffInDays(now())
            ] : null,
            
            'newest_service' => $newestService ? [
                'nom' => $newestService->nom,
                'code' => $newestService->code,
                'created_at' => $newestService->created_at->format('d/m/Y'),
                'days_ago' => $newestService->created_at->diffInDays(now())
            ] : null,
            
            'average_services_per_month' => $this->getAverageServicesPerMonth(),
            'most_productive_month' => $this->getMostProductiveMonth(),
            'services_by_status' => [
                'actif' => $services->where('statut', 'actif')->get()->map(function($service) {
                    return [
                        'nom' => $service->nom,
                        'code' => $service->code,
                        'created_at' => $service->created_at->format('d/m/Y')
                    ];
                })->toArray(),
                'inactif' => $services->where('statut', 'inactif')->get()->map(function($service) {
                    return [
                        'nom' => $service->nom,
                        'code' => $service->code,
                        'created_at' => $service->created_at->format('d/m/Y')
                    ];
                })->toArray(),
            ]
        ]);
    }

    private function getAverageServicesPerMonth(): float
    {
        $firstService = $this->createdServices()->oldest('created_at')->first();
        if (!$firstService) {
            return 0;
        }
        
        $monthsSinceFirst = $firstService->created_at->diffInMonths(now()) + 1;
        $totalServices = $this->createdServices()->count();
        
        return round($totalServices / $monthsSinceFirst, 2);
    }

    private function getMostProductiveMonth(): ?array
    {
        $servicesByMonth = $this->createdServices()
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
            ->groupBy('year', 'month')
            ->orderBy('count', 'desc')
            ->first();
        
        if (!$servicesByMonth) {
            return null;
        }
        
        return [
            'year' => $servicesByMonth->year,
            'month' => $servicesByMonth->month,
            'month_name' => \Carbon\Carbon::create($servicesByMonth->year, $servicesByMonth->month)->locale('fr')->monthName,
            'count' => $servicesByMonth->count,
            'formatted' => \Carbon\Carbon::create($servicesByMonth->year, $servicesByMonth->month)->locale('fr')->format('F Y')
        ];
    }
}