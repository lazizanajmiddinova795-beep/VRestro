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
        $adminRole = Role::create(['name' => 'Admin']);
        $chefRole = Role::create(['name' => 'Chef']);
        $waiterRole = Role::create(['name' => 'Waiter']);
        $cashierRole = Role::create(['name' => 'Cashier']);

        // Create Permissions
        $managePayments = Permission::create(['name' => 'manage payments']);
        $adminRole->givePermissionTo($managePayments);
        $cashierRole->givePermissionTo($managePayments);

        // Create Admin User
        $adminUser = User::create([
            'name' => 'Tizim Administratori',
            'login' => 'admin',
            'password' => Hash::make('admin123'),
            'face_registered' => true,
            'phone' => '+998901234567',
            'shift_hours' => '09:00 - 18:00',
            'status' => 'active',
        ]);
        $adminUser->assignRole($adminRole);

        // Create Chef User
        $chefUser = User::create([
            'name' => 'Asilbek Povar',
            'login' => 'chef',
            'password' => Hash::make('chef123'),
            'face_registered' => true,
            'phone' => '+998901234568',
            'shift_hours' => '08:00 - 20:00',
            'status' => 'active',
        ]);
        $chefUser->assignRole($chefRole);

        // Create Waiter User
        $waiterUser = User::create([
            'name' => 'Davron Ofitsiant',
            'login' => 'waiter',
            'password' => Hash::make('waiter123'),
            'face_registered' => true,
            'phone' => '+998901234569',
            'shift_hours' => '10:00 - 22:00',
            'status' => 'active',
        ]);
        $waiterUser->assignRole($waiterRole);

        // Create Cashier User
        $cashierUser = User::create([
            'name' => 'Lobar Kassir',
            'login' => 'cashier',
            'password' => Hash::make('cashier123'),
            'face_registered' => true,
            'phone' => '+998901234570',
            'shift_hours' => '09:00 - 21:00',
            'status' => 'active',
        ]);
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
            ['name' => 'Palov (Osh)', 'price' => 45000, 'category_id' => $categories[0]->id],
            ['name' => 'Lag\'mon', 'price' => 35000, 'category_id' => $categories[0]->id],
            ['name' => 'Mol go\'shtidan Shashlik', 'price' => 25000, 'category_id' => $categories[0]->id],
            ['name' => 'Sezar Salati', 'price' => 30000, 'category_id' => $categories[2]->id],
            ['name' => 'Achchiq-chuchuk', 'price' => 15000, 'category_id' => $categories[2]->id],
            ['name' => 'Coca-Cola 1.5L', 'price' => 12000, 'category_id' => $categories[1]->id],
            ['name' => 'Limonli Ko\'k Choy', 'price' => 15000, 'category_id' => $categories[1]->id],
            ['name' => 'Paxlava', 'price' => 25000, 'category_id' => $categories[3]->id],
            ['name' => 'Muzqaymoq', 'price' => 18000, 'category_id' => $categories[3]->id],
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
    }
}
