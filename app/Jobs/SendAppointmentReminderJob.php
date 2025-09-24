<?php
namespace App\Jobs;

use App\Models\Appointment;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendAppointmentReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Appointment $appointment)
    {
        //
    }

    public function handle(): void
    {
        // Only send reminders for scheduled appointments
        if ($this->appointment->status !== 'scheduled') {
            return;
        }

        $channels = ['email'];
        if ($this->appointment->client->phone) {
            $channels[] = 'sms';
        }

        foreach ($channels as $channel) {
            $notification = Notification::create([
                'company_id' => $this->appointment->company_id,
                'appointment_id' => $this->appointment->id,
                'channel' => $channel,
                'status' => 'pending',
                'payload' => [
                    'type' => 'appointment_reminder',
                    'client_name' => $this->appointment->client->name,
                    'client_email' => $this->appointment->client->email,
                    'client_phone' => $this->appointment->client->phone,
                    'service_name' => $this->appointment->service->name,
                    'staff_name' => $this->appointment->user->name,
                    'start_time' => $this->appointment->start_time->format('Y-m-d H:i:s'),
                    'company_name' => $this->appointment->company->name,
                ],
            ]);

            SendNotificationJob::dispatch($notification);
        }
    }
}