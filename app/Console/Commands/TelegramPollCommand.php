<?php

namespace App\Console\Commands;

use App\Repositories\Contracts\SettingRepositoryInterface;
use App\Services\TelegramBotService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramPollCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:poll';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Poll Telegram API for incoming bot updates';

    protected SettingRepositoryInterface $settingRepository;
    protected TelegramBotService $telegramBotService;

    public function __construct(SettingRepositoryInterface $settingRepository, TelegramBotService $telegramBotService)
    {
        parent::__construct();
        $this->settingRepository = $settingRepository;
        $this->telegramBotService = $telegramBotService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting Telegram Bot Polling loop...");

        $offset = 0;

        while (true) {
            $token = $this->settingRepository->getByKey('telegram_bot_token');
            if (!$token) {
                $this->warn("Telegram Bot Token is not set in global settings. Waiting 5s...");
                sleep(5);
                continue;
            }

            try {
                $url = "https://api.telegram.org/bot{$token}/getUpdates";
                $response = Http::timeout(10)->post($url, [
                    'offset' => $offset,
                    'timeout' => 5
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if ($data['ok'] && !empty($data['result'])) {
                        foreach ($data['result'] as $update) {
                            $this->info("Processing Update ID: " . $update['update_id']);
                            $this->telegramBotService->handleUpdate($update);
                            $offset = $update['update_id'] + 1;
                        }
                    }
                } else {
                    $this->error("Failed to connect to Telegram: " . $response->body());
                    sleep(2);
                }
            } catch (\Exception $e) {
                $this->error("Polling connection failed: " . $e->getMessage());
                sleep(2);
            }

            // Sleep slightly to prevent CPU hogging
            usleep(200000); // 200ms
        }
    }
}
