<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
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
     * Méthodes utilitaires pour les statuts
     */
    public static function getInactiveStatus()
    {
        return self::find(1); // ID 1 = Inactif
    }
    
    public static function getActiveStatus()
    {
        return self::find(2); // ID 2 = Actif
    }
    
    public static function getSuspendedStatus()
    {
        return self::find(3); // ID 3 = Suspendu
    }
    
    /**
     * Obtenir tous les utilisateurs avec ce statut
     */
    public function getUsersCount()
    {
        return $this->users()->count();
    }
    
    /**
     * Vérifier si c'est le statut actif
     */
    public function isActiveStatus()
    {
        return $this->id === 2;
    }
    
    /**
     * Vérifier si c'est le statut inactif
     */
    public function isInactiveStatus()
    {
        return $this->id === 1;
    }
    
    /**
     * Vérifier si c'est le statut suspendu
     */
    public function isSuspendedStatus()
    {
        return $this->id === 3;
    }
}