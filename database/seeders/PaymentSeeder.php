<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Appointment;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        // Create payments for completed appointments
        $completedAppointments = Appointment::where('status', 'completed')->get();

        foreach ($completedAppointments as $appointment) {
            // 80% chance of having a payment
            if (rand(1, 10) <= 8) {
                Payment::factory()->create([
                    'company_id' => $appointment->company_id,
                    'appointment_id' => $appointment->id,
                    'amount' => $appointment->service->price,
                    'status' => 'paid',
                    'method' => ['cash', 'card', 'online'][rand(0, 2)],
                ]);
            }
        }

        // Create some standalone payments (not linked to appointments)
        $companies = \App\Models\Company::all();
        foreach ($companies as $company) {
            Payment::factory(5)->create([
                'company_id' => $company->id,
                'appointment_id' => null,
                'status' => 'paid',
            ]);
        }
    }
}