<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    // Tipos de pregunta soportados por el constructor.
    public const TYPE_SHORT_TEXT = 'short_text';
    public const TYPE_PARAGRAPH = 'paragraph';
    public const TYPE_SINGLE_CHOICE = 'single_choice';
    public const TYPE_MULTIPLE_CHOICE = 'multiple_choice';
    public const TYPE_DUAL_CHOICE = 'dual_choice'; // opción principal / secundaria
    public const TYPE_LINEAR_SCALE = 'linear_scale';
    public const TYPE_DATE = 'date';
    public const TYPE_TIME = 'time';

    public const TYPES_WITH_OPTIONS = [
        self::TYPE_SINGLE_CHOICE,
        self::TYPE_MULTIPLE_CHOICE,
        self::TYPE_DUAL_CHOICE,
    ];

    protected $fillable = [
        'survey_id',
        'title',
        'description',
        'type',
        'is_required',
        'order',
        'min_select',
        'max_select',
        'scale_min',
        'scale_max',
        'scale_min_label',
        'scale_max_label',
        'ask_location',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'ask_location' => 'boolean',
        ];
    }

    // -------- Relaciones --------

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('order');
    }

    public function principalOptions(): HasMany
    {
        return $this->options()->where('option_group', 'principal');
    }

    public function secundariaOptions(): HasMany
    {
        return $this->options()->where('option_group', 'secundaria');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    public function hasOptions(): bool
    {
        return in_array($this->type, self::TYPES_WITH_OPTIONS, true);
    }
}
