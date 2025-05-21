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

    public function userType()
    {
        return $this->belongsTo(UserType::class);
    }
    
    public function status()
    {
        return $this->belongsTo(Status::class);
    }
    
    
    public function isAdmin()
    {
        // Supposons que l'ID 1 correspond au type administrateur
        return $this->user_type_id === 1;
    }
    
    // Méthode pour vérifier si l'utilisateur est actif
    public function isActive()
    {
        // Supposons que l'ID 1 correspond au statut actif
        return $this->status_id === 1;
    }
}