<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Survey extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'cover_image_path',
        'cover_image_url',
        'is_public',
        'is_active',
        'starts_at',
        'ends_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    // -------- Relaciones --------

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(Response::class);
    }

    /** Encuestadores con algún permiso (respuesta y/o resultados) sobre esta encuesta. */
    public function authorizedEncuestadores(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'survey_encuestador_access')
            ->withPivot(['can_respond', 'can_view_results'])
            ->withTimestamps();
    }

    // -------- Helpers --------

    /** ¿Puede este usuario (o null = visitante anónimo) ver/responder la encuesta? */
    public function isVisibleTo(?User $user): bool
    {
        if ($this->is_public) {
            return true; // persona natural, encuestador o admin
        }

        // Encuesta especial: solo admin o encuestador con can_respond = true
        if (!$user) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $this->authorizedEncuestadores()
            ->wherePivot('user_id', $user->id)
            ->wherePivot('can_respond', true)
            ->exists();
    }

    public function isActiveNow(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->starts_at && $now->lt($this->starts_at)) {
            return false;
        }

        if ($this->ends_at && $now->gt($this->ends_at)) {
            return false;
        }

        return true;
    }
}
