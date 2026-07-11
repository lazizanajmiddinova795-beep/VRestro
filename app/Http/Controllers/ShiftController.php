<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function closeShift(Request $request): JsonResponse
    {
        $openOrdersExist = Order::where('status', '!=', 'delivered')
                                ->where('status', '!=', 'cancelled')
                                ->exists();

        if ($openOrdersExist) {
            return response()->json([
                'success' => false,
                'message' => "Smenani yopib bo'lmaydi! Tizimda hali to'lovi qilinmagan faol stollar mavjud."
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Smena muvaffaqiyatli yakunlandi.'
        ]);
    }
}
