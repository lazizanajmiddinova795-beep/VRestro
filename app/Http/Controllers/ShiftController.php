<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function closeShift(Request $request): JsonResponse
    {
        // Removed open orders check so cashiers can change shifts while customers are dining

        return response()->json([
            'success' => true,
            'message' => 'Smena muvaffaqiyatli yakunlandi.'
        ]);
    }
}
