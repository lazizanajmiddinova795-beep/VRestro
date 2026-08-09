<?php

namespace App\Services;

use App\Repositories\Contracts\SettingRepositoryInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramBotService
{
    protected SettingRepositoryInterface $settingRepository;

    public function __construct(SettingRepositoryInterface $settingRepository)
    {
        $this->settingRepository = $settingRepository;
    }

    /**
     * Handle incoming Telegram Update payload.
     */
    public function handleUpdate(array $update): void
    {
        $token = $this->settingRepository->getByKey('telegram_bot_token');
        if (!$token) return;

        if (isset($update['message'])) {
            $this->handleMessage($update['message'], $token);
        }
    }

    /**
     * Handle simple text messages and commands.
     */
    protected function handleMessage(array $message, string $token): void
    {
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';

        if (str_starts_with($text, '/start')) {
            $welcomeText = "👋 <b>Assalomu alaykum!</b>\n\n";
            $welcomeText .= "Ushbu bot FoodFlow tizimi xavfsizligi va adminlarga 2-bosqichli tasdiqlash (2FA) parollarini yetkazish uchun xizmat qiladi.\n\n";
            $welcomeText .= "Sizning Chat ID raqamingiz: <code>{$chatId}</code>\n\n";
            $welcomeText .= "Tizim ma'muri ushbu ID ni sozlamalarga kiritishi mumkin.";

            $this->sendTelegramRequest('sendMessage', $token, [
                'chat_id' => $chatId,
                'text' => $welcomeText,
                'parse_mode' => 'HTML',
            ]);
        } else {
            // General text input
            $this->sendTelegramRequest('sendMessage', $token, [
                'chat_id' => $chatId,
                'text' => "Noma'lum buyruq. Ushbu bot faqat tizim xavfsizligi uchun ishlaydi.",
            ]);
        }
    }

    /**
     * Send HTTP API request to Telegram bot.
     */
    protected function sendTelegramRequest(string $method, string $token, array $params = []): void
    {
        try {
            $url = "https://api.telegram.org/bot{$token}/{$method}";
            $response = Http::post($url, $params);
            if ($response->failed()) {
                Log::error("TelegramBotRequestFailed: {$method} - " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("TelegramBotConnectionError: {$method} - " . $e->getMessage());
        }
    }
}
