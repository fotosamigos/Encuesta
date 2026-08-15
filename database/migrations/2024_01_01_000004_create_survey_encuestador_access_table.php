<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Controla, por encuesta y por encuestador, dos permisos independientes:
     * - can_respond: puede responder esta encuesta (relevante sobre todo
     *   para encuestas "especiales" con is_public = false)
     * - can_view_results: puede ver resultados / estadísticas / mapa
     *   en tiempo real de ESTA encuesta puntual.
     *
     * Que un encuestador tenga acceso a la encuesta A no le da
     * automáticamente acceso a la encuesta B.
     */
    public function up(): void
    {
        Schema::create('survey_encuestador_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->boolean('can_respond')->default(false);
            $table->boolean('can_view_results')->default(false);

            $table->timestamps();

            $table->unique(['survey_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_encuestador_access');
    }
};
