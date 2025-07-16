<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            
            // ✅ Informations principales du service
            $table->string('nom', 100)->comment('Nom du service');
            $table->string('letter_of_service', 3)->comment('Lettre d\'identification du service (1-3 caractères)');
            $table->text('description')->nullable()->comment('Description détaillée du service');
            
            // ✅ Statut et état
            $table->enum('statut', ['actif', 'inactif'])->default('actif')->comment('Statut opérationnel du service');
            
            // ✅ Informations de création et gestion
            $table->unsignedBigInteger('created_by')->comment('ID de l\'administrateur créateur');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            
            // ✅ Timestamps
            $table->timestamps();
            
            // ✅ INDEX COMPOSÉ pour l'unicité par créateur
            $table->unique(['letter_of_service', 'created_by'], 'unique_letter_per_creator');
            
            // ✅ Index de performance
            $table->index(['statut', 'created_by'], 'idx_statut_creator');
            $table->index('created_at', 'idx_created_at');
            $table->index('nom', 'idx_nom');
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