<?php

namespace App\Jobs;

use App\Models\SystemNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $data;

    /**
     * Create a new job instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Persist the notification to PostgreSQL
        $notification = SystemNotification::create([
            'type' => $this->data['type'],
            'title' => $this->data['title'],
            'message' => $this->data['message'],
            'meta_data' => $this->data['meta_data'] ?? null,
            'is_read' => false
        ]);

        // Simulating WebSocket/Pusher broadcast for frontend live updates
        Log::info("NotificationBroadcast: New real-time alert created [{$notification->type}] - {$notification->title}");

        // Forward to Telegram bot chat
        try {
            $telegramService = app(\App\Services\TelegramService::class);
            $emoji = '🔔';
            if ($notification->type === 'low_stock') $emoji = '⚠️';
            if ($notification->type === 'order_cancelled') $emoji = '🛑';
            
            $text = "{$emoji} <b>" . strtoupper($notification->type) . "</b>\n";
            $text .= "<b>{$notification->title}</b>\n";
            $text .= "{$notification->message}";

            $telegramService->sendMessage($text);
        } catch (\Exception $e) {
            Log::error("TelegramJobError: " . $e->getMessage());
        }
    }
}
