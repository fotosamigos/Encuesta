<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'color',
        'is_active',
        'created_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // -------- Helpers de rol --------

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isEncuestador(): bool
    {
        return $this->role === 'encuestador';
    }

    // -------- Relaciones --------

    /** Encuestas creadas por este usuario (solo admins). */
    public function surveysCreated(): HasMany
    {
        return $this->hasMany(Survey::class, 'created_by');
    }

    /** Admin que creó esta cuenta de encuestador. */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Encuestas a las que este encuestador tiene algún tipo de acceso. */
    public function surveyAccess(): BelongsToMany
    {
        return $this->belongsToMany(Survey::class, 'survey_encuestador_access')
            ->withPivot(['can_respond', 'can_view_results'])
            ->withTimestamps();
    }

    /** Respuestas que este encuestador ha enviado. */
    public function responses(): HasMany
    {
        return $this->hasMany(Response::class, 'respondent_id');
    }
}
