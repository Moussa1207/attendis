<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Service extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nom',
        'code',
        'statut',
        'description',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * ✅ Relations
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * ✅ Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('statut', 'actif');
    }

    public function scopeInactive($query)
    {
        return $query->where('statut', 'inactif');
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('nom', 'LIKE', "%{$search}%")
              ->orWhere('code', 'LIKE', "%{$search}%")
              ->orWhere('description', 'LIKE', "%{$search}%");
        });
    }

    /**
     * ✅ Méthodes de statut
     */
    public function isActive(): bool
    {
        return $this->statut === 'actif';
    }

    public function isInactive(): bool
    {
        return $this->statut === 'inactif';
    }

    public function activate(): bool
    {
        return $this->update(['statut' => 'actif']);
    }

    public function deactivate(): bool
    {
        return $this->update(['statut' => 'inactif']);
    }

    /**
     * ✅ Méthodes d'affichage
     */
    public function getStatusWithEmoji(): string
    {
        return $this->statut === 'actif' ? '✅ Actif' : '⏸️ Inactif';
    }

    public function getStatusBadgeColor(): string
    {
        return $this->statut === 'actif' ? 'success' : 'warning';
    }

    /**
     * ✅ Méthode pour l'âge formaté
     */
    public function getAgeFormattedAttribute(): string
    {
        $days = $this->created_at->diffInDays(now());
        
        if ($days < 1) {
            return 'Moins d\'un jour';
        } elseif ($days === 1) {
            return '1 jour';
        } elseif ($days < 7) {
            return $days . ' jours';
        } elseif ($days < 30) {
            $weeks = floor($days / 7);
            return $weeks . ' semaine' . ($weeks > 1 ? 's' : '');
        } elseif ($days < 365) {
            $months = floor($days / 30);
            return $months . ' mois';
        } else {
            $years = floor($days / 365);
            return $years . ' an' . ($years > 1 ? 's' : '');
        }
    }

    /**
     * ✅ Données pour API
     */
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'code' => $this->code,
            'statut' => $this->statut,
            'statut_emoji' => $this->getStatusWithEmoji(),
            'status_badge_color' => $this->getStatusBadgeColor(),
            'description' => $this->description ?: 'Aucune description',
            'created_by' => $this->creator ? $this->creator->username : 'Système',
            'created_at' => $this->created_at->format('d/m/Y à H:i'),
            'created_at_iso' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->format('d/m/Y à H:i'),
            'updated_at_iso' => $this->updated_at->toISOString(),
            'age_formatted' => $this->age_formatted,
            'is_active' => $this->isActive(),
            'is_inactive' => $this->isInactive(),
            'created_at_relative' => $this->created_at->diffForHumans(),
            'updated_at_relative' => $this->updated_at->diffForHumans(),
        ];
    }

    /**
     * ✅ Validation personnalisée
     */
    public static function getValidationRules($serviceId = null): array
    {
        return [
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:services,code,' . $serviceId,
            'statut' => 'required|in:actif,inactif',
            'description' => 'nullable|string|max:1000',
        ];
    }

    /**
     * ✅ Messages de validation
     */
    public static function getValidationMessages(): array
    {
        return [
            'nom.required' => 'Le nom du service est obligatoire.',
            'nom.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'code.required' => 'Le code du service est obligatoire.',
            'code.unique' => 'Ce code existe déjà. Veuillez en choisir un autre.',
            'code.max' => 'Le code ne peut pas dépasser 50 caractères.',
            'statut.required' => 'Le statut est obligatoire.',
            'statut.in' => 'Le statut doit être "actif" ou "inactif".',
            'description.max' => 'La description ne peut pas dépasser 1000 caractères.',
        ];
    }

    /**
     * ✅ Méthodes utilitaires
     */
    public function canBeDeleted(): bool
    {
        // Ajouter ici votre logique métier
        // Par exemple: vérifier s'il n'y a pas de dépendances
        return true;
    }

    public function canBeModified(): bool
    {
        // Ajouter ici votre logique métier
        return true;
    }

    /**
     * ✅ Boot method pour les événements
     */
    protected static function boot()
    {
        parent::boot();

        // Générer le code automatiquement si vide
        static::creating(function ($service) {
            if (empty($service->code)) {
                $service->code = Str::slug($service->nom);
            }
            
            // S'assurer de l'unicité
            $originalCode = $service->code;
            $counter = 1;
            while (static::where('code', $service->code)->exists()) {
                $service->code = $originalCode . '-' . $counter;
                $counter++;
            }
        });

        // Log des modifications importantes
        static::updated(function ($service) {
            if ($service->isDirty('statut')) {
                \Log::info('Service status changed', [
                    'service_id' => $service->id,
                    'service_name' => $service->nom,
                    'old_status' => $service->getOriginal('statut'),
                    'new_status' => $service->statut,
                    'changed_by' => auth()->id(),
                ]);
            }
        });

        static::deleted(function ($service) {
            \Log::warning('Service deleted', [
                'service_id' => $service->id,
                'service_name' => $service->nom,
                'deleted_by' => auth()->id(),
            ]);
        });
    }

    /**
     * ✅ Getters personnalisés
     */
    public function getFormattedCreatedAtAttribute(): string
    {
        return $this->created_at->format('d/m/Y à H:i');
    }

    public function getFormattedUpdatedAtAttribute(): string
    {
        return $this->updated_at->format('d/m/Y à H:i');
    }

    public function getCreatorNameAttribute(): string
    {
        return $this->creator ? $this->creator->username : 'Système';
    }

    /**
     * ✅ Méthodes de recherche avancée
     */
    public static function searchAdvanced($filters = [])
    {
        $query = self::query();

        // Recherche textuelle
        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Filtre par statut
        if (!empty($filters['statut'])) {
            $query->where('statut', $filters['statut']);
        }

        // Filtre par créateur
        if (!empty($filters['created_by'])) {
            $query->where('created_by', $filters['created_by']);
        }

        // Filtre par période
        if (!empty($filters['period'])) {
            switch ($filters['period']) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'week':
                    $query->where('created_at', '>=', now()->startOfWeek());
                    break;
                case 'month':
                    $query->where('created_at', '>=', now()->startOfMonth());
                    break;
            }
        }

        return $query;
    }

    /**
     * ✅ Statistiques globales
     */
    public static function getGlobalStats(): array
    {
        return [
            'total' => self::count(),
            'active' => self::active()->count(),
            'inactive' => self::inactive()->count(),
            'created_today' => self::whereDate('created_at', today())->count(),
            'created_this_week' => self::where('created_at', '>=', now()->startOfWeek())->count(),
            'created_this_month' => self::where('created_at', '>=', now()->startOfMonth())->count(),
        ];
    }

    /**
     * ✅ Récupérer les services récents
     */
    public static function getRecent($limit = 10)
    {
        return self::with('creator')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * ✅ Récupérer les services populaires (les plus utilisés)
     */
    public static function getPopular($limit = 10)
    {
        // Vous pouvez adapter cette méthode selon votre logique métier
        // Par exemple, trier par nombre d'utilisations, de références, etc.
        return self::active()
            ->with('creator')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * ✅ Duplicate un service
     */
    public function duplicate(array $overrides = []): self
    {
        $attributes = $this->getAttributes();
        unset($attributes['id'], $attributes['created_at'], $attributes['updated_at']);

        // Générer un nouveau code unique
        $attributes['code'] = $attributes['code'] . '-copy';
        $attributes['nom'] = $attributes['nom'] . ' (Copie)';

        // Appliquer les overrides
        $attributes = array_merge($attributes, $overrides);

        return self::create($attributes);
    }

    /**
     * ✅ Export des données pour CSV/Excel
     */
    public function toExportArray(): array
    {
        return [
            'ID' => $this->id,
            'Nom' => $this->nom,
            'Code' => $this->code,
            'Statut' => $this->getStatusWithEmoji(),
            'Description' => $this->description ?: 'Aucune description',
            'Créé par' => $this->creator_name,
            'Date de création' => $this->formatted_created_at,
            'Dernière modification' => $this->formatted_updated_at,
            'Âge' => $this->age_formatted,
        ];
    }
}