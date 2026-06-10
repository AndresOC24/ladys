<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registro_verificacion_id')->constrained('registros_verificacion')->cascadeOnDelete();
            $table->enum('tipo', ['anverso', 'reverso', 'selfie']);
            $table->string('ruta_archivo');
            $table->string('hash_archivo', 64);
            $table->timestamps();

            $table->index(['registro_verificacion_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
