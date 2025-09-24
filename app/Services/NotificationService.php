<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function sendEmail(Notification $notification): bool
    {
        try {
            $payload = $notification->payload;
            
            // Simple email sending logic - in production, use proper mail templates
            Mail::raw($this->buildEmailContent($payload), function ($message) use ($payload) {
                $message->to($payload['client_email'])
                        ->subject($this->getEmailSubject($payload['type']));
            });

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send email notification', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function sendSMS(Notification $notification): bool
    {
        // Mock SMS service - integrate with actual SMS provider
        try {
            $payload = $notification->payload;
            $message = $this->buildSMSContent($payload);

            // Example with a hypothetical SMS service
            $response = Http::post('https://api.smsservice.com/send', [
                'to' => $payload['client_phone'],
                'message' => $message,
                'api_key' => config('services.sms.api_key'),
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Failed to send SMS notification', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function sendWhatsApp(Notification $notification): bool
    {
        // Mock WhatsApp service - integrate with WhatsApp Business API
        try {
            $payload = $notification->payload;
            $message = $this->buildWhatsAppContent($payload);

            // Example with WhatsApp Business API
            $response = Http::post('https://api.whatsapp.com/send', [
                'phone' => $payload['client_phone'],
                'message' => $message,
                'token' => config('services.whatsapp.token'),
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp notification', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    private function buildEmailContent(array $payload): string
    {
        return match ($payload['type']) {
            'appointment_created' => "Hello {$payload['client_name']},\n\nYour appointment has been scheduled:\n\nService: {$payload['service_name']}\nStaff: {$payload['staff_name']}\nDate & Time: {$payload['start_time']}\n\nThank you,\n{$payload['company_name']}",
            'appointment_updated' => "Hello {$payload['client_name']},\n\nYour appointment has been updated:\n\nService: {$payload['service_name']}\nStaff: {$payload['staff_name']}\nNew Date & Time: {$payload['start_time']}\n\nThank you,\n{$payload['company_name']}",
            'appointment_cancelled' => "Hello {$payload['client_name']},\n\nYour appointment has been cancelled:\n\nService: {$payload['service_name']}\nOriginal Date & Time: {$payload['start_time']}\n\nWe apologize for any inconvenience.\n\nThank you,\n{$payload['company_name']}",
            'appointment_reminder' => "Hello {$payload['client_name']},\n\nThis is a reminder for your upcoming appointment:\n\nService: {$payload['service_name']}\nStaff: {$payload['staff_name']}\nDate & Time: {$payload['start_time']}\n\nSee you soon!\n{$payload['company_name']}",
            'default' => "Hello {$payload['client_name']},\n\nYou have a notification from {$payload['company_name']}.",
        };
    }

    private function buildSMSContent(array $payload): string
    {
        return match ($payload['type']) {
            'appointment_created' => "Appointment confirmed: {$payload['service_name']} with {$payload['staff_name']} on {$payload['start_time']}. - {$payload['company_name']}",
            'appointment_updated' => "Appointment updated: {$payload['service_name']} with {$payload['staff_name']} on {$payload['start_time']}. - {$payload['company_name']}",
            'appointment_cancelled' => "Appointment cancelled: {$payload['service_name']} on {$payload['start_time']}. - {$payload['company_name']}",
            'appointment_reminder' => "Reminder: {$payload['service_name']} with {$payload['staff_name']} on {$payload['start_time']}. - {$payload['company_name']}",
            default => "Notification from {$payload['company_name']}",
        };
    }

    private function buildWhatsAppContent(array $payload): string
    {
        return $this->buildSMSContent($payload); // Same as SMS for simplicity
    }

    private function getEmailSubject(string $type): string
    {
        return match ($type) {
            'appointment_created' => 'Appointment Confirmation',
            'appointment_updated' => 'Appointment Updated',
            'appointment_cancelled' => 'Appointment Cancelled',
            'appointment_reminder' => 'Appointment Reminder',
            default => 'Notification',
        };
    }
}