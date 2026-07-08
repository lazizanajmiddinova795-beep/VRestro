<?php

namespace App\Services;

use App\Repositories\Contracts\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Authenticate user credentials.
     *
     * @param string $login
     * @param string $password
     * @return User
     * @throws ValidationException
     */
    public function verifyCredentials(string $login, string $password): User
    {
        $user = $this->userRepository->findByLogin($login);

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['Tizimga kirish ma\'lumotlari noto\'g\'ri.'],
            ]);
        }

        return $user;
    }

    /**
     * Complete authentication by verifying Face ID (simulated) and issuing token.
     *
     * @param int $userId
     * @param bool $faceVerified
     * @return array
     * @throws ValidationException
     */
    public function verifyBiometricsAndIssueToken(int $userId, bool $faceVerified): array
    {
        $user = $this->userRepository->findById($userId);

        if (!$user) {
            throw ValidationException::withMessages([
                'biometrics' => ['Foydalanuvchi topilmadi.'],
            ]);
        }

        if (!$faceVerified) {
            throw ValidationException::withMessages([
                'biometrics' => ['Face ID tekshiruvidan o\'tilmadi.'],
            ]);
        }

        // Revoke existing tokens if any
        $user->tokens()->delete();

        // Create Sanctum Token
        $token = $user->createToken('vrestro-auth-token')->plainTextToken;

        // Get user roles
        $roles = $user->getRoleNames();

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'login' => $user->login,
                'roles' => $roles,
            ],
            'token' => $token,
        ];
    }
}
