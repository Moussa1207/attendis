<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ✅ MIGRATION : Table queue pour la gestion de file d'attente
     * 
     * Cette table gère le cycle de vie complet d'une demande client :
     * 1. Enregistrement (prise de ticket)
     * 2. Traitement (prise en charge conseiller)
     * 3. Finalisation (résolution ou transfert)
     */
    public function up(): void
    {
        Schema::create('queues', function (Blueprint $table) {
            $table->id();
            
            // ===============================================
            // INFORMATIONS AGENCE ET SERVICE
            // ===============================================
            $table->unsignedBigInteger('id_agence')->nullable()->comment('ID de l\'agence où se déroule la demande');
            $table->string('letter_of_service', 5)->comment('Lettre du service (copiée depuis la table services)');
            $table->unsignedBigInteger('service_id')->comment('ID du service demandé');
            
            // ===============================================
            // INFORMATIONS CLIENT (DEPUIS LE FORMULAIRE)
            // ===============================================
            $table->string('prenom', 100)->comment('Prénom du client (depuis formulaire app-ecran)');
            $table->string('telephone', 20)->comment('Téléphone du client (depuis formulaire app-ecran)');
            $table->text('commentaire')->nullable()->comment('Commentaire initial du client (depuis formulaire app-ecran)');
            
            // ===============================================
            // INFORMATIONS TEMPORELLES
            // ===============================================
            $table->date('date')->comment('Date du jour de la demande');
            $table->time('heure_d_enregistrement')->comment('Heure de prise de ticket à l\'accueil');
            $table->time('heure_prise_en_charge')->nullable()->comment('Heure où le conseiller prend en charge');
            $table->time('heure_de_fin')->nullable()->comment('Heure de fin de traitement');
            $table->time('heure_transfert')->nullable()->comment('Heure de transfert vers autre conseiller');
            
            // ===============================================
            // GESTION DU TRAITEMENT
            // ===============================================
            $table->unsignedBigInteger('conseiller_client_id')->nullable()->comment('ID du conseiller qui traite');
            $table->unsignedBigInteger('conseiller_transfert')->nullable()->comment('ID du conseiller de transfert');
            
            // ===============================================
            // ÉTATS ET STATUTS
            // ===============================================
            $table->enum('resolu', ['Yes', 'No', 'En cours'])->default('En cours')->comment('État de résolution');
            $table->text('commentaire_resolution')->nullable()->comment('Commentaire de résolution (obligatoire si No)');
            $table->enum('transferer', ['Yes', 'No'])->default('No')->comment('Demande transférée ou non');
            $table->enum('debut', ['Yes', 'No'])->default('No')->comment('Traitement commencé ou en attente');
            
            // ===============================================
            // NUMÉRO DE TICKET GÉNÉRÉ
            // ===============================================
            $table->string('numero_ticket', 10)->unique()->comment('Numéro de ticket généré (ex: A001, B023)');
            $table->integer('position_file')->default(1)->comment('Position dans la file d\'attente');
            $table->integer('temps_attente_estime')->nullable()->comment('Temps d\'attente estimé en minutes');
            
            // ===============================================
            // METADATA ET TRACKING
            // ===============================================
            $table->string('statut_global', 50)->default('en_attente')->comment('Statut global: en_attente, en_cours, termine, transfere');
            $table->json('historique')->nullable()->comment('Historique JSON des changements d\'état');
            $table->string('created_by_ip', 45)->nullable()->comment('IP de création du ticket');
            $table->text('notes_internes')->nullable()->comment('Notes internes pour les conseillers');
            
            // ===============================================
            // TIMESTAMPS STANDARD
            // ===============================================
            $table->timestamps();
            
            // ===============================================
            // CLÉS ÉTRANGÈRES
            // ===============================================
            $table->foreign('id_agence')->references('id')->on('agencies')->onDelete('set null');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            $table->foreign('conseiller_client_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('conseiller_transfert')->references('id')->on('users')->onDelete('set null');
            
            // ===============================================
            // INDEX POUR PERFORMANCES
            // ===============================================
            $table->index(['date', 'service_id'], 'idx_date_service');
            $table->index(['statut_global', 'date'], 'idx_statut_date');
            $table->index(['conseiller_client_id', 'date'], 'idx_conseiller_date');
            $table->index(['numero_ticket'], 'idx_numero_ticket');
            $table->index(['letter_of_service', 'date'], 'idx_letter_date');
            $table->index(['position_file', 'service_id'], 'idx_position_service');
            $table->index(['heure_d_enregistrement', 'date'], 'idx_heure_enregistrement');
            $table->index(['id_agence', 'date', 'service_id'], 'idx_agence_date_service');
        });      
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queues');
    }
};