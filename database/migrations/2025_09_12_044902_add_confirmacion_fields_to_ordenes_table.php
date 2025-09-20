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
        Schema::table('ordenes', function (Blueprint $table) {
            // Modificar la columna estado existente
            $table->enum('estado', ['pendiente', 'confirmada', 'cancelada'])->default('pendiente')->change();
            $table->timestamp('fecha_confirmacion')->nullable();
            $table->decimal('total_pagado', 10, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordenes', function (Blueprint $table) {
            // Restaurar la columna estado original
            $table->enum('estado', ['pendiente', 'procesando', 'completada', 'cancelada'])->default('pendiente')->change();
            $table->dropColumn(['fecha_confirmacion', 'total_pagado']);
        });
    }
};
