<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agency extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'phone',
        'address_1',
        'address_2',
        'city',
        'country',
        'status',
        'notes',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Définir le statut par défaut lors de la création
        static::creating(function ($agency) {
            if (empty($agency->status)) {
                $agency->status = 'active';
            }
        });
    }

    /**
     * Relation: L'utilisateur qui a créé l'agence.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope: Agences actives.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Agences inactives.
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Scope: Agences récentes (derniers 7 jours).
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope: Recherche par terme.
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('name', 'LIKE', "%{$term}%")
              ->orWhere('phone', 'LIKE', "%{$term}%")
              ->orWhere('address_1', 'LIKE', "%{$term}%")
              ->orWhere('city', 'LIKE', "%{$term}%")
              ->orWhere('country', 'LIKE', "%{$term}%");
        });
    }

    /**
     * Vérifier si l'agence est active.
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Vérifier si l'agence est inactive.
     */
    public function isInactive()
    {
        return $this->status === 'inactive';
    }

    /**
     * Activer l'agence.
     */
    public function activate()
    {
        return $this->update(['status' => 'active']);
    }

    /**
     * Désactiver l'agence.
     */
    public function deactivate()
    {
        return $this->update(['status' => 'inactive']);
    }

    /**
     * Obtenir l'adresse complète.
     */
    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->address_1,
            $this->address_2,
            $this->city,
            $this->country
        ]);

        return implode(', ', $parts);
    }

    /**
     * Obtenir le nom du statut formaté.
     */
    public function getStatusNameAttribute()
    {
        return match($this->status) {
            'active' => 'Active',
            'inactive' => 'Inactive',
            default => 'Non défini'
        };
    }

    /**
     * Obtenir la couleur du badge de statut.
     */
    public function getStatusBadgeColorAttribute()
    {
        return match($this->status) {
            'active' => 'success',
            'inactive' => 'warning',
            default => 'secondary'
        };
    }

    /**
     * Obtenir l'icône du statut.
     */
    public function getStatusIconAttribute()
    {
        return match($this->status) {
            'active' => 'check-circle',
            'inactive' => 'pause-circle',
            default => 'help-circle'
        };
    }

    /**
     * Obtenir l'âge de l'agence formaté.
     */
    public function getAgeFormattedAttribute()
    {
        try {
            $diff = $this->created_at->diff(now());
            
            if ($diff->y > 0) {
                return $diff->y . ' an(s)';
            } elseif ($diff->m > 0) {
                return $diff->m . ' mois';
            } elseif ($diff->d > 0) {
                return $diff->d . ' jour(s)';
            } elseif ($diff->h > 0) {
                return $diff->h . ' heure(s)';
            } else {
                return 'Moins d\'une heure';
            }
        } catch (\Exception $e) {
            return 'Calcul impossible';
        }
    }

    /**
     * Rechercher des agences par différents critères.
     */
    public static function searchAgencies($filters = [])
    {
        $query = self::query();

        // Recherche textuelle
        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Filtre par statut
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'active') {
                $query->active();
            } elseif ($filters['status'] === 'inactive') {
                $query->inactive();
            }
        }

        // Filtre par pays
        if (!empty($filters['country'])) {
            $query->where('country', $filters['country']);
        }

        // Filtre par ville
        if (!empty($filters['city'])) {
            $query->where('city', 'LIKE', "%{$filters['city']}%");
        }

        // Filtre récent
        if (!empty($filters['recent'])) {
            $query->recent((int) $filters['recent']);
        }

        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Obtenir les statistiques des agences.
     */
    public static function getStats()
    {
        return [
            'total' => self::count(),
            'active' => self::active()->count(),
            'inactive' => self::inactive()->count(),
            'recent' => self::recent(7)->count(),
        ];
    }

    /**
     * Validation rules pour la création/modification.
     */
    public static function validationRules($agencyId = null)
    {
        $uniqueRule = $agencyId ? "unique:agencies,name,{$agencyId}" : 'unique:agencies,name';

        return [
            'name' => ['required', 'string', 'max:255', $uniqueRule],
            'phone' => ['required', 'string', 'max:20'],
            'address_1' => ['required', 'string', 'max:255'],
            'address_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'country' => ['required', 'string', 'max:100'],
            'status' => ['nullable', 'in:active,inactive'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Messages de validation personnalisés.
     */
    public static function validationMessages()
    {
        return [
            'name.required' => 'Le nom de l\'agence est obligatoire.',
            'name.unique' => 'Ce nom d\'agence existe déjà.',
            'name.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'phone.required' => 'Le téléphone est obligatoire.',
            'phone.max' => 'Le téléphone ne peut pas dépasser 20 caractères.',
            'address_1.required' => 'L\'adresse principale est obligatoire.',
            'address_1.max' => 'L\'adresse principale ne peut pas dépasser 255 caractères.',
            'address_2.max' => 'L\'adresse complémentaire ne peut pas dépasser 255 caractères.',
            'city.required' => 'La ville est obligatoire.',
            'city.max' => 'La ville ne peut pas dépasser 100 caractères.',
            'country.required' => 'Le pays est obligatoire.',
            'country.max' => 'Le pays ne peut pas dépasser 100 caractères.',
            'status.in' => 'Le statut doit être "active" ou "inactive".',
            'notes.max' => 'Les notes ne peuvent pas dépasser 1000 caractères.',
        ];
    }
}