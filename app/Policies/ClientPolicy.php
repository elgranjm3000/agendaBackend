<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Client;

class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, Client $client): bool
    {
        return $user->company_id === $client->company_id && $user->isStaff();
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, Client $client): bool
    {
        return $user->company_id === $client->company_id && $user->isStaff();
    }

    public function delete(User $user, Client $client): bool
    {
        return $user->company_id === $client->company_id && $user->isManager();
    }
}
