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

        $user = $this->authService->verifyCredentials(
            $credentials['login'],
            $credentials['password']
        );

        return response()->json([
            'message' => 'Credentials verified. Proceed to biometric check.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'login' => $user->login,
                'face_registered' => $user->face_registered,
            ]
        ]);
    }

    /**
     * Step 2: Verify simulated Face ID and issue token.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function verifyFace(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer'],
            'face_verified' => ['required', 'boolean'],
        ]);

        $authData = $this->authService->verifyBiometricsAndIssueToken(
            $data['user_id'],
            $data['face_verified']
        );

        return response()->json([
            'message' => 'Authentication successful.',
            'user' => $authData['user'],
            'token' => $authData['token']
        ]);
    }
}
