<?php

namespace App\Http\Controllers;

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

        return response()->json([
            'message' => 'Authentication successful.',
            'user' => $authData['user'],
            'token' => $authData['token']
        ]);
    }
}
