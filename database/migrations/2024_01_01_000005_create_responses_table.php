<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->cascadeOnDelete();

            // 'natural' | 'encuestador'
            $table->string('respondent_type');

            // Solo se llena cuando respondent_type == 'encuestador'
            $table->foreignId('respondent_id')->nullable()
                ->constrained('users')->nullOnDelete();

            // Ubicación (solo si la pregunta la pidió y el usuario la brindó)
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Resueltos vía reverse-geocoding al momento de guardar la respuesta,
            // para poder mostrar "Hora peruana: 2:34am | Hora argentina: 4:34am"
            // sin tener que volver a golpear un servicio externo cada vez.
            $table->string('location_country')->nullable();
            $table->string('location_timezone')->nullable(); // ej: America/Argentina/Buenos_Aires

            // Contador correlativo POR encuesta y POR encuestador (1, 2, 3...).
            // Se reinicia en cada encuesta nueva. Null si respondent_type == 'natural'.
            $table->unsignedInteger('encuestador_sequence_number')->nullable();

            $table->timestamp('submitted_at');

            $table->timestamps();

            $table->index(['survey_id', 'respondent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('responses');
    }
};
