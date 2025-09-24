<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Company;

class CompanyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isOwner();
    }

    public function view(User $user, Company $company): bool
    {
        return $user->company_id === $company->id && $user->isStaff();
    }

    public function create(User $user): bool
    {
        return $user->isOwner();
    }

    public function update(User $user, Company $company): bool
    {
        return $user->company_id === $company->id && $user->isManager();
    }

    public function delete(User $user, Company $company): bool
    {
        return $user->company_id === $company->id && $user->isOwner();
    }
}