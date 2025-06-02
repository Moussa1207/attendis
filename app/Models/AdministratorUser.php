<?php
// app/Models/AdministratorUser.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdministratorUser extends Model
{
    protected $table = 'administrator_user';
    
    protected $fillable = [
        'administrator_id',
        'user_id'
    ];

    /**
     * Relation avec l'administrateur qui a créé l'utilisateur
     */
    public function administrator()
    {
        return $this->belongsTo(User::class, 'administrator_id');
    }

    /**
     * Relation avec l'utilisateur créé
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}