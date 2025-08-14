<?php
// database/migrations/2025_08_12_000001_unique_ticket_per_service_per_day.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            // ⚠️ adapte le nom si différent (SHOW INDEX FROM queues; pour vérifier)
            $table->dropUnique('queues_numero_ticket_unique');
            $table->unique(['service_id', 'date', 'numero_ticket'], 'queues_service_date_numero_unique');
        });
    }

    public function down(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            $table->dropUnique('queues_service_date_numero_unique');
            $table->unique('numero_ticket', 'queues_numero_ticket_unique');
        });
    }
};
