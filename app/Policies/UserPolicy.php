<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isManager();
    }

    public function view(User $user, User $model): bool
    {
        return $user->company_id === $model->company_id && 
               ($user->isManager() || $user->id === $model->id);
    }

    public function create(User $user): bool
    {
        return $user->isManager();
    }

    public function update(User $user, User $model): bool
    {
        return $user->company_id === $model->company_id && 
               ($user->isManager() || ($user->id === $model->id && !$this->isChangingRole($user, $model)));
    }

    public function delete(User $user, User $model): bool
    {
        return $user->company_id === $model->company_id && 
               $user->isManager() && 
               $user->id !== $model->id;
    }

    private function isChangingRole(User $user, User $model): bool
    {
        return request()->has('role') && request('role') !== $model->role;
    }
}
