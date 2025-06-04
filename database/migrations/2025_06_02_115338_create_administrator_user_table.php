<?php

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
            
            // CHAMPS SUPPLÉMENTAIRES pour cohérence avec UserManagementController
            $table->string('creation_method')->default('manual'); // manual, import, api
            $table->text('creation_notes')->nullable(); // Notes de l'admin
            $table->boolean('password_reset_required')->default(true); // Force reset password
            $table->timestamp('password_reset_sent_at')->nullable(); // Quand envoyé
            
            $table->timestamps();
           
            // Relations avec cascade
            $table->foreign('administrator_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
           
            // Index pour performance
            $table->index(['administrator_id', 'user_id']);
            $table->unique(['user_id']); // Un utilisateur ne peut être créé que par un seul admin
            
            // Index supplémentaires pour les requêtes fréquentes
            $table->index('password_reset_required');
            $table->index('creation_method');
        });
    }

    public function down()
    {
        Schema::dropIfExists('administrator_user');
    }
}