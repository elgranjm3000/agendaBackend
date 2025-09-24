<?php

namespace App\Listeners;

use App\Events\AppointmentCreated;
use App\Events\AppointmentUpdated;
use App\Events\AppointmentCancelled;
use App\Jobs\SendNotificationJob;
use App\Models\Notification;

class SendAppointmentNotifications
{
    public function handleCreated(AppointmentCreated $event): void
    {
        $this->createNotifications($event->appointment, 'appointment_created');
    }

    public function handleUpdated(AppointmentUpdated $event): void
    {
        $this->createNotifications($event->appointment, 'appointment_updated');
    }

    public function handleCancelled(AppointmentCancelled $event): void
    {
        $this->createNotifications($event->appointment, 'appointment_cancelled');
    }

    private function createNotifications($appointment, $type): void
    {
        $channels = ['email'];
        
        if ($appointment->client->phone) {
            $channels[] = 'sms';
        }

        foreach ($channels as $channel) {
            $notification = Notification::create([
                'company_id' => $appointment->company_id,
                'appointment_id' => $appointment->id,
                'channel' => $channel,
                'status' => 'pending',
                'payload' => $this->buildPayload($appointment, $type),
            ]);

            SendNotificationJob::dispatch($notification);
        }
    }

    private function buildPayload($appointment, $type): array
    {
        return [
            'type' => $type,
            'client_name' => $appointment->client->name,
            'client_email' => $appointment->client->email,
            'client_phone' => $appointment->client->phone,
            'service_name' => $appointment->service->name,
            'staff_name' => $appointment->user->name,
            'start_time' => $appointment->start_time->format('Y-m-d H:i:s'),
            'end_time' => $appointment->end_time->format('Y-m-d H:i:s'),
            'company_name' => $appointment->company->name,
        ];
    }
}
