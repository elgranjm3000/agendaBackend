<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Payment;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, Payment $payment): bool
    {
        return $user->company_id === $payment->company_id && $user->isStaff();
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, Payment $payment): bool
    {
        return $user->company_id === $payment->company_id && $user->isManager();
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $user->company_id === $payment->company_id && $user->isManager();
    }

    public function refund(User $user, Payment $payment): bool
    {
        return $user->company_id === $payment->company_id && 
               $user->isManager() && 
               $payment->status === 'paid';
    }
}
