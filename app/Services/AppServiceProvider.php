<?php

use Laravel\Sanctum\Sanctum;
use App\Models\PersonalAccessToken;

public function boot(): void
{
    Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
}