<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Setting;
use App\Models\Category;
use App\Models\Food;
use App\Models\Table;
use App\Services\TelegramBotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TelegramBotTest extends TestCase
{
    use RefreshDatabase;

    protected TelegramBotService $telegramBotService;
    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::create([
            'name' => 'Admin User',
            'login' => 'admin_test',
            'password' => bcrypt('password123'),
            'phone' => '+998901111129',
            'status' => 'active',
        ]);

        Setting::create(['key' => 'telegram_bot_token', 'value' => 'mock_token', 'type' => 'string']);
        Setting::create(['key' => 'service_charge_rate', 'value' => '10', 'type' => 'number']);

        $this->telegramBotService = app(TelegramBotService::class);
    }

    public function test_telegram_start_sends_welcome_message(): void
    {
        Http::fake([
            'https://api.telegram.org/botmock_token/sendMessage' => Http::response(['ok' => true], 200),
        ]);

        $update = [
            'update_id' => 1234,
            'message' => [
                'chat' => ['id' => 9999],
                'text' => '/start',
                'from' => ['id' => 8888]
            ]
        ];

        $this->telegramBotService->handleUpdate($update);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.telegram.org/botmock_token/sendMessage' &&
                $request['chat_id'] === 9999 &&
                str_contains($request['text'], 'xush kelibsiz');
        });
    }

    public function test_telegram_add_to_cart_persists_in_cache(): void
    {
        $category = Category::create(['name' => 'Desserts']);
        $food = Food::create([
            'name' => 'Ice Cream',
            'category_id' => $category->id,
            'price' => 12000.00,
            'status' => 'available',
            'sizes' => []
        ]);

        Http::fake([
            'https://api.telegram.org/botmock_token/answerCallbackQuery' => Http::response(['ok' => true], 200),
            'https://api.telegram.org/botmock_token/sendMessage' => Http::response(['ok' => true], 200),
        ]);

        $update = [
            'update_id' => 1235,
            'callback_query' => [
                'id' => 'cb_123',
                'from' => ['id' => 8888],
                'message' => [
                    'chat' => ['id' => 9999],
                    'message_id' => 777
                ],
                'data' => "add_{$food->id}"
            ]
        ];

        $this->telegramBotService->handleUpdate($update);

        // Verify cart cache
        $cart = Cache::get('tg_cart_8888');
        $this->assertNotEmpty($cart);
        $this->assertEquals('Ice Cream', $cart[$food->id]['name']);
        $this->assertEquals(1, $cart[$food->id]['quantity']);
    }

    public function test_telegram_checkout_places_order_successfully(): void
    {
        $category = Category::create(['name' => 'Main']);
        $food = Food::create([
            'name' => 'Somsa',
            'category_id' => $category->id,
            'price' => 10000.00,
            'status' => 'available',
            'sizes' => []
        ]);

        $table = Table::create(['table_number' => '5', 'capacity' => 4, 'status' => 'empty']);

        // Mock cart in cache
        Cache::put('tg_cart_8888', [
            $food->id => [
                'name' => 'Somsa',
                'price' => 10000.00,
                'quantity' => 3 // 30 000
            ]
        ], 3600);

        Http::fake([
            'https://api.telegram.org/botmock_token/answerCallbackQuery' => Http::response(['ok' => true], 200),
            'https://api.telegram.org/botmock_token/sendMessage' => Http::response(['ok' => true], 200),
        ]);

        $update = [
            'update_id' => 1236,
            'callback_query' => [
                'id' => 'cb_124',
                'from' => ['id' => 8888],
                'message' => [
                    'chat' => ['id' => 9999],
                    'message_id' => 778
                ],
                'data' => "table_{$table->id}"
            ]
        ];

        $this->telegramBotService->handleUpdate($update);

        // Cart should be cleared
        $this->assertEmpty(Cache::get('tg_cart_8888'));

        // Order should be in DB: 30 000 + 10% service = 33 000 UZS
        $this->assertDatabaseHas('orders', [
            'table_id' => $table->id,
            'total_amount' => 33000.00
        ]);
    }
}
