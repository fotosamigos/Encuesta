<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'label',
        'image_path',
        'image_url',
        'order',
        'option_group',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /** URL final a mostrar, priorizando el archivo subido sobre la URL externa. */
    public function getImageAttribute(): ?string
    {
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }

        return $this->image_url;
    }
}
