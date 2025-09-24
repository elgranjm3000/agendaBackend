<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Service;

class ServicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, Service $service): bool
    {
        return $user->company_id === $service->company_id && $user->isStaff();
    }

    public function create(User $user): bool
    {
        return $user->isManager();
    }

    public function update(User $user, Service $service): bool
    {
        return $user->company_id === $service->company_id && $user->isManager();
    }

    public function delete(User $user, Service $service): bool
    {
        return $user->company_id === $service->company_id && $user->isManager();
    }
}