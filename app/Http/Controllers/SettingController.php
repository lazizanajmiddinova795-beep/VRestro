<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\SettingRepositoryInterface;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    protected SettingService $settingService;
    protected SettingRepositoryInterface $settingRepository;

    public function __construct(SettingService $settingService, SettingRepositoryInterface $settingRepository)
    {
        $this->settingService = $settingService;
        $this->settingRepository = $settingRepository;
    }

    /**
     * Get all settings.
     */
    public function index(): JsonResponse
    {
        $settings = $this->settingRepository->getAllKeyValue();
        return response()->json($settings);
    }

    /**
     * Update settings.
     */
    public function update(Request $request): JsonResponse
    {
        // Allow any input since we map key-value dynamically
        $settingsData = $request->except(['restaurant_logo', '_method']);
        $files = $request->only(['restaurant_logo']);

        $this->settingService->updateSettings($settingsData, $files);

        return response()->json([
            'message' => 'Sozlamalar muvaffaqiyatli saqlandi.',
            'settings' => $this->settingRepository->getAllKeyValue()
        ]);
    }

    /**
     * Change user password.
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'old_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $this->settingService->changePassword(
            Auth::id(),
            $request->input('old_password'),
            $request->input('new_password')
        );

        return response()->json([
            'message' => 'Parol muvaffaqiyatli yangilandi.'
        ]);
    }

    /**
     * Update user profile details.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone,' . $user->id],
            'email' => ['nullable', 'email', 'max:100'],
            'passport_number' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:255'],
            'avatar_url' => ['nullable', 'string', 'max:255'],
        ]);

        $user->update($data);

        return response()->json([
            'message' => 'Profil muvaffaqiyatli yangilandi.',
            'user' => $user
        ]);
    }

    /**
     * Clear system cache.
     */
    public function clearCache(): JsonResponse
    {
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        
        return response()->json([
            'message' => 'Tizim keshlari muvaffaqiyatli tozalandi.'
        ]);
    }

    /**
     * Test Telegram connection.
     */
    public function testTelegram(Request $request): JsonResponse
    {
        $request->validate([
            'telegram_bot_token' => ['required', 'string'],
            'telegram_chat_id' => ['required', 'string'],
        ]);

        $token = $request->input('telegram_bot_token');
        $chatId = $request->input('telegram_chat_id');

        try {
            $url = "https://api.telegram.org/bot{$token}/sendMessage";
            $response = \Illuminate\Support\Facades\Http::timeout(5)->post($url, [
                'chat_id' => $chatId,
                'text' => "🔔 <b>VRestro ERP Integratsiyasi</b>\nTelegram bot muvaffaqiyatli sinovdan o'tdi!",
                'parse_mode' => 'HTML',
            ]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Telegram xabari muvaffaqiyatli yuborildi!'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Telegram xatosi: ' . ($response->json()['description'] ?? 'Noma\'lum xatolik')
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ulanish xatosi: ' . $e->getMessage()
            ], 500);
        }
    }
}
