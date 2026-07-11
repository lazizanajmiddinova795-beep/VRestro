<?php

namespace App\Services;

use App\Repositories\Contracts\SettingRepositoryInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected SettingRepositoryInterface $settingRepository;

    public function __construct(SettingRepositoryInterface $settingRepository)
    {
        $this->settingRepository = $settingRepository;
    }

    /**
     * Send notification to Telegram bot chat.
     */
    public function sendMessage(string $text): void
    {
        $enabled = $this->settingRepository->getByKey('telegram_notifications_enabled');
        $token = $this->settingRepository->getByKey('telegram_bot_token');
        $chatId = $this->settingRepository->getByKey('telegram_chat_id');

        if (!$enabled || !$token || !$chatId) {
            return;
        }

        try {
            $url = "https://api.telegram.org/bot{$token}/sendMessage";
            $response = Http::timeout(5)->post($url, [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
            ]);

            if ($response->failed()) {
                Log::error("TelegramAPIError: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("TelegramConnectionError: " . $e->getMessage());
        }
    }
}
