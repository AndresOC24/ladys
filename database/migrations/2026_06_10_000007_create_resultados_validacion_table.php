<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resultados_validacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registro_verificacion_id')->constrained('registros_verificacion')->cascadeOnDelete();
            $table->enum('tipo', ['facial', 'ocr', 'liveness']);
            $table->decimal('puntaje', 6, 4)->nullable();
            $table->enum('resultado', ['aprobado', 'rechazado', 'dudoso']);
            $table->json('detalles')->nullable();
            $table->timestamps();

            $table->index(['registro_verificacion_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resultados_validacion');
    }
};
