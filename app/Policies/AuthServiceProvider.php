<?php
namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Company;
use App\Models\Client;
use App\Models\Service;
use App\Models\Appointment;
use App\Models\Payment;
use App\Policies\UserPolicy;
use App\Policies\CompanyPolicy;
use App\Policies\ClientPolicy;
use App\Policies\ServicePolicy;
use App\Policies\AppointmentPolicy;
use App\Policies\PaymentPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => UserPolicy::class,
        Company::class => CompanyPolicy::class,
        Client::class => ClientPolicy::class,
        Service::class => ServicePolicy::class,
        Appointment::class => AppointmentPolicy::class,
        Payment::class => PaymentPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Additional gates for specific actions
        Gate::define('view-reports', function (User $user) {
            return $user->isManager();
        });

        Gate::define('export-data', function (User $user) {
            return $user->isManager();
        });

        Gate::define('manage-notifications', function (User $user) {
            return $user->isManager();
        });

        Gate::define('view-audit-logs', function (User $user) {
            return $user->isOwner();
        });

        Gate::define('manage-company-settings', function (User $user) {
            return $user->isOwner();
        });
    }
}