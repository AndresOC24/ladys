<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('datos_documento', function (Blueprint $table) {
            $table->string('serie', 20)->nullable()->after('numero_cedula');
            $table->string('seccion', 20)->nullable()->after('serie');
            $table->string('domicilio')->nullable()->after('lugar_nacimiento');
            $table->string('ocupacion')->nullable()->after('domicilio');
            $table->string('estado_civil', 50)->nullable()->after('ocupacion');
        });

        // De enum a string para poder sumar el tipo "rostro_cedula" (recorte
        // del rostro extraído del documento) sin ALTERs específicos de MySQL;
        // los valores válidos los controla la aplicación.
        Schema::table('documentos', function (Blueprint $table) {
            $table->string('tipo', 20)->change();
        });
    }

    public function down(): void
    {
        Schema::table('datos_documento', function (Blueprint $table) {
            $table->dropColumn(['serie', 'seccion', 'domicilio', 'ocupacion', 'estado_civil']);
        });
    }
};
