<?php
// database/migrations/xxxx_xx_xx_create_administrator_user_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdministratorUserTable extends Migration
{
    public function up()
    {
        Schema::create('administrator_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('administrator_id'); // ID de l'admin qui a créé
            $table->unsignedBigInteger('user_id'); // ID de l'utilisateur créé
            $table->timestamps();
            
            // Relations
            $table->foreign('administrator_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Éviter les doublons
            $table->unique(['administrator_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('administrator_user');
    }
}