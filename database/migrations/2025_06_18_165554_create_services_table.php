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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('code')->unique();
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->text('description')->nullable();
            
            // Relation avec l'utilisateur qui a créé le service
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            
            // Index pour les recherches
            $table->index(['statut']);
            $table->index(['created_by']);
            $table->index(['nom']);
            $table->index(['code']);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};