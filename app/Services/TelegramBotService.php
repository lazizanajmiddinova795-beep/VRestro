<?php

namespace App\Services;

use App\Repositories\Contracts\SettingRepositoryInterface;
use App\Models\Category;
use App\Models\Food;
use App\Models\Table;
use App\Services\OrderService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TelegramBotService
{
    protected SettingRepositoryInterface $settingRepository;
    protected OrderService $orderService;

    public function __construct(SettingRepositoryInterface $settingRepository, OrderService $orderService)
    {
        $this->settingRepository = $settingRepository;
        $this->orderService = $orderService;
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
        } elseif (isset($update['callback_query'])) {
            $this->handleCallbackQuery($update['callback_query'], $token);
        }
    }

    /**
     * Handle simple text messages and commands.
     */
    protected function handleMessage(array $message, string $token): void
    {
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';
        $userId = $message['from']['id'];

        if (str_starts_with($text, '/start')) {
            $welcomeText = "👋 <b>Assalomu alaykum! VRestro botiga xush kelibsiz!</b>\n\n";
            $welcomeText .= "Bu yerda siz restoranimiz menyusini ko'rishingiz, taom tanlashingiz va to'g'ridan-to'g'ri buyurtma berishingiz mumkin.\n\n";
            $welcomeText .= "Tugmalardan birini tanlang 👇";

            $keyboard = [
                'inline_keyboard' => [
                    [
                        ['text' => '🍽️ Menyuni ko\'rish', 'callback_data' => 'show_categories'],
                        ['text' => '🛒 Savatcha', 'callback_data' => 'view_cart']
                    ],
                    [
                        ['text' => 'ℹ️ Restoran haqida', 'callback_data' => 'about_restaurant']
                    ]
                ]
            ];

            $this->sendTelegramRequest('sendMessage', $token, [
                'chat_id' => $chatId,
                'text' => $welcomeText,
                'parse_mode' => 'HTML',
                'reply_markup' => $keyboard
            ]);
        } else {
            // General text input
            $this->sendTelegramRequest('sendMessage', $token, [
                'chat_id' => $chatId,
                'text' => "Noma'lum buyruq. Menyuni ko'rish uchun /start buyrug'ini yuboring.",
            ]);
        }
    }

    /**
     * Handle inline button clicks (Callback queries).
     */
    protected function handleCallbackQuery(array $callbackQuery, string $token): void
    {
        $chatId = $callbackQuery['message']['chat']['id'];
        $messageId = $callbackQuery['message']['message_id'];
        $data = $callbackQuery['data'];
        $userId = $callbackQuery['from']['id'];

        // Answer callback query so it doesn't show loading on Telegram client
        $this->sendTelegramRequest('answerCallbackQuery', $token, [
            'callback_query_id' => $callbackQuery['id']
        ]);

        if ($data === 'show_categories') {
            $categories = Category::all();
            if ($categories->isEmpty()) {
                $this->sendTelegramRequest('sendMessage', $token, [
                    'chat_id' => $chatId,
                    'text' => "Hozirda menyuda kategoriyalar mavjud emas."
                ]);
                return;
            }

            $keyboard = [];
            foreach ($categories as $cat) {
                $keyboard[] = [['text' => $cat->name, 'callback_data' => "cat_{$cat->id}"]];
            }
            $keyboard[] = [['text' => '⬅️ Orqaga', 'callback_data' => 'back_to_start']];

            $this->sendTelegramRequest('editMessageText', $token, [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => "📚 <b>Kategoriyalarni tanlang:</b>",
                'parse_mode' => 'HTML',
                'reply_markup' => ['inline_keyboard' => $keyboard]
            ]);
        } elseif (str_starts_with($data, 'cat_')) {
            $catId = (int) str_replace('cat_', '', $data);
            $category = Category::find($catId);
            if (!$category) return;

            $foods = Food::where('category_id', $catId)->where('status', 'available')->get();

            $keyboard = [];
            $text = "🍽️ <b>{$category->name}</b> kategoriyasidagi taomlar:\n\n";

            if ($foods->isEmpty()) {
                $text .= "Ushbu kategoriyada taomlar yo'q.";
            } else {
                foreach ($foods as $food) {
                    $priceStr = number_format($food->price, 0, '.', ' ') . " UZS";
                    $text .= "🔸 <b>{$food->name}</b> - {$priceStr}\n";
                    $keyboard[] = [
                        ['text' => "➕ {$food->name}", 'callback_data' => "add_{$food->id}"]
                    ];
                }
            }

            $keyboard[] = [
                ['text' => '📁 Kategoriyalar', 'callback_data' => 'show_categories'],
                ['text' => '🛒 Savatcha', 'callback_data' => 'view_cart']
            ];

            $this->sendTelegramRequest('editMessageText', $token, [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $text,
                'parse_mode' => 'HTML',
                'reply_markup' => ['inline_keyboard' => $keyboard]
            ]);
        } elseif (str_starts_with($data, 'add_')) {
            $foodId = (int) str_replace('add_', '', $data);
            $food = Food::find($foodId);
            if (!$food) return;

            // Get user cart from Cache
            $cartKey = "tg_cart_{$userId}";
            $cart = Cache::get($cartKey, []);

            if (isset($cart[$foodId])) {
                $cart[$foodId]['quantity']++;
            } else {
                $cart[$foodId] = [
                    'name' => $food->name,
                    'price' => (float) $food->price,
                    'quantity' => 1
                ];
            }

            Cache::put($cartKey, $cart, 3600); // Save for 1 hour

            // Send notification about adding to cart
            $this->sendTelegramRequest('sendMessage', $token, [
                'chat_id' => $chatId,
                'text' => "✅ <b>{$food->name}</b> savatchaga qo'shildi!" . (isset($cart[$foodId]) ? " (Jami: " . $cart[$foodId]['quantity'] . " ta)" : ""),
                'parse_mode' => 'HTML'
            ]);
        } elseif ($data === 'view_cart') {
            $cartKey = "tg_cart_{$userId}";
            $cart = Cache::get($cartKey, []);

            if (empty($cart)) {
                $keyboard = [
                    'inline_keyboard' => [
                        [['text' => '🍽️ Menyu', 'callback_data' => 'show_categories']]
                    ]
                ];
                $this->sendTelegramRequest('sendMessage', $token, [
                    'chat_id' => $chatId,
                    'text' => "Savatchangiz bo'sh. Taom tanlash uchun menyuga o'ting.",
                    'reply_markup' => $keyboard
                ]);
                return;
            }

            $text = "🛒 <b>Sizning savatchangiz:</b>\n\n";
            $total = 0;
            foreach ($cart as $id => $item) {
                $subtotal = $item['price'] * $item['quantity'];
                $total += $subtotal;
                $text .= "▪️ <b>{$item['name']}</b>\n      {$item['quantity']} x " . number_format($item['price'], 0, '.', ' ') . " = " . number_format($subtotal, 0, '.', ' ') . " UZS\n";
            }
            $text .= "\n<b>Jami: " . number_format($total, 0, '.', ' ') . " UZS</b>";

            $keyboard = [
                'inline_keyboard' => [
                    [
                        ['text' => '🗑️ Savatchani tozalash', 'callback_data' => 'clear_cart'],
                        ['text' => '🍽️ Menyu', 'callback_data' => 'show_categories']
                    ],
                    [
                        ['text' => '✅ Buyurtma berish', 'callback_data' => 'checkout']
                    ]
                ]
            ];

            $this->sendTelegramRequest('sendMessage', $token, [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
                'reply_markup' => $keyboard
            ]);
        } elseif ($data === 'clear_cart') {
            $cartKey = "tg_cart_{$userId}";
            Cache::forget($cartKey);

            $this->sendTelegramRequest('sendMessage', $token, [
                'chat_id' => $chatId,
                'text' => "Savatchangiz muvaffaqiyatli tozalandi.",
            ]);
        } elseif ($data === 'checkout') {
            $tables = Table::where('status', 'empty')->get();

            $keyboard = [];
            foreach ($tables as $table) {
                $keyboard[] = [['text' => "Stol #{$table->table_number}", 'callback_data' => "table_{$table->id}"]];
            }
            $keyboard[] = [['text' => "🛍️ Olib ketish (Takeaway)", 'callback_data' => "takeaway"]];
            $keyboard[] = [['text' => "⬅️ Orqaga", 'callback_data' => 'view_cart']];

            $this->sendTelegramRequest('sendMessage', $token, [
                'chat_id' => $chatId,
                'text' => "📍 <b>Qayerda o'tiribsiz? Iltimos stolni tanlang:</b>",
                'parse_mode' => 'HTML',
                'reply_markup' => ['inline_keyboard' => $keyboard]
            ]);
        } elseif (str_starts_with($data, 'table_') || $data === 'takeaway') {
            $tableId = null;
            if (str_starts_with($data, 'table_')) {
                $tableId = (int) str_replace('table_', '', $data);
            }

            $cartKey = "tg_cart_{$userId}";
            $cart = Cache::get($cartKey, []);

            if (empty($cart)) {
                $this->sendTelegramRequest('sendMessage', $token, [
                    'chat_id' => $chatId,
                    'text' => "Xatolik: Buyurtma berish uchun savatcha bo'sh bo'lmasligi kerak."
                ]);
                return;
            }

            // Create Order
            DB::transaction(function () use ($cart, $tableId, $chatId, $cartKey, $token) {
                $items = [];
                foreach ($cart as $foodId => $item) {
                    $items[] = [
                        'food_id' => $foodId,
                        'quantity' => $item['quantity'],
                        'size_name' => null
                    ];
                }

                // Retrieve dummy admin or first user for creator ID
                $adminUser = \App\Models\User::first();

                $order = $this->orderService->createOrder([
                    'table_id' => $tableId,
                    'items' => $items
                ], $adminUser);

                // Clear cart
                Cache::forget($cartKey);

                $text = "🎉 <b>Buyurtmangiz muvaffaqiyatli qabul qilindi!</b>\n\n";
                $text .= "Buyurtma raqami: <b>#{$order->order_number}</b>\n";
                $text .= "Umumiy summa: <b>" . number_format($order->total_amount, 0, '.', ' ') . " UZS</b>\n";
                $text .= "Tez orada taomlaringiz tayyor bo'ladi. Rahmat!";

                $this->sendTelegramRequest('sendMessage', $token, [
                    'chat_id' => $chatId,
                    'text' => $text,
                    'parse_mode' => 'HTML'
                ]);
            });
        } elseif ($data === 'back_to_start') {
            $welcomeText = "👋 <b>VRestro botiga xush kelibsiz!</b>\nTugmalardan birini tanlang:";
            $keyboard = [
                'inline_keyboard' => [
                    [
                        ['text' => '🍽️ Menyuni ko\'rish', 'callback_data' => 'show_categories'],
                        ['text' => '🛒 Savatcha', 'callback_data' => 'view_cart']
                    ]
                ]
            ];
            $this->sendTelegramRequest('editMessageText', $token, [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $welcomeText,
                'parse_mode' => 'HTML',
                'reply_markup' => $keyboard
            ]);
        } elseif ($data === 'about_restaurant') {
            $aboutText = "🏢 <b>VRestro ERP Restaurant</b>\n\n";
            $aboutText .= "📍 Manzil: " . $this->settingRepository->getByKey('restaurant_address') . "\n";
            $aboutText .= "📞 Telefon: " . $this->settingRepository->getByKey('restaurant_phone') . "\n";
            $aboutText .= "⏰ Ish vaqti: " . $this->settingRepository->getByKey('restaurant_hours') . "\n";

            $keyboard = [
                'inline_keyboard' => [
                    [['text' => '⬅️ Orqaga', 'callback_data' => 'back_to_start']]
                ]
            ];

            $this->sendTelegramRequest('editMessageText', $token, [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $aboutText,
                'parse_mode' => 'HTML',
                'reply_markup' => $keyboard
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
