<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ✅ NOUVELLE MIGRATION : Ajouter les champs de tracking pour les utilisateurs
     * 
     * Cette migration ajoute les champs nécessaires pour tracker :
     * - Les connexions (dernière connexion, tentatives échouées)
     * - Les changements de mot de passe
     * - Les informations de sécurité
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // ✅ TRACKING DES CONNEXIONS
            $table->timestamp('last_login_at')->nullable()->after('updated_at')
                ->comment('Dernière connexion réussie de l\'utilisateur');
            
            $table->integer('failed_login_attempts')->default(0)->after('last_login_at')
                ->comment('Nombre de tentatives de connexion échouées consécutives');
            
            // ✅ TRACKING DES MOTS DE PASSE
            $table->timestamp('last_password_change')->nullable()->after('failed_login_attempts')
                ->comment('Date du dernier changement de mot de passe');
            
            // ✅ INFORMATIONS DE SÉCURITÉ SUPPLÉMENTAIRES (optionnelles)
            $table->string('last_login_ip', 45)->nullable()->after('last_password_change')
                ->comment('Adresse IP de la dernière connexion');
            
            $table->text('last_user_agent')->nullable()->after('last_login_ip')
                ->comment('User agent de la dernière connexion');
            
            // ✅ INDEX POUR OPTIMISER LES PERFORMANCES
            $table->index(['last_login_at'], 'idx_users_last_login');
            $table->index(['failed_login_attempts'], 'idx_users_failed_attempts');
            $table->index(['last_password_change'], 'idx_users_password_change');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Supprimer les index d'abord
            $table->dropIndex('idx_users_last_login');
            $table->dropIndex('idx_users_failed_attempts');
            $table->dropIndex('idx_users_password_change');
            
            // Supprimer les colonnes
            $table->dropColumn([
                'last_login_at',
                'failed_login_attempts', 
                'last_password_change',
                'last_login_ip',
                'last_user_agent'
            ]);
        });
    }
};