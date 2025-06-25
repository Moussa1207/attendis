<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); //Clé unique du paramètre
            $table->text('value')->nullable(); //Valeur du paramètre (JSON, string, etc.)
            $table->string('type')->default('string'); //Type de données: string, boolean, integer, json, time'
            $table->string('group')->default('general');//'Groupe de paramètres: general, security, user_management, etc.'
            $table->string('label')->nullable(); //Libellé affiché dans l\'interface
            $table->text('description')->nullable(); //'Description détaillée du paramètre'
            $table->json('meta')->nullable(); //Métadonnées additionnelles (validation, options, etc.)
            $table->boolean('is_active')->default(true); //Paramètre actif ou non
            $table->integer('sort_order')->default(0) ; //Ordre d\'affichage'
            $table->timestamps();

            // Index pour améliorer les performances
            $table->index(['key'], 'idx_settings_key');
            $table->index(['group'], 'idx_settings_group');
            $table->index(['is_active'], 'idx_settings_active');
            $table->index(['group', 'sort_order'], 'idx_settings_group_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};