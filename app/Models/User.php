<?php

namespace App\Models;

use App\Models\Local;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'id_local',
        'name',
        'email',
        'role',
        'password',
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
        ];
    }

    public function local(): BelongsTo
    {
        return $this->belongsTo(Local::class, 'id_local');
    }

    // NUEVO (recomendado)
    public function mesasAtendidas(): HasMany
    {
        return $this->hasMany(Mesa::class, 'atendida_por');
    }

    public function comandasComoMozo(): HasMany
    {
        return $this->hasMany(Comanda::class, 'id_mozo');
    }

    public function comandasCuentaSolicitada(): HasMany
    {
        return $this->hasMany(Comanda::class, 'cuenta_solicitada_por');
    }
}
