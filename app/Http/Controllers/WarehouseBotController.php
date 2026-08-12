<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Ingredient;
use App\Models\InventoryTransaction;
use App\Models\InventoryTransactionItem;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WarehouseBotController extends Controller
{
    const BOT_TOKEN = '7852632301:AAFBYv3qkWHDMEiEEG9cdkHderPmiKFkaTI';

    /**
     * Telegram webhook endpoint for warehouse bot.
     */
    public function webhook(Request $request): JsonResponse
    {
        $update = $request->all();

        if (!isset($update['message']['text'])) {
            return response()->json(['ok' => true]);
        }

        $message = $update['message'];
        $chatId = $message['chat']['id'];
        $text = trim($message['text']);

        // Handle commands
        if (str_starts_with($text, '/start') || str_starts_with($text, '/help')) {
            $this->handleStart($chatId);
            return response()->json(['ok' => true]);
        }

        if (str_starts_with($text, '/filiallar')) {
            $this->handleBranches($chatId);
            return response()->json(['ok' => true]);
        }

        if (str_starts_with($text, '/mahsulotlar')) {
            $branchName = trim(str_replace('/mahsulotlar', '', $text));
            $this->handleIngredientsList($chatId, $branchName);
            return response()->json(['ok' => true]);
        }

        if (str_starts_with($text, '/bugun')) {
            $this->handleTodayReport($chatId);
            return response()->json(['ok' => true]);
        }

        if (str_starts_with($text, '/')) {
            $this->sendMessage($chatId, "Noma'lum buyruq. /help bosing.");
            return response()->json(['ok' => true]);
        }

        // Regular text = process as kirim
        $this->processKirimMessage($chatId, $text, $message);

        return response()->json(['ok' => true]);
    }

    /**
     * /start and /help command
     */
    protected function handleStart(int $chatId): void
    {
        $text = "👋 <b>Assalomu alaykum!</b>\n\n";
        $text .= "Bu bot ombor kirimlarini (mahsulot qabul qilish) uchun xizmat qiladi.\n\n";
        $text .= "📝 <b>Kirim qilish formati:</b>\n";
        $text .= "<code>Filial nomi\nMahsulot1 miqdor\nMahsulot2 miqdor narx</code>\n\n";
        $text .= "📌 <b>Misol:</b>\n";
        $text .= "<code>Chorsu\nKartoshka 50\nPiyoz 30\nGo'sht 20 85000</code>\n\n";
        $text .= "💡 Narx yozilmasa — ombordagi mavjud narx ishlatiladi.\n\n";
        $text .= "📋 <b>Buyruqlar:</b>\n";
        $text .= "/filiallar — Barcha filiallar ro'yxati\n";
        $text .= "/mahsulotlar Filial — Filialdagi mahsulotlar\n";
        $text .= "/bugun — Bugungi kirimlar hisoboti\n";
        $text .= "/help — Yordam";

        $this->sendMessage($chatId, $text);
    }

    /**
     * /filiallar command — list all branches
     */
    protected function handleBranches(int $chatId): void
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        if ($branches->isEmpty()) {
            $this->sendMessage($chatId, "❌ Hech qanday filial topilmadi.");
            return;
        }

        $text = "🏪 <b>Mavjud filiallar:</b>\n\n";
        foreach ($branches as $i => $branch) {
            $ingredientCount = Ingredient::withoutGlobalScopes()
                ->where('branch_id', $branch->id)
                ->count();
            $text .= ($i + 1) . ". <b>{$branch->name}</b>";
            if ($branch->address) {
                $text .= " — {$branch->address}";
            }
            $text .= " ({$ingredientCount} ta mahsulot)\n";
        }

        $text .= "\n💡 Mahsulotlarni ko'rish: /mahsulotlar Filial nomi";

        $this->sendMessage($chatId, $text);
    }

    /**
     * /mahsulotlar command — list ingredients for a branch
     */
    protected function handleIngredientsList(int $chatId, string $branchName): void
    {
        if (empty($branchName)) {
            $this->sendMessage($chatId, "❌ Filial nomini kiriting.\n\n<b>Misol:</b> /mahsulotlar Chorsu");
            return;
        }

        $branch = Branch::where('is_active', true)
            ->whereRaw('LOWER(name) LIKE ?', ['%' . mb_strtolower($branchName) . '%'])
            ->first();

        if (!$branch) {
            $this->sendMessage($chatId, "❌ <b>\"{$branchName}\"</b> nomli filial topilmadi.\n\n/filiallar — ro'yxatni ko'rish");
            return;
        }

        $ingredients = Ingredient::withoutGlobalScopes()
            ->where('branch_id', $branch->id)
            ->orderBy('name')
            ->get();

        if ($ingredients->isEmpty()) {
            $this->sendMessage($chatId, "📦 <b>{$branch->name}</b> filialida hali mahsulot yo'q.");
            return;
        }

        $text = "📦 <b>{$branch->name}</b> filialdagi mahsulotlar:\n\n";
        foreach ($ingredients as $ing) {
            $stock = number_format($ing->quantity, 1);
            $emoji = $ing->is_low_stock ? '🔴' : '🟢';
            $text .= "{$emoji} <b>{$ing->name}</b> — {$stock} {$ing->unit}\n";
        }

        // Split into chunks if too long (Telegram 4096 char limit)
        if (mb_strlen($text) > 4000) {
            $chunks = str_split($text, 3900);
            foreach ($chunks as $chunk) {
                $this->sendMessage($chatId, $chunk);
            }
        } else {
            $this->sendMessage($chatId, $text);
        }
    }

    /**
     * /bugun command — today's kirim report
     */
    protected function handleTodayReport(int $chatId): void
    {
        $today = now()->toDateString();

        $transactions = InventoryTransaction::with(['items.ingredient', 'user'])
            ->where('type', 'kirim')
            ->whereDate('created_at', $today)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($transactions->isEmpty()) {
            $this->sendMessage($chatId, "📊 Bugun hali kirim qilinmagan.");
            return;
        }

        $text = "📊 <b>Bugungi kirimlar</b> ({$today})\n\n";

        foreach ($transactions as $tx) {
            $time = $tx->created_at->format('H:i');
            $notes = $tx->notes ?: '-';
            $text .= "🕒 <b>{$time}</b> — {$notes}\n";

            foreach ($tx->items as $item) {
                $name = $item->ingredient ? $item->ingredient->name : "#{$item->ingredient_id}";
                $unit = $item->ingredient ? $item->ingredient->unit : '';
                $price = $item->unit_price ? number_format((float)$item->unit_price, 0, '', ' ') : '-';
                $text .= "  • {$name}: {$item->quantity} {$unit} ({$price} so'm)\n";
            }
            $text .= "\n";
        }

        if (mb_strlen($text) > 4000) {
            $chunks = str_split($text, 3900);
            foreach ($chunks as $chunk) {
                $this->sendMessage($chatId, $chunk);
            }
        } else {
            $this->sendMessage($chatId, $text);
        }
    }

    /**
     * Process a multi-line kirim message.
     */
    protected function processKirimMessage(int $chatId, string $text, array $message): void
    {
        $lines = array_values(array_filter(array_map('trim', explode("\n", $text))));

        if (count($lines) < 2) {
            $this->sendMessage($chatId, "❌ <b>Xato format!</b>\n\nBirinchi qatorda filial nomi, keyingi qatorlarda mahsulotlar bo'lishi kerak.\n\n<b>Misol:</b>\n<code>Chorsu\nKartoshka 50\nPiyoz 30</code>");
            return;
        }

        // First line = branch name
        $branchName = array_shift($lines);
        $branch = Branch::where('is_active', true)
            ->whereRaw('LOWER(name) LIKE ?', ['%' . mb_strtolower($branchName) . '%'])
            ->first();

        if (!$branch) {
            $allBranches = Branch::where('is_active', true)->pluck('name')->toArray();
            $branchList = empty($allBranches) ? 'Hech qanday filial yo\'q' : implode("\n• ", $allBranches);
            $this->sendMessage($chatId, "❌ <b>\"{$branchName}\"</b> nomli filial topilmadi!\n\nMavjud filiallar:\n• {$branchList}\n\n/filiallar — to'liq ro'yxat");
            return;
        }

        // Parse ingredient lines
        $successItems = [];
        $failedItems = [];
        $kirimItems = [];

        foreach ($lines as $line) {
            $parsed = $this->parseIngredientLine($line, $branch->id);

            if ($parsed['success']) {
                $kirimItems[] = $parsed['item'];
                $successItems[] = $parsed;
            } else {
                $failedItems[] = $parsed;
            }
        }

        if (empty($kirimItems)) {
            $errorList = implode("\n", array_map(fn($f) => "• {$f['raw']} — {$f['error']}", $failedItems));
            $this->sendMessage($chatId, "❌ <b>Hech qanday mahsulot topilmadi!</b>\n\n{$errorList}\n\n/mahsulotlar {$branch->name} — mavjud mahsulotlar");
            return;
        }

        // Find a user to attribute the transaction
        $user = User::where('branch_id', $branch->id)
            ->whereHas('roles', fn($q) => $q->where('name', 'Manager'))
            ->first();

        if (!$user) {
            $user = User::where('branch_id', $branch->id)->first();
        }
        if (!$user) {
            $user = User::whereHas('roles', fn($q) => $q->where('name', 'Manager'))->first();
        }
        if (!$user) {
            $user = User::first();
        }

        if (!$user) {
            $this->sendMessage($chatId, "❌ Tizimda foydalanuvchi topilmadi.");
            return;
        }

        try {
            $senderName = trim(($message['from']['first_name'] ?? '') . ' ' . ($message['from']['last_name'] ?? ''));
            if (empty($senderName)) $senderName = 'Telegram Bot';

            // Execute kirim directly (bypassing global scopes)
            DB::transaction(function () use ($user, $kirimItems, $senderName, $branch) {
                $transaction = InventoryTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'kirim',
                    'notes' => "Telegram bot: {$senderName} ({$branch->name})",
                ]);

                foreach ($kirimItems as $item) {
                    $ingredient = Ingredient::withoutGlobalScopes()->find($item['ingredient_id']);
                    if (!$ingredient) continue;

                    $oldQty = (float) $ingredient->quantity;
                    $oldCost = (float) $ingredient->cost_price;
                    $incomingQty = (float) $item['quantity'];
                    $incomingPrice = (float) $item['unit_price'];

                    $newQty = $oldQty + $incomingQty;

                    // Moving average cost
                    if ($newQty > 0) {
                        $newCost = (($oldQty * $oldCost) + ($incomingQty * $incomingPrice)) / $newQty;
                    } else {
                        $newCost = $incomingPrice;
                    }

                    $ingredient->update([
                        'quantity' => $newQty,
                        'cost_price' => round($newCost, 2),
                    ]);

                    InventoryTransactionItem::create([
                        'transaction_id' => $transaction->id,
                        'ingredient_id' => $ingredient->id,
                        'quantity' => $incomingQty,
                        'unit_price' => $incomingPrice,
                        'old_quantity' => $oldQty,
                        'new_quantity' => $newQty,
                    ]);
                }

                Cache::forget('admin_dashboard_analytics');
            });

            // Build success report
            $report = "✅ <b>Kirim muvaffaqiyatli saqlandi!</b>\n\n";
            $report .= "🏪 <b>Filial:</b> {$branch->name}\n";
            $report .= "👤 <b>Kirituvchi:</b> {$senderName}\n";
            $report .= "🕒 <b>Vaqt:</b> " . now()->format('d.m.Y, H:i') . "\n\n";
            $report .= "📦 <b>Qabul qilingan mahsulotlar:</b>\n";

            foreach ($successItems as $s) {
                $priceText = $s['price'] ? number_format($s['price'], 0, '', ' ') . " so'm" : 'eski narx';
                $newTag = !empty($s['is_new']) ? ' 🆕' : '';
                $report .= "  ✅ <b>{$s['name']}</b>: {$s['quantity']} {$s['unit']} ({$priceText}){$newTag}\n";
            }

            // Show list of newly created ingredients
            $newItems = array_filter($successItems, fn($s) => !empty($s['is_new']));
            if (!empty($newItems)) {
                $report .= "\n🆕 <b>Yangi yaratilgan mahsulotlar:</b>\n";
                foreach ($newItems as $n) {
                    $report .= "  • <b>{$n['name']}</b> ({$n['unit']}) — Masalliqlar bo'limiga qo'shildi\n";
                }
            }

            if (!empty($failedItems)) {
                $report .= "\n⚠️ <b>Topilmagan mahsulotlar:</b>\n";
                foreach ($failedItems as $f) {
                    $report .= "  ❌ {$f['raw']} — {$f['error']}\n";
                }
            }

            $report .= "\n📊 /bugun — bugungi barcha kirimlar";

            $this->sendMessage($chatId, $report);

        } catch (\Exception $e) {
            Log::error("WarehouseBot kirim error: " . $e->getMessage());
            $this->sendMessage($chatId, "❌ Kirimni saqlashda xatolik:\n" . $e->getMessage());
        }
    }

    /**
     * Parse a single ingredient line and auto-create if not found.
     * Formats:  "Kartoshka 50"  or  "Kartoshka 50 8000"  or  "Go'sht 20 kg 85000"
     */
    protected function parseIngredientLine(string $line, int $branchId): array
    {
        $parts = preg_split('/\s+/', trim($line));

        if (count($parts) < 2) {
            return ['success' => false, 'raw' => $line, 'error' => 'Miqdor kiritilmagan'];
        }

        $knownUnits = ['kg', 'g', 'l', 'ml', 'dona', 'pachka', 'litr'];

        // Scan from the end for numbers and optional unit
        $numbers = [];
        $detectedUnit = null;
        $namePartEnd = count($parts);

        for ($i = count($parts) - 1; $i >= 1; $i--) {
            $val = str_replace(',', '.', $parts[$i]);
            if (is_numeric($val)) {
                $numbers[] = (float) $val;
                $namePartEnd = $i;
            } elseif (in_array(mb_strtolower($parts[$i]), $knownUnits) && empty($detectedUnit)) {
                $detectedUnit = mb_strtolower($parts[$i]);
                if ($detectedUnit === 'litr') $detectedUnit = 'l';
                $namePartEnd = $i;
            } else {
                break;
            }
        }

        $numbers = array_reverse($numbers);

        if (empty($numbers)) {
            return ['success' => false, 'raw' => $line, 'error' => 'Miqdor kiritilmagan'];
        }

        $ingredientName = implode(' ', array_slice($parts, 0, $namePartEnd));
        $quantity = $numbers[0];
        $unitPrice = count($numbers) > 1 ? $numbers[1] : null;

        if ($quantity <= 0) {
            return ['success' => false, 'raw' => $line, 'error' => 'Miqdor noto\'g\'ri'];
        }

        // Find ingredient — exact match first, then partial
        $ingredient = Ingredient::withoutGlobalScopes()
            ->where('branch_id', $branchId)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($ingredientName)])
            ->first();

        if (!$ingredient) {
            $ingredient = Ingredient::withoutGlobalScopes()
                ->where('branch_id', $branchId)
                ->whereRaw('LOWER(name) LIKE ?', ['%' . mb_strtolower($ingredientName) . '%'])
                ->first();
        }

        $isNew = false;

        // Auto-create if not found
        if (!$ingredient) {
            $unit = $detectedUnit ?: 'kg';
            $sku = 'ING-' . strtoupper(\Illuminate\Support\Str::random(5));

            $ingredient = new Ingredient();
            $ingredient->branch_id = $branchId;
            $ingredient->name = mb_convert_case($ingredientName, MB_CASE_TITLE, 'UTF-8');
            $ingredient->sku = $sku;
            $ingredient->quantity = 0;
            $ingredient->unit = $unit;
            $ingredient->cost_price = $unitPrice ?? 0;
            $ingredient->sell_price = null;
            $ingredient->low_stock_threshold = 5;
            $ingredient->save();

            $isNew = true;
        }

        $finalPrice = $unitPrice ?? (float) $ingredient->cost_price;

        return [
            'success' => true,
            'raw' => $line,
            'name' => $ingredient->name,
            'quantity' => $quantity,
            'unit' => $ingredient->unit,
            'price' => $unitPrice,
            'is_new' => $isNew,
            'item' => [
                'ingredient_id' => $ingredient->id,
                'quantity' => $quantity,
                'unit_price' => $finalPrice,
            ],
        ];
    }

    /**
     * Send message via Telegram Bot API.
     */
    protected function sendMessage(int|string $chatId, string $text): void
    {
        try {
            Http::timeout(10)->post("https://api.telegram.org/bot" . self::BOT_TOKEN . "/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
            ]);
        } catch (\Exception $e) {
            Log::error("WarehouseBot sendMessage error: " . $e->getMessage());
        }
    }
}
