<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('rol_id')->nullable()->after('id')->constrained('roles')->nullOnDelete();
            $table->string('telefono', 20)->nullable()->after('email');
            $table->date('fecha_nacimiento')->nullable()->after('telefono');
            $table->enum('estado_verificacion', ['pendiente', 'en_proceso', 'aprobada', 'rechazada', 'en_revision'])
                ->default('pendiente')
                ->after('fecha_nacimiento');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rol_id');
            $table->dropColumn(['telefono', 'fecha_nacimiento', 'estado_verificacion']);
        });
    }
};
