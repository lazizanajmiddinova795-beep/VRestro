<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    protected TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    public function openShift(Request $request): JsonResponse
    {
        $user = auth()->user();
        $name = $user ? $user->name : 'Noma\'lum kassir';
        
        $openedAt = now()->format('d.m.Y, H:i:s');
        
        $text = "🟢 <b>Smena boshlandi</b>\n\n";
        $text .= "👤 <b>Kassir:</b> {$name}\n";
        $text .= "🕒 <b>Boshlangan vaqt:</b> {$openedAt}";

        $this->telegramService->sendMessage($text);

        return response()->json([
            'success' => true,
            'message' => 'Smena boshlangani haqida xabar yuborildi.'
        ]);
    }

    public function closeShift(Request $request): JsonResponse
    {
        $user = auth()->user();
        $name = $user ? $user->name : 'Noma\'lum kassir';
        
        $openedAt = $request->input('opened_at', 'Noma\'lum');
        $cash = number_format((float)$request->input('cash', 0), 0, '', ' ') . ' UZS';
        $card = number_format((float)$request->input('card', 0), 0, '', ' ') . ' UZS';
        $qr = number_format((float)$request->input('qr', 0), 0, '', ' ') . ' UZS';
        $total = number_format((float)$request->input('total', 0), 0, '', ' ') . ' UZS';
        $count = $request->input('count', 0);

        $text = "🔒 <b>Smena yopildi (Z-Report)</b>\n\n";
        $text .= "👤 <b>Kassir:</b> {$name}\n";
        $text .= "🕒 <b>Ochilgan vaqt:</b> {$openedAt}\n";
        $text .= "🕒 <b>Yopilgan vaqt:</b> " . now()->format('d.m.Y, H:i:s') . "\n\n";
        $text .= "💵 <b>Naqd pul:</b> {$cash}\n";
        $text .= "💳 <b>Plastik:</b> {$card}\n";
        $text .= "📱 <b>QR to'lov:</b> {$qr}\n";
        $text .= "🧾 <b>Jami cheklar:</b> {$count} ta\n";
        $text .= "💰 <b>Jami tushum:</b> {$total}\n";

        $this->telegramService->sendMessage($text);

        return response()->json([
            'success' => true,
            'message' => 'Smena muvaffaqiyatli yakunlandi.'
        ]);
    }
}
