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
        Schema::create('imagenes_sistema', function (Blueprint $table) {
            $table->id();
            $table->string('uso', 50); // logo_header, login_ilustracion, favicon, loader
            $table->string('ruta', 500);
            $table->string('nombre_archivo', 255)->nullable();
            $table->boolean('es_activa')->default(false);
            $table->integer('tamaño_kb')->nullable();
            $table->integer('ancho')->nullable();
            $table->integer('alto')->nullable();
            $table->timestamps();

            // coloco indices para la busqueda rapida
            $table->index('uso');
            $table->index('es_activa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imagenes_sistema');
    }
};
