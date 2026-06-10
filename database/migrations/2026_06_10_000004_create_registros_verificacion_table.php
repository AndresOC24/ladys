<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registros_verificacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuaria_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('fecha_inicio')->nullable();
            $table->timestamp('fecha_resolucion')->nullable();
            $table->enum('estado', ['pendiente', 'en_proceso', 'aprobada', 'rechazada', 'en_revision'])
                ->default('pendiente');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registros_verificacion');
    }
};
