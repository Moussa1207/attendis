<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ✅ MIGRATION COMPLÉMENTAIRE : Ajouter les colonnes manquantes pour le système de transfert
     */
    public function up(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            // ✅ Colonnes déjà présentes (à vérifier) :
            // - conseiller_transfert (ID du conseiller qui a transféré)
            // - transferer (statut : 'new', 'transferé', 'No')
            
            // ✅ NOUVELLES COLONNES POUR LE SYSTÈME COLLABORATIF :
            
            // Motif du transfert (obligatoire selon votre validation)
            $table->text('transfer_reason')->nullable()->after('transferer')->comment('Motif du transfert (obligatoire)');
            
            // Notes additionnelles du transfert (optionnel)
            $table->text('transfer_notes')->nullable()->after('transfer_reason')->comment('Notes additionnelles du transfert');
            
            // Timestamp du transfert pour l'historique
            $table->timestamp('transfer_timestamp')->nullable()->after('transfer_notes')->comment('Horodatage du transfert');
            
            // Service de destination en cas de transfert vers un autre service
            $table->unsignedBigInteger('transfer_to_service_id')->nullable()->after('transfer_timestamp')->comment('Service de destination du transfert');
            
            // Commentaire de résolution (pour les refus avec commentaire obligatoire)
            if (!Schema::hasColumn('queues', 'commentaire_resolution')) {
                $table->text('commentaire_resolution')->nullable()->after('resolu')->comment('Commentaire de résolution (obligatoire pour refus)');
            }
            
            // ✅ INDEX POUR OPTIMISER LES REQUÊTES DE TRANSFERT
            $table->index(['transferer', 'statut_global', 'date'], 'idx_queue_transfer_status');
            $table->index(['conseiller_transfert', 'date'], 'idx_queue_transfer_from');
            $table->index(['transfer_to_service_id'], 'idx_queue_transfer_to_service');
        });
        
        // ✅ MISE À JOUR DES DONNÉES EXISTANTES
        DB::table('queues')
            ->whereNull('transfer_reason')
            ->where('transferer', '!=', 'No')
            ->where('transferer', '!=', 'no')
            ->whereNotNull('transferer')
            ->update([
                'transfer_reason' => 'Transfert effectué avant mise à jour système',
                'transfer_timestamp' => now()
            ]);

        \Log::info('Migration colonnes transfert terminée', [
            'nouvelles_colonnes' => ['transfer_reason', 'transfer_notes', 'transfer_timestamp', 'transfer_to_service_id'],
            'index_ajoutes' => 3
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            // Supprimer les index
            $table->dropIndex('idx_queue_transfer_status');
            $table->dropIndex('idx_queue_transfer_from');
            $table->dropIndex('idx_queue_transfer_to_service');
            
            // Supprimer les colonnes
            $table->dropColumn([
                'transfer_reason',
                'transfer_notes', 
                'transfer_timestamp',
                'transfer_to_service_id'
            ]);
        });
    }
};