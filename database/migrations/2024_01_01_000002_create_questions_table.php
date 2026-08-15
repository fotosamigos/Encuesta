<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->cascadeOnDelete();

            $table->string('title');
            $table->text('description')->nullable();

            // Tipos soportados:
            // short_text, paragraph, single_choice, multiple_choice,
            // dual_choice (opción principal/secundaria), linear_scale,
            // date, time
            $table->string('type');

            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('order')->default(0);

            // Solo aplica a multiple_choice: cuántas opciones se pueden/deben marcar.
            // Si min_select == max_select, la UI puede mostrar "obligatoriamente N opciones".
            $table->unsignedTinyInteger('min_select')->nullable();
            $table->unsignedTinyInteger('max_select')->nullable();

            // Para linear_scale
            $table->unsignedTinyInteger('scale_min')->nullable();
            $table->unsignedTinyInteger('scale_max')->nullable();
            $table->string('scale_min_label')->nullable();
            $table->string('scale_max_label')->nullable();

            // Si esta pregunta específica debe pedir ubicación al respondiente.
            $table->boolean('ask_location')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
