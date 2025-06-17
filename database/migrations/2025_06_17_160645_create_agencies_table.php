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
        Schema::create('agencies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Nom de l'agence
            $table->string('phone', 20); // Téléphone
            $table->string('address_1'); // Adresse principale
            $table->string('address_2')->nullable(); // Adresse complémentaire
            $table->string('city', 100); // Ville
            $table->string('country', 100); // Pays
            $table->enum('status', ['active', 'inactive'])->default('active'); // Statut
            $table->text('notes')->nullable(); // Notes optionnelles
            $table->unsignedBigInteger('created_by')->nullable(); // Créateur
            $table->timestamps();
            $table->softDeletes();

            // Index pour améliorer les performances
            $table->index(['status']);
            $table->index(['country']);
            $table->index(['city']);
            $table->index(['created_at']);
            $table->index(['created_by']);

            // Clés étrangères
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agencies');
    }
};