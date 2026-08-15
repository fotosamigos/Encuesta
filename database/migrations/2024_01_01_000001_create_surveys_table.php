<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surveys', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();

            // Imagen de portada. Se guarda ruta local (storage) o URL externa.
            $table->string('cover_image_path')->nullable();
            $table->string('cover_image_url')->nullable();

            // Visibilidad: true = cualquier cuenta (natural + encuestador)
            // false = "especial", solo encuestadores con acceso concedido
            $table->boolean('is_public')->default(true);

            // Publicación / vigencia de la encuesta
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surveys');
    }
};
