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
        Schema::table('menus', function (Blueprint $table) {
            $table->string('section')->nullable()->after('route')
                ->comment('Identificador para asociar con permisos');
        });

        Schema::table('submenus', function (Blueprint $table) {
            $table->string('section')->nullable()->after('route')
                ->comment('Identificador para asociar con permisos');
        });
        
        // Poblar los campos 'section' con valores predeterminados
        DB::statement('UPDATE menus SET section = LOWER(REPLACE(COALESCE(route, ""), "/", "")) WHERE section IS NULL');
        DB::statement('UPDATE menus SET section = LOWER(name) WHERE section = ""');
        
        DB::statement('UPDATE submenus SET section = LOWER(REPLACE(COALESCE(route, ""), "/", "")) WHERE section IS NULL');
        DB::statement('UPDATE submenus SET section = LOWER(name) WHERE section = ""');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropColumn('section');
        });

        Schema::table('submenus', function (Blueprint $table) {
            $table->dropColumn('section');
        });
    }
};
