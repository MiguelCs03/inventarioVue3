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
        Schema::create('grupos', function (Blueprint $table) {
           $table->id('id_grupo');
            $table->string('descripcion', 200)->nullable();
            $table->boolean('activo')->default(1);
        // Auditoría
            $table->unsignedBigInteger('creado_por');
            $table->dateTime('fecha_creado');
            $table->unsignedBigInteger('modificado_por')->nullable();
            $table->dateTime('fecha_mod')->nullable();

            $table->primary('id_grupo');
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
        Schema::dropIfExists('grupos');
    }
};
