<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('datos_documento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registro_verificacion_id')->unique()->constrained('registros_verificacion')->cascadeOnDelete();
            $table->string('numero_cedula', 20)->nullable();
            $table->string('nombre_completo')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->date('fecha_emision')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->string('lugar_nacimiento')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('datos_documento');
    }
};
