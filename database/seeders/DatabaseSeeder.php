<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Roles
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $chefRole = Role::firstOrCreate(['name' => 'Chef']);
        $waiterRole = Role::firstOrCreate(['name' => 'Waiter']);
        $cashierRole = Role::firstOrCreate(['name' => 'Cashier']);

        // Create Permissions
        $managePayments = Permission::firstOrCreate(['name' => 'manage payments']);
        $manageDiscounts = Permission::firstOrCreate(['name' => 'manage discounts']);
        $viewReports = Permission::firstOrCreate(['name' => 'view reports']);
        $manageSettings = Permission::firstOrCreate(['name' => 'manage settings']);
        $viewCashierDashboard = Permission::firstOrCreate(['name' => 'view cashier dashboard']);
        $viewKitchenPanel = Permission::firstOrCreate(['name' => 'view kitchen panel']);
        $viewWaiterPanel = Permission::firstOrCreate(['name' => 'view waiter panel']);
        
        $adminRole->givePermissionTo($managePayments);
        $adminRole->givePermissionTo($manageDiscounts);
        $adminRole->givePermissionTo($viewReports);
        $adminRole->givePermissionTo($manageSettings);
        $adminRole->givePermissionTo($viewCashierDashboard);
        $adminRole->givePermissionTo($viewKitchenPanel);
        $adminRole->givePermissionTo($viewWaiterPanel);
        
        $chefRole->givePermissionTo($viewKitchenPanel);

        $waiterRole->givePermissionTo($viewWaiterPanel);

        $cashierRole->givePermissionTo($managePayments);
        $cashierRole->givePermissionTo($manageDiscounts);
        $cashierRole->givePermissionTo($viewReports);
        $cashierRole->givePermissionTo($viewCashierDashboard);

        // Create or update Admin User
        $adminUser = User::firstOrCreate(
            ['login' => 'admin'],
            [
                'name' => 'Tizim Administratori',
                'password' => Hash::make('admin123'),
                'face_registered' => true,
                'phone' => '+998901234567',
                'shift_hours' => '09:00 - 18:00',
                'status' => 'active',
            ]
        );
        $adminUser->assignRole($adminRole);

        // Create or update Chef User
        $chefUser = User::firstOrCreate(
            ['login' => 'chef'],
            [
                'name' => 'Asilbek Povar',
                'password' => Hash::make('chef123'),
                'face_registered' => true,
                'phone' => '+998901234568',
                'shift_hours' => '08:00 - 20:00',
                'status' => 'active',
            ]
        );
        $chefUser->assignRole($chefRole);

        // Create or update Waiter User
        $waiterUser = User::firstOrCreate(
            ['login' => 'waiter'],
            [
                'name' => 'Davron Ofitsiant',
                'password' => Hash::make('waiter123'),
                'face_registered' => true,
                'phone' => '+998901234569',
                'shift_hours' => '10:00 - 22:00',
                'status' => 'active',
            ]
        );
        $waiterUser->assignRole($waiterRole);

        // Create or update Cashier User
        $cashierUser = User::firstOrCreate(
            ['login' => 'cashier'],
            [
                'name' => 'Lobar Kassir',
                'password' => Hash::make('cashier123'),
                'face_registered' => true,
                'phone' => '+998901234570',
                'shift_hours' => '09:00 - 21:00',
                'status' => 'active',
            ]
        );
        $cashierUser->assignRole($cashierRole);

        // Create Tables
        $tableModels = [];
        
        // Seed 10 Regular Tables
        for ($t = 1; $t <= 10; $t++) {
            $tableModels[] = \App\Models\Table::create([
                'table_number' => 'Stol ' . $t,
                'capacity' => 4,
                'status' => 'empty',
                'qr_code_token' => 'qr_table_' . $t . '_' . bin2hex(random_bytes(4)),
            ]);
        }

        // Seed 3 VIP Tables
        for ($v = 1; $v <= 3; $v++) {
            $tableModels[] = \App\Models\Table::create([
                'table_number' => 'VIP ' . $v,
                'capacity' => 8,
                'status' => 'empty',
                'qr_code_token' => 'qr_vip_' . $v . '_' . bin2hex(random_bytes(4)),
            ]);
        }

        // Seed 20+ Customers
        $customerNames = [
            'Jasur Aliyev', 'Zilola Karimova', 'Sardor Mansurov', 'Nigora Abdullayeva', 'Diyorbek Toshtemirov',
            'Malika Rasulova', 'Bekzod Shukurov', 'Shaxzoda Rustamova', 'Farrux Umarov', 'Guli Ergasheva',
            'Bobur Xasanov', 'Madina Solihova', 'Sherzod Orifov', 'Lola Mirzayeva', 'Javoxir Karimov',
            'Sevara Tursunova', 'Otabek Raxmonov', 'Ruxshona Olimova', 'Sirojiddin Alimov', 'Asal Sodiqova',
            'Davron Nabiyev', 'Nozima Ismoilova'
        ];

        foreach ($customerNames as $idx => $name) {
            $visits = rand(2, 45);
            $spent = $visits * rand(35000, 120000);
            $bonus = $spent * 0.03; // 3% cashback

            \App\Models\Customer::create([
                'name' => $name,
                'phone' => '+9989090000' . str_pad($idx + 1, 2, '0', STR_PAD_LEFT),
                'bonus_balance' => round($bonus, -2), // Round to nearest 100 UZS
                'total_orders_count' => $visits,
                'total_spent_amount' => $spent
            ]);
        }

        // Create Food Categories
        $categories = [];
        $categoryNames = ['Taomlar', 'Ichimliklar', 'Salatlar', 'Shirinliklar'];
        foreach ($categoryNames as $name) {
            $categories[] = \App\Models\Category::create(['name' => $name]);
        }

        // Create Foods
        $foods = [
            [
                'name' => 'Palov (Osh)', 
                'price' => 45000, 
                'category_id' => $categories[0]->id,
                'sizes' => json_encode([
                    ['name' => 'Yarim porsiya (0.5)', 'price' => 25000],
                    ['name' => '1 porsiya (1.0)', 'price' => 45000],
                    ['name' => 'Katta porsiya (1.5)', 'price' => 65000]
                ])
            ],
            [
                'name' => 'Lag\'mon', 
                'price' => 35000, 
                'category_id' => $categories[0]->id,
                'sizes' => json_encode([
                    ['name' => 'Yarim porsiya (0.5)', 'price' => 20000],
                    ['name' => '1 porsiya (1.0)', 'price' => 35000],
                    ['name' => 'Katta porsiya (1.5)', 'price' => 50000]
                ])
            ],
            [
                'name' => 'Mol go\'shtidan Shashlik', 
                'price' => 25000, 
                'category_id' => $categories[0]->id,
                'sizes' => json_encode([
                    ['name' => '1.0 (Standart)', 'price' => 25000]
                ])
            ],
            [
                'name' => 'Sezar Salati', 
                'price' => 30000, 
                'category_id' => $categories[2]->id,
                'sizes' => json_encode([
                    ['name' => '1.0 (Standart)', 'price' => 30000]
                ])
            ],
            [
                'name' => 'Achchiq-chuchuk', 
                'price' => 15000, 
                'category_id' => $categories[2]->id,
                'sizes' => json_encode([
                    ['name' => 'Yarim porsiya (0.5)', 'price' => 8000],
                    ['name' => '1 porsiya (1.0)', 'price' => 15000]
                ])
            ],
            [
                'name' => 'Coca-Cola 1.5L', 
                'price' => 12000, 
                'category_id' => $categories[1]->id,
                'sizes' => json_encode([
                    ['name' => '0.5L', 'price' => 6000],
                    ['name' => '1.0L', 'price' => 10000],
                    ['name' => '1.5L', 'price' => 12000]
                ])
            ],
            [
                'name' => 'Limonli Ko\'k Choy', 
                'price' => 15000, 
                'category_id' => $categories[1]->id,
                'sizes' => json_encode([
                    ['name' => '1.0 (Standart)', 'price' => 15000]
                ])
            ],
            [
                'name' => 'Paxlava', 
                'price' => 25000, 
                'category_id' => $categories[3]->id,
                'sizes' => json_encode([
                    ['name' => 'Kichik (2 dona)', 'price' => 15000],
                    ['name' => 'O\'rtacha (4 dona)', 'price' => 25000],
                    ['name' => 'Katta (6 dona)', 'price' => 35000]
                ])
            ],
            [
                'name' => 'Muzqaymoq', 
                'price' => 18000, 
                'category_id' => $categories[3]->id,
                'sizes' => json_encode([
                    ['name' => '1.0 (Standart)', 'price' => 18000]
                ])
            ],
        ];
        $foodModels = [];
        foreach ($foods as $food) {
            $foodModels[] = \App\Models\Food::create($food);
        }

        // Seed Orders for the past 7 days (including today)
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            
            // Generate random number of orders per day (e.g. 6 to 15)
            $ordersCount = ($i === 0) ? 8 : rand(6, 15);
            
            for ($o = 0; $o < $ordersCount; $o++) {
                // Waiter for the order
                $waiter = $waiterUser;
                
                // Status distribution
                if ($i === 0) {
                    // Today: distribute statuses for demo: new, cooking, ready, delivered, cancelled
                    if ($o < 3) $status = 'delivered';
                    elseif ($o < 5) $status = 'cooking';
                    elseif ($o < 6) $status = 'new';
                    elseif ($o < 7) $status = 'ready';
                    else $status = 'cancelled';
                } else {
                    // Previous days: almost all delivered, few cancelled
                    $status = (rand(1, 10) > 1) ? 'delivered' : 'cancelled';
                }
                
                // Generate sequential order number: ORD-YYYYMMDD-SEQ
                $orderNumber = 'ORD-' . $date->format('Ymd') . '-' . str_pad($o + 1, 3, '0', STR_PAD_LEFT);
                
                $order = \App\Models\Order::create([
                    'order_number' => $orderNumber,
                    'table_id' => $tableModels[rand(0, 9)]->id,
                    'waiter_id' => $waiter->id,
                    'total_amount' => 0, // Will update below
                    'status' => $status,
                    'created_at' => $date->copy()->setTime(rand(9, 22), rand(0, 59)),
                    'updated_at' => $date,
                ]);
                
                // Add 1 to 4 random food items to order
                $itemsCount = rand(1, 4);
                $totalAmount = 0;
                
                $shuffledFoods = $foodModels;
                shuffle($shuffledFoods);
                
                for ($it = 0; $it < $itemsCount; $it++) {
                    $selectedFood = $shuffledFoods[$it];
                    $qty = rand(1, 3);
                    $price = $selectedFood->price;
                    
                    \App\Models\OrderItem::create([
                        'order_id' => $order->id,
                        'food_id' => $selectedFood->id,
                        'quantity' => $qty,
                        'price' => $price,
                        'notes' => (rand(1, 5) === 1) ? 'Piyozsiz bo\'lsin' : null,
                        'created_at' => $order->created_at,
                        'updated_at' => $order->updated_at,
                    ]);
                    
                    $totalAmount += $qty * $price;
                }
                
                // Update total amount
                $order->update(['total_amount' => $totalAmount]);

                if ($status === 'delivered') {
                    $pm = ['cash', 'card', 'qr'][rand(0, 2)];
                    \App\Models\Payment::create([
                        'order_id' => $order->id,
                        'customer_id' => rand(1, 10) > 4 ? rand(1, 20) : null, // randomly assign a customer
                        'total_amount' => $totalAmount,
                        'payment_method' => $pm,
                        'cash_amount' => ($pm === 'cash') ? $totalAmount : 0,
                        'card_amount' => ($pm === 'card') ? $totalAmount : 0,
                        'qr_amount' => ($pm === 'qr') ? $totalAmount : 0,
                        'bonus_used' => 0,
                        'status' => 'completed',
                        'created_at' => $order->created_at,
                        'updated_at' => $order->updated_at,
                    ]);
                }
            }

            // Seed Expenses for each day
            $expenseCategories = ['ingredients', 'utility', 'salary', 'other'];
            
            // Random expense for today and past days
            $expenseCount = rand(1, 3);
            for ($e = 0; $e < $expenseCount; $e++) {
                $category = $expenseCategories[rand(0, 3)];
                $amount = rand(50, 450) * 1000; // e.g. 50,000 to 450,000 UZS
                
                \App\Models\Expense::create([
                    'amount' => $amount,
                    'description' => $category . ' xarajatlari',
                    'category' => $category,
                    'created_at' => $date->copy()->setTime(rand(9, 18), rand(0, 59)),
                    'updated_at' => $date,
                ]);
            }
        }

        // Seed Ingredients
        $ingredients = [
            ['name' => 'Guruch', 'sku' => 'ING-00001', 'quantity' => 50.000, 'unit' => 'kg', 'cost_price' => 15000.00, 'low_stock_threshold' => 10.000],
            ['name' => 'Mol go\'shti', 'sku' => 'ING-00002', 'quantity' => 12.500, 'unit' => 'kg', 'cost_price' => 85000.00, 'low_stock_threshold' => 15.000], // Low stock
            ['name' => 'O\'simlik yog\'i', 'sku' => 'ING-00003', 'quantity' => 30.000, 'unit' => 'l', 'cost_price' => 18000.00, 'low_stock_threshold' => 8.000],
            ['name' => 'Osh tuzi', 'sku' => 'ING-00004', 'quantity' => 5.000, 'unit' => 'kg', 'cost_price' => 3000.00, 'low_stock_threshold' => 2.000],
            ['name' => 'Piyoz', 'sku' => 'ING-00005', 'quantity' => 4.200, 'unit' => 'kg', 'cost_price' => 4000.00, 'low_stock_threshold' => 5.000], // Low stock
            ['name' => 'Sabzi', 'sku' => 'ING-00006', 'quantity' => 18.000, 'unit' => 'kg', 'cost_price' => 5000.00, 'low_stock_threshold' => 6.000],
        ];

        foreach ($ingredients as $ing) {
            \App\Models\Ingredient::create($ing);
        }

        // Seed Recipe pivot items
        $palov = \App\Models\Food::where('name', 'Palov (Osh)')->first();
        $shashlik = \App\Models\Food::where('name', 'Mol go\'shtidan Shashlik')->first();
        $lagman = \App\Models\Food::where('name', 'Lag\'mon')->first();

        $rice = \App\Models\Ingredient::where('name', 'Guruch')->first();
        $meat = \App\Models\Ingredient::where('name', 'Mol go\'shti')->first();
        $oil = \App\Models\Ingredient::where('name', 'O\'simlik yog\'i')->first();
        $salt = \App\Models\Ingredient::where('name', 'Osh tuzi')->first();
        $onion = \App\Models\Ingredient::where('name', 'Piyoz')->first();
        $carrot = \App\Models\Ingredient::where('name', 'Sabzi')->first();

        if ($palov && $rice && $meat && $oil && $salt && $onion && $carrot) {
            \App\Models\Recipe::create(['food_id' => $palov->id, 'ingredient_id' => $rice->id, 'quantity_required' => 0.200]);
            \App\Models\Recipe::create(['food_id' => $palov->id, 'ingredient_id' => $meat->id, 'quantity_required' => 0.150]);
            \App\Models\Recipe::create(['food_id' => $palov->id, 'ingredient_id' => $oil->id, 'quantity_required' => 0.050]);
            \App\Models\Recipe::create(['food_id' => $palov->id, 'ingredient_id' => $salt->id, 'quantity_required' => 0.010]);
            \App\Models\Recipe::create(['food_id' => $palov->id, 'ingredient_id' => $onion->id, 'quantity_required' => 0.050]);
            \App\Models\Recipe::create(['food_id' => $palov->id, 'ingredient_id' => $carrot->id, 'quantity_required' => 0.150]);
        }

        if ($shashlik && $meat && $salt && $onion) {
            \App\Models\Recipe::create(['food_id' => $shashlik->id, 'ingredient_id' => $meat->id, 'quantity_required' => 0.250]);
            \App\Models\Recipe::create(['food_id' => $shashlik->id, 'ingredient_id' => $salt->id, 'quantity_required' => 0.005]);
            \App\Models\Recipe::create(['food_id' => $shashlik->id, 'ingredient_id' => $onion->id, 'quantity_required' => 0.050]);
        }

        if ($lagman && $meat && $onion && $carrot) {
            \App\Models\Recipe::create(['food_id' => $lagman->id, 'ingredient_id' => $meat->id, 'quantity_required' => 0.100]);
            \App\Models\Recipe::create(['food_id' => $lagman->id, 'ingredient_id' => $onion->id, 'quantity_required' => 0.030]);
            \App\Models\Recipe::create(['food_id' => $lagman->id, 'ingredient_id' => $carrot->id, 'quantity_required' => 0.050]);
        }

        // Seed discounts / promocodes
        \App\Models\Discount::create([
            'name' => 'Yangi yil chegirmasi',
            'type' => 'percentage',
            'value' => 15.00,
            'code' => 'YANGIYIL2026',
            'min_order_amount' => 100000.00,
            'starts_at' => now()->subDays(5),
            'expires_at' => now()->addDays(20),
            'is_active' => true,
        ]);

        \App\Models\Discount::create([
            'name' => 'Kofe chegirmasi',
            'type' => 'fixed',
            'value' => 15000.00,
            'code' => 'COFFEE26',
            'min_order_amount' => 50000.00,
            'starts_at' => now()->subDays(2),
            'expires_at' => now()->addDays(10),
            'is_active' => true,
        ]);

        \App\Models\Discount::create([
            'name' => 'Kompaniya yubileyi',
            'type' => 'percentage',
            'value' => 20.00,
            'code' => 'VRESTRO2026',
            'min_order_amount' => 0.00,
            'starts_at' => now()->subDays(10),
            'expires_at' => now()->addDays(30),
            'is_active' => true,
        ]);

        // Seed settings
        \App\Models\Setting::create(['key' => 'restaurant_name', 'value' => 'VRestro Restaurant', 'type' => 'string']);
        \App\Models\Setting::create(['key' => 'restaurant_address', 'value' => 'Toshkent, O\'zbekiston', 'type' => 'string']);
        \App\Models\Setting::create(['key' => 'restaurant_phone', 'value' => '+998901234567', 'type' => 'string']);
        \App\Models\Setting::create(['key' => 'restaurant_hours', 'value' => '09:00 - 23:00', 'type' => 'string']);
        \App\Models\Setting::create(['key' => 'restaurant_logo', 'value' => null, 'type' => 'file']);
        \App\Models\Setting::create(['key' => 'tax_rate', 'value' => '12', 'type' => 'number']);
        \App\Models\Setting::create(['key' => 'currency', 'value' => 'UZS', 'type' => 'string']);
        \App\Models\Setting::create(['key' => 'language', 'value' => 'uz', 'type' => 'string']);
        \App\Models\Setting::create(['key' => 'telegram_bot_token', 'value' => '8846820582:AAEYcOljJoCbDBfGNkuG-dntVw1dFfWdDWw', 'type' => 'string']);
        \App\Models\Setting::create(['key' => 'telegram_chat_id', 'value' => '@VRestro_uz', 'type' => 'string']);
        \App\Models\Setting::create(['key' => 'telegram_notifications_enabled', 'value' => 'true', 'type' => 'boolean']);
        \App\Models\Setting::create(['key' => 'service_charge_rate', 'value' => '10', 'type' => 'number']);
        \App\Models\Setting::create(['key' => 'receipt_header', 'value' => 'VRestro - Xizmatimizdan mamnunmisiz?', 'type' => 'string']);
        \App\Models\Setting::create(['key' => 'receipt_footer', 'value' => 'Xaridingiz uchun rahmat! Yana keling!', 'type' => 'string']);
    }
}
