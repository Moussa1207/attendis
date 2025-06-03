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
   
    /**
     * Relation avec les utilisateurs
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
    
    /**
     * Méthodes utilitaires pour les types
     */
    public static function getAdminType()
    {
        return self::find(1); // ID 1 = Administrateur
    }
    
    public static function getUserType()
    {
        return self::find(2); // ID 2 = Utilisateur normal
    }
    
    /**
     * Obtenir tous les utilisateurs de ce type
     */
    public function getUsersCount()
    {
        return $this->users()->count();
    }
    
    /**
     * Obtenir les utilisateurs actifs de ce type
     */
    public function getActiveUsersCount()
    {
        return $this->users()->where('status_id', 2)->count();
    }
    
    /**
     * Vérifier si c'est le type administrateur
     */
    public function isAdminType()
    {
        return $this->id === 1;
    }
    
    /**
     * Vérifier si c'est le type utilisateur normal
     */
    public function isUserType()
    {
        return $this->id === 2;
    }
    
    /**
     * Obtenir les statistiques de ce type
     */
    public function getStats()
    {
        return [
            'total_users' => $this->getUsersCount(),
            'active_users' => $this->getActiveUsersCount(),
            'inactive_users' => $this->users()->where('status_id', 1)->count(),
            'suspended_users' => $this->users()->where('status_id', 3)->count(),
        ];
    }
}