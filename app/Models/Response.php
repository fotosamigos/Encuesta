<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Response extends Model
{
    use HasFactory;

    public const RESPONDENT_NATURAL = 'natural';
    public const RESPONDENT_ENCUESTADOR = 'encuestador';

    protected $fillable = [
        'survey_id',
        'respondent_type',
        'respondent_id',
        'latitude',
        'longitude',
        'location_country',
        'location_timezone',
        'encuestador_sequence_number',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'submitted_at' => 'datetime',
        ];
    }

    // -------- Relaciones --------

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function respondent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'respondent_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    public function hasLocation(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    /**
     * Texto listo para mostrar en el mapa/resultados, ej:
     * "Hora peruana: 2:34am | Hora argentina: 4:34am"
     * Si la respuesta vino desde Perú, solo muestra la hora peruana.
     */
    public function getDualTimeLabelAttribute(): string
    {
        $peru = $this->submitted_at->clone()->setTimezone('America/Lima');
        $label = 'Hora peruana: ' . $peru->format('g:ia');

        if ($this->location_timezone && $this->location_timezone !== 'America/Lima') {
            $local = $this->submitted_at->clone()->setTimezone($this->location_timezone);
            $countryLabel = $this->location_country ? "Hora de {$this->location_country}" : 'Hora local';
            $label .= " | {$countryLabel}: " . $local->format('g:ia');
        }

        return $label;
    }
}
