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
        Schema::table('users', function (Blueprint $table) {
            // Ajouter la colonne agency_id après company
            $table->unsignedBigInteger('agency_id')->nullable()->after('company');
            
            // Ajouter la clé étrangère
            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('set null');
            
            // Ajouter un index pour les performances
            $table->index('agency_id', 'idx_users_agency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Supprimer l'index
            $table->dropIndex('idx_users_agency');
            
            // Supprimer la clé étrangère
            $table->dropForeign(['agency_id']);
            
            // Supprimer la colonne
            $table->dropColumn('agency_id');
        });
    }
};