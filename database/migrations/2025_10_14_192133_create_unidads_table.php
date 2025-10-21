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
        Schema::create('unidades', function (Blueprint $table) {
            $table->id('id_unidad');
            $table->string('codigo', 50)->nullable();
            $table->string('descripcion', 150)->nullable();
            $table->boolean('activo')->default(1);
        // Auditoría
            $table->unsignedBigInteger('creado_por');
            $table->dateTime('fecha_creado');
            $table->unsignedBigInteger('modificado_por')->nullable();
            $table->dateTime('fecha_mod')->nullable();

            $table->primary('id_unidad');
            $table->index('creado_por');
            $table->index('modificado_por');
            $table->foreign('creado_por')->references('id')->on('users');
            $table->foreign('modificado_por')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unidades');
    }
};
