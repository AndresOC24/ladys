<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revisiones_administrativas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registro_verificacion_id')->unique()->constrained('registros_verificacion')->cascadeOnDelete();
            $table->foreignId('administrador_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('decision', ['aprobada', 'rechazada', 'solicitar_reenvio']);
            $table->text('observaciones')->nullable();
            $table->timestamp('fecha_revision')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revisiones_administrativas');
    }
};
