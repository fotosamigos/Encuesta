<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();

            $table->string('label');

            // Imagen de la opción: puede subirse como archivo o pegarse como URL.
            $table->string('image_path')->nullable();
            $table->string('image_url')->nullable();

            $table->unsignedInteger('order')->default(0);

            // Solo se usa cuando question.type == 'dual_choice':
            // 'principal' | 'secundaria'. Null para el resto de tipos.
            $table->string('option_group')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_options');
    }
};
