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
        Schema::create('marcas', function (Blueprint $table) {
            $table->id('id_marca');
            $table->string('descripcion', 200)->nullable();
            $table->string('pais', 100)->nullable();
            $table->string('fabricante', 150)->nullable();
            $table->unsignedBigInteger('id_grupo')->nullable();
            $table->boolean('activo')->default(1);
        // Auditoría
            $table->unsignedBigInteger('creado_por');
            $table->dateTime('fecha_creado');
            $table->unsignedBigInteger('modificado_por')->nullable();
            $table->dateTime('fecha_mod')->nullable();

            $table->primary('id_marca');
            $table->index('id_grupo');
            $table->index('creado_por');
            $table->index('modificado_por');
            $table->foreign('id_grupo')->references('id_grupo')->on('grupos')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('creado_por')->references('id')->on('users');
            $table->foreign('modificado_por')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marcas');
    }
};
