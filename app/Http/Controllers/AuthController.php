<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Step 1: Verify user credentials (login & password).
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $authData = $this->authService->verifyCredentials(
            $credentials['login'],
            $credentials['password']
        );

        if (!empty($authData['requires_otp'])) {
            return response()->json([
                'requires_otp' => true,
                'message' => 'Login tekshirildi. Telegram orqali 8 xonali parolni kiriting.',
                'user' => $authData['user'],
            ]);
        }

        // Log direct login (no OTP)
        $logUser = \App\Models\User::find($authData['user']['id'] ?? null);
        ActivityLog::record(
            'login',
            ($authData['user']['name'] ?? 'Noma\'lum') . " tizimga kirdi",
            'auth',
            ['user_id' => $authData['user']['id'] ?? null],
            $logUser
        );

        return response()->json([
            'requires_otp' => false,
            'message' => 'Tizimga kirish muvaffaqiyatli yakunlandi.',
            'user' => $authData['user'],
            'token' => $authData['token']
        ]);
    }

    /**
     * Login via PIN code.
     */
    public function loginWithPin(Request $request): JsonResponse
    {
        $data = $request->validate([
            'pin' => ['required', 'string']
        ]);

        $authData = $this->authService->verifyPin($data['pin']);

        if (!empty($authData['requires_otp'])) {
            return response()->json([
                'requires_otp' => true,
                'message' => 'Login tekshirildi. Telegram orqali 8 xonali parolni kiriting.',
                'user' => $authData['user'],
            ]);
        }

        // Log direct login via PIN
        $logUser = \App\Models\User::find($authData['user']['id'] ?? null);
        ActivityLog::record(
            'login',
            ($authData['user']['name'] ?? 'Noma\'lum') . " tizimga PIN orqali kirdi",
            'auth',
            ['user_id' => $authData['user']['id'] ?? null],
            $logUser
        );

        return response()->json([
            'requires_otp' => false,
            'message' => 'Tizimga kirish muvaffaqiyatli yakunlandi.',
            'user' => $authData['user'],
            'token' => $authData['token']
        ]);
    }

    /**
     * Step 2: Verify 8-digit Telegram OTP code and issue token.
     */
    public function verifyFace(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer'],
            'otp' => ['required', 'string', 'size:8'],
        ]);

        $authData = $this->authService->verifyOtpAndIssueToken(
            $data['user_id'],
            $data['otp']
        );

        // Log OTP-verified login
        $logUser = \App\Models\User::find($data['user_id']);
        ActivityLog::record(
            'login',
            ($authData['user']['name'] ?? 'Noma\'lum') . " tizimga kirdi (OTP orqali)",
            'auth',
            ['user_id' => $data['user_id']],
            $logUser
        );

        return response()->json([
            'message' => 'Authentication successful.',
            'user' => $authData['user'],
            'token' => $authData['token']
        ]);
    }
}
