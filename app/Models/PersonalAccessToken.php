<?php

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    protected $connection = 'mysql'; // O 'mysql' según tu BD principal

    // ⭐ Importante: Permitir asignación masiva
    protected $fillable = [
        'tokenable_type',
        'tokenable_id',
        'name',
        'token',
        'abilities',
        'expires_at',
        'last_used_at',
    ];

    public function tokenable()
    {
        $instance = $this->morphTo('tokenable');
        
        if ($this->tokenable_type === 'App\\Models\\User') {
            $instance->setConnection('secondary');
        }
        
        return $instance;
    }
}