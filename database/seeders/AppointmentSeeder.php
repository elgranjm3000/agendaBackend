<?php
namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Company;
use App\Models\Client;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $companies = Company::all();

        foreach ($companies as $company) {
            $clients = Client::where('company_id', $company->id)->get();
            $services = Service::where('company_id', $company->id)->get();
            $users = User::where('company_id', $company->id)
                         ->whereIn('role', ['owner', 'manager', 'staff'])
                         ->get();

            // Create appointments for the last 3 months
            for ($i = 0; $i < 50; $i++) {
                $client = $clients->random();
                $service = $services->random();
                $user = $users->random();
                
                // Random date in the last 3 months or future
                $startTime = Carbon::now()
                    ->subMonths(3)
                    ->addDays(rand(0, 180))
                    ->setHour(rand(9, 17))
                    ->setMinute([0, 15, 30, 45][rand(0, 3)])
                    ->setSecond(0);
                
                $endTime = $startTime->copy()->addMinutes($service->duration_minutes);

                // Determine status based on date
                $status = 'scheduled';
                if ($startTime->isPast()) {
                    $status = ['completed', 'cancelled', 'no_show'][rand(0, 2)];
                    // Higher chance of completed for past appointments
                    if (rand(1, 10) <= 7) {
                        $status = 'completed';
                    }
                }

                Appointment::factory()->create([
                    'company_id' => $company->id,
                    'client_id' => $client->id,
                    'service_id' => $service->id,
                    'user_id' => $user->id,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'status' => $status,
                ]);
            }
        }
    }
}
