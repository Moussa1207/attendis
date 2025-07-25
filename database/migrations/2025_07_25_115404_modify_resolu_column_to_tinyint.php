<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * ✅ MIGRATION : Modifier la colonne 'resolu' de enum vers tinyint
     * 
     * Avant : enum('Yes', 'No', 'En cours') 
     * Après : tinyint (0 = non résolu, 1 = résolu)
     */
    public function up(): void
    {
        // ===============================================
        // ÉTAPE 1 : Sauvegarder les données existantes
        // ===============================================
        $existingData = DB::table('queues')->get(['id', 'resolu']);
        
        // ===============================================
        // ÉTAPE 2 : Modifier la structure de la table
        // ===============================================
        Schema::table('queues', function (Blueprint $table) {
            // Supprimer l'ancienne colonne enum
            $table->dropColumn('resolu');
        });
        
        Schema::table('queues', function (Blueprint $table) {
            // Ajouter la nouvelle colonne tinyint
            $table->tinyInteger('resolu')->default(1)->comment('État de résolution: 0=non résolu, 1=résolu')->after('conseiller_transfert');
        });
        
        // ===============================================
        // ÉTAPE 3 : Migrer les données existantes
        // ===============================================
        foreach ($existingData as $row) {
            $newValue = match($row->resolu) {
                'Yes' => 1,        // Résolu
                'No' => 0,         // Non résolu
                'En cours' => 1,   // Par défaut : résolu (ticket en cours sera géré par statut_global)
                default => 1
            };
            
            DB::table('queues')
                ->where('id', $row->id)
                ->update(['resolu' => $newValue]);
        }
        
        // ===============================================
        // ÉTAPE 4 : Mettre à jour les tickets en cours
        // ===============================================
        // Les tickets avec statut_global = 'en_attente' ou 'en_cours' restent resolu = 1 par défaut
        // Seuls les tickets refusés auront resolu = 0
        
        \Log::info('Migration resolu terminée', [
            'total_tickets_migres' => $existingData->count(),
            'mapping' => 'Yes->1, No->0, En cours->1'
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ===============================================
        // SAUVEGARDER LES DONNÉES ACTUELLES
        // ===============================================
        $currentData = DB::table('queues')->get(['id', 'resolu']);
        
        // ===============================================
        // RESTAURER L'ANCIENNE STRUCTURE
        // ===============================================
        Schema::table('queues', function (Blueprint $table) {
            $table->dropColumn('resolu');
        });
        
        Schema::table('queues', function (Blueprint $table) {
            $table->enum('resolu', ['Yes', 'No', 'En cours'])->default('En cours')->comment('État de résolution')->after('conseiller_transfert');
        });
        
        // ===============================================
        // RESTAURER LES DONNÉES
        // ===============================================
        foreach ($currentData as $row) {
            $oldValue = match($row->resolu) {
                1 => 'Yes',        // Résolu
                0 => 'No',         // Non résolu
                default => 'En cours'
            };
            
            DB::table('queues')
                ->where('id', $row->id)
                ->update(['resolu' => $oldValue]);
        }
    }
};