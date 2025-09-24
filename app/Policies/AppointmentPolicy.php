<?php
namespace App\Policies;

use App\Models\User;
use App\Models\Appointment;

class AppointmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, Appointment $appointment): bool
    {
        return $user->company_id === $appointment->company_id && 
               ($user->isManager() || $user->id === $appointment->user_id);
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, Appointment $appointment): bool
    {
        return $user->company_id === $appointment->company_id && 
               ($user->isManager() || $user->id === $appointment->user_id);
    }

    public function delete(User $user, Appointment $appointment): bool
    {
        return $user->company_id === $appointment->company_id && 
               ($user->isManager() || $user->id === $appointment->user_id);
    }

    public function reschedule(User $user, Appointment $appointment): bool
    {
        return $this->update($user, $appointment) && $appointment->status === 'scheduled';
    }

    public function cancel(User $user, Appointment $appointment): bool
    {
        return $this->update($user, $appointment) && 
               in_array($appointment->status, ['scheduled']);
    }
}
