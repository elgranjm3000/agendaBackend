<?php
namespace App\Jobs;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Notification $notification)
    {
        //
    }

    public function handle(NotificationService $service): void
    {
        try {
            $result = match ($this->notification->channel) {
                'email' => $service->sendEmail($this->notification),
                'sms' => $service->sendSMS($this->notification),
                'whatsapp' => $service->sendWhatsApp($this->notification),
                default => false,
            };

            $this->notification->update([
                'status' => $result ? 'sent' : 'failed',
                'sent_at' => $result ? now() : null,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send notification', [
                'notification_id' => $this->notification->id,
                'channel' => $this->notification->channel,
                'error' => $e->getMessage(),
            ]);

            $this->notification->update(['status' => 'failed']);
        }
    }
}