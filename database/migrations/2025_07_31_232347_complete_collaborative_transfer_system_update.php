<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * ✅ MIGRATION COMPLÈTE : Alignement du système de transfert collaboratif
     * 
     * Cette migration corrige TOUTES les incohérences entre la BDD et le code :
     * 1. Transforme 'resolu' de enum vers tinyint
     * 2. Modifie 'transferer' pour supporter 'new', 'transferé', 'No'
     * 3. Ajoute toutes les colonnes manquantes pour le système collaboratif
     */
    public function up(): void
    {
        // ===============================================
        // ÉTAPE 1 : SAUVEGARDER LES DONNÉES EXISTANTES
        // ===============================================
        $existingData = DB::table('queues')->get(['id', 'resolu', 'transferer']);
        
        // ===============================================
        // ÉTAPE 2 : MODIFIER LA COLONNE 'resolu' (enum -> tinyint)
        // ===============================================
        Schema::table('queues', function (Blueprint $table) {
            $table->dropColumn('resolu');
        });
        
        Schema::table('queues', function (Blueprint $table) {
            $table->tinyInteger('resolu')
                  ->default(1)
                  ->comment('État de résolution: 0=non résolu, 1=résolu')
                  ->after('conseiller_transfert');
        });
        
        // Migrer les données 'resolu'
        foreach ($existingData as $row) {
            $newResoluValue = match($row->resolu) {
                'Yes' => 1,        // Résolu
                'No' => 0,         // Non résolu
                'En cours' => 1,   // Par défaut : résolu
                default => 1
            };
            
            DB::table('queues')
                ->where('id', $row->id)
                ->update(['resolu' => $newResoluValue]);
        }
        
        // ===============================================
        // ÉTAPE 3 : MODIFIER LA COLONNE 'transferer' (enum -> varchar)
        // ===============================================
        Schema::table('queues', function (Blueprint $table) {
            $table->dropColumn('transferer');
        });
        
        Schema::table('queues', function (Blueprint $table) {
            $table->string('transferer', 20)
                  ->default('No')
                  ->comment('Statut transfert: new=reçu(priorité), transferé=envoyé, No=normal')
                  ->after('commentaire_resolution');
        });
        
        // Migrer les données 'transferer'
        foreach ($existingData as $row) {
            $newTransfererValue = match($row->transferer) {
                'Yes' => 'transferé',  // Ancien système -> nouveau
                'No' => 'No',          // Reste No
                default => 'No'
            };
            
            DB::table('queues')
                ->where('id', $row->id)
                ->update(['transferer' => $newTransfererValue]);
        }
        
        // ===============================================
        // ÉTAPE 4 : AJOUTER LES NOUVELLES COLONNES COLLABORATIVES
        // ===============================================
        Schema::table('queues', function (Blueprint $table) {
            // Colonnes pour le système de transfert collaboratif
            if (!Schema::hasColumn('queues', 'transfer_reason')) {
                $table->text('transfer_reason')
                      ->nullable()
                      ->after('transferer')
                      ->comment('Motif du transfert (obligatoire lors du transfert)');
            }
            
            if (!Schema::hasColumn('queues', 'transfer_notes')) {
                $table->text('transfer_notes')
                      ->nullable()
                      ->after('transfer_reason')
                      ->comment('Notes additionnelles du transfert (optionnel)');
            }
            
            if (!Schema::hasColumn('queues', 'transfer_timestamp')) {
                $table->timestamp('transfer_timestamp')
                      ->nullable()
                      ->after('transfer_notes')
                      ->comment('Horodatage exact du transfert');
            }
            
            if (!Schema::hasColumn('queues', 'transfer_to_service_id')) {
                $table->unsignedBigInteger('transfer_to_service_id')
                      ->nullable()
                      ->after('transfer_timestamp')
                      ->comment('ID du service de destination si transfert de service');
            }
            
            // Clé étrangère pour transfer_to_service_id
            $table->foreign('transfer_to_service_id')
                  ->references('id')
                  ->on('services')
                  ->onDelete('set null');
        });
        
        // ===============================================
        // ÉTAPE 5 : AJOUTER LES INDEX POUR PERFORMANCES
        // ===============================================
        Schema::table('queues', function (Blueprint $table) {
            // Index pour les requêtes de transfert collaboratif
            $table->index(['transferer', 'statut_global', 'date'], 'idx_transfer_priority');
            $table->index(['conseiller_transfert', 'date'], 'idx_transfer_from_advisor');
            $table->index(['transfer_to_service_id', 'date'], 'idx_transfer_to_service');
            $table->index(['transferer', 'created_at'], 'idx_transfer_chronological');
            
            // Index pour améliorer les performances FIFO avec priorité
            $table->index(['date', 'statut_global', 'transferer', 'created_at'], 'idx_fifo_priority');
        });
        
        // ===============================================
        // ÉTAPE 6 : NETTOYER LES DONNÉES EXISTANTES
        // ===============================================
        // Marquer les tickets transférés existants avec des valeurs par défaut
        DB::table('queues')
            ->where('transferer', 'transferé')
            ->whereNull('transfer_reason')
            ->update([
                'transfer_reason' => 'Transfert effectué avant mise à jour système collaboratif',
                'transfer_timestamp' => DB::raw('COALESCE(heure_transfert, updated_at)')
            ]);
        
        // Corriger les tickets avec conseiller_transfert mais pas marqués comme transférés
        DB::table('queues')
            ->whereNotNull('conseiller_transfert')
            ->where('transferer', 'No')
            ->update([
                'transferer' => 'transferé',
                'transfer_reason' => 'Correction automatique - ticket transféré détecté',
                'transfer_timestamp' => DB::raw('COALESCE(heure_transfert, updated_at)')
            ]);
        
        // ===============================================
        // ÉTAPE 7 : VALIDER LA MIGRATION
        // ===============================================
        $stats = [
            'total_tickets' => DB::table('queues')->count(),
            'tickets_resolus' => DB::table('queues')->where('resolu', 1)->count(),
            'tickets_non_resolus' => DB::table('queues')->where('resolu', 0)->count(),
            'tickets_new' => DB::table('queues')->where('transferer', 'new')->count(),
            'tickets_transferes' => DB::table('queues')->where('transferer', 'transferé')->count(),
            'tickets_normaux' => DB::table('queues')->where('transferer', 'No')->count(),
        ];
        
        \Log::info('Migration système collaboratif complète', $stats);
        
        // Afficher un résumé
        echo "\n=== MIGRATION SYSTÈME COLLABORATIF TERMINÉE ===\n";
        echo "Total tickets migrés : {$stats['total_tickets']}\n";
        echo "Tickets résolus (resolu=1) : {$stats['tickets_resolus']}\n";
        echo "Tickets non résolus (resolu=0) : {$stats['tickets_non_resolus']}\n";
        echo "Tickets avec statut 'new' : {$stats['tickets_new']}\n";
        echo "Tickets avec statut 'transferé' : {$stats['tickets_transferes']}\n";
        echo "Tickets normaux : {$stats['tickets_normaux']}\n";
        echo "===============================================\n\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ===============================================
        // SAUVEGARDER LES DONNÉES ACTUELLES
        // ===============================================
        $currentData = DB::table('queues')->get(['id', 'resolu', 'transferer']);
        
        // ===============================================
        // SUPPRIMER LES INDEX
        // ===============================================
        Schema::table('queues', function (Blueprint $table) {
            $table->dropIndex('idx_transfer_priority');
            $table->dropIndex('idx_transfer_from_advisor');
            $table->dropIndex('idx_transfer_to_service');
            $table->dropIndex('idx_transfer_chronological');
            $table->dropIndex('idx_fifo_priority');
        });
        
        // ===============================================
        // SUPPRIMER LES COLONNES AJOUTÉES
        // ===============================================
        Schema::table('queues', function (Blueprint $table) {
            $table->dropForeign(['transfer_to_service_id']);
            $table->dropColumn([
                'transfer_reason',
                'transfer_notes',
                'transfer_timestamp',
                'transfer_to_service_id'
            ]);
        });
        
        // ===============================================
        // RESTAURER 'transferer' EN ENUM
        // ===============================================
        Schema::table('queues', function (Blueprint $table) {
            $table->dropColumn('transferer');
        });
        
        Schema::table('queues', function (Blueprint $table) {
            $table->enum('transferer', ['Yes', 'No'])
                  ->default('No')
                  ->comment('Demande transférée ou non')
                  ->after('commentaire_resolution');
        });
        
        // Restaurer les données 'transferer'
        foreach ($currentData as $row) {
            $oldTransfererValue = match($row->transferer) {
                'new' => 'Yes',
                'transferé' => 'Yes',
                'No' => 'No',
                default => 'No'
            };
            
            DB::table('queues')
                ->where('id', $row->id)
                ->update(['transferer' => $oldTransfererValue]);
        }
        
        // ===============================================
        // RESTAURER 'resolu' EN ENUM
        // ===============================================
        Schema::table('queues', function (Blueprint $table) {
            $table->dropColumn('resolu');
        });
        
        Schema::table('queues', function (Blueprint $table) {
            $table->enum('resolu', ['Yes', 'No', 'En cours'])
                  ->default('En cours')
                  ->comment('État de résolution')
                  ->after('conseiller_transfert');
        });
        
        // Restaurer les données 'resolu'
        foreach ($currentData as $row) {
            $oldResoluValue = match($row->resolu) {
                1 => 'Yes',
                0 => 'No',
                default => 'En cours'
            };
            
            DB::table('queues')
                ->where('id', $row->id)
                ->update(['resolu' => $oldResoluValue]);
        }
    }
};