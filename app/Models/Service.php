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
        'letter_of_service',
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
              ->orWhere('letter_of_service', 'LIKE', "%{$search}%")
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
            'letter_of_service' => $this->letter_of_service,
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
            'letter_of_service' => 'required|string|max:5|unique:services,letter_of_service,' . $serviceId,
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
            'letter_of_service.required' => 'La lettre de service est obligatoire.',
            'letter_of_service.unique' => 'Cette lettre de service est déjà utilisée. Veuillez en choisir une autre.',
            'letter_of_service.max' => 'La lettre de service ne peut pas dépasser 5 caractères.',
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
     * ✅ Génération automatique de la lettre de service
     */
    public static function generateLetterOfService($serviceName, $excludeId = null): string
    {
        // Prendre la première lettre du nom en majuscule
        $firstLetter = strtoupper(substr($serviceName, 0, 1));
        
        // Vérifier si cette lettre est déjà utilisée
        $query = self::where('letter_of_service', $firstLetter);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        if (!$query->exists()) {
            return $firstLetter;
        }
        
        // Si la première lettre est prise, essayer les suivantes
        for ($i = 1; $i < 26; $i++) {
            $ascii = ord($firstLetter) + $i;
            if ($ascii > 90) { // Si on dépasse Z, recommencer avec A
                $ascii = 65 + ($i - (90 - ord($firstLetter) + 1));
            }
            
            $testLetter = chr($ascii);
            $query = self::where('letter_of_service', $testLetter);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            
            if (!$query->exists()) {
                return $testLetter;
            }
        }
        
        // Si toutes les lettres simples sont prises, utiliser des combinaisons
        $counter = 1;
        do {
            $testLetter = $firstLetter . $counter;
            $query = self::where('letter_of_service', $testLetter);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            $counter++;
        } while ($query->exists());
        
        return $testLetter;
    }

    /**
     * ✅ Vérifier si une lettre de service est disponible
     */
    public static function isLetterOfServiceAvailable($letter, $excludeId = null): bool
    {
        $query = self::where('letter_of_service', $letter);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        return !$query->exists();
    }

    /**
     * ✅ Boot method pour les événements
     */
    protected static function boot()
    {
        parent::boot();

        // Générer la lettre de service automatiquement si vide
        static::creating(function ($service) {
            if (empty($service->letter_of_service)) {
                $service->letter_of_service = self::generateLetterOfService($service->nom);
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
                'letter_of_service' => $service->letter_of_service,
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

        // Générer une nouvelle lettre de service unique
        $attributes['letter_of_service'] = self::generateLetterOfService($attributes['nom']);
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
            'Lettre de service' => $this->letter_of_service,
            'Statut' => $this->getStatusWithEmoji(),
            'Description' => $this->description ?: 'Aucune description',
            'Créé par' => $this->creator_name,
            'Date de création' => $this->formatted_created_at,
            'Dernière modification' => $this->formatted_updated_at,
            'Âge' => $this->age_formatted,
        ];
    }
}