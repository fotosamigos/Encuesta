<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega los campos necesarios para diferenciar admin / encuestador
     * y guardar la personalización (color) de cada encuestador.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 'admin' | 'encuestador'
            $table->string('role')->default('encuestador')->after('email');

            // Color hex (#RRGGBB) que identifica al encuestador en el mapa.
            // Puede venir de una paleta fija o ser un hex libre elegido por el admin.
            $table->string('color', 7)->nullable()->after('role');

            // El admin puede deshabilitar cuentas de encuestador sin borrarlas.
            $table->boolean('is_active')->default(true)->after('color');

            // Referencia a qué admin creó esta cuenta de encuestador (auditoría simple).
            $table->foreignId('created_by')->nullable()->after('is_active')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn(['role', 'color', 'is_active']);
        });
    }
};
