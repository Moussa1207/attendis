<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'username', 'email', 'password', 'mobile_number', 'user_type_id', 'status_id',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Relations existantes
    public function userType()
    {
        return $this->belongsTo(UserType::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    // Nouvelles relations pour administrator_user
    /**
     * Utilisateurs créés par cet administrateur
     */
    public function createdUsers()
    {
        return $this->belongsToMany(User::class, 'administrator_user', 'administrator_id', 'user_id')
                    ->withTimestamps();
    }

    /**
     * Administrateurs qui ont créé cet utilisateur
     */
    public function createdByAdministrators()
    {
        return $this->belongsToMany(User::class, 'administrator_user', 'user_id', 'administrator_id')
                    ->withTimestamps();
    }

    /**
     * Obtenir l'administrateur qui a créé cet utilisateur
     */
    public function createdBy()
    {
        return $this->createdByAdministrators()->first();
    }

    /**
     * Vérifier si cet utilisateur a été créé par un administrateur
     */
    public function wasCreatedByAdmin()
    {
        return $this->createdByAdministrators()->exists();
    }

    // Méthodes utilitaires existantes
    public function isAdmin()
    {
        return $this->user_type_id === 1; // ID 1 = Administrateur
    }

    public function isActive()
    {
        return $this->status_id === 2; // ID 2 = Actif
    }

    public function isInactive()
    {
        return $this->status_id === 1; // ID 1 = Inactif
    }

    public function isSuspended()
    {
        return $this->status_id === 3; // ID 3 = Suspendu
    }

    // Méthodes pour changer le statut
    public function activate()
    {
        $this->update(['status_id' => 2]);
    }

    public function deactivate()
    {
        $this->update(['status_id' => 1]);
    }

    public function suspend()
    {
        $this->update(['status_id' => 3]);
    }

    // Méthode pour obtenir le nom du type d'utilisateur
    public function getTypeName()
    {
        return $this->userType->name ?? 'Non défini';
    }

    // Méthode pour obtenir le nom du statut
    public function getStatusName()
    {
        return $this->status->name ?? 'Non défini';
    }
}