<?php
// database/migrations/2025_06_03_create_administrator_user_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdministratorUserTable extends Migration
{
    public function up()
    {
        Schema::create('administrator_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('administrator_id'); // Qui a créé
            $table->unsignedBigInteger('user_id'); // Qui a été créé
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            // Relations avec cascade
            $table->foreign('administrator_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Index pour performance
            $table->index(['administrator_id', 'user_id']);
            $table->unique(['user_id']); // Un utilisateur ne peut être créé que par un seul admin
        });
    }

    public function down()
    {
        Schema::dropIfExists('administrator_user');
    }
}