<?php

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

    // Relations
    public function userType()
    {
        return $this->belongsTo(UserType::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    // Méthodes utilitaires
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