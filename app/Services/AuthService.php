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
    public function verifyCredentials(string $login, string $password): array
    {
        $user = $this->userRepository->findByLogin(trim($login));

        if (!$user || !Hash::check(trim($password), $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['Tizimga kirish ma\'lumotlari noto\'g\'ri.'],
            ]);
        }

        // Check if user is Admin and 2FA is enabled
        if ($user->hasRole('Manager')) {
            $settingRepo = app(\App\Repositories\Contracts\SettingRepositoryInterface::class);
            $twoFaEnabled = filter_var($settingRepo->getByKey('telegram_2fa_enabled'), FILTER_VALIDATE_BOOLEAN);

            if ($twoFaEnabled) {
                // Generate 8-digit OTP for Admin
                $otp = sprintf("%08d", mt_rand(10000000, 99999999));
                $user->telegram_otp = $otp;
                $user->telegram_otp_expires_at = now()->addMinutes(10);
                $user->save();

                // Send OTP to Telegram Channel using TelegramService
                try {
                    $telegramService = app(\App\Services\TelegramService::class);
                    $message = "🔐 <b>FoodFlow Admin Autentifikatsiya Kodi</b>\n\n";
                    $message .= "Administrator: <b>{$user->name}</b> ({$user->login})\n";
                    $message .= "Kirish uchun 8 xonali tasdiqlash kodi: <code>{$otp}</code>\n\n";
                    $message .= "⚠️ Ushbu kod 10 daqiqa davomida faol bo'ladi.";
                    $telegramService->sendMessage($message);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Failed to send OTP to Telegram: " . $e->getMessage());
                }

                return [
                    'requires_otp' => true,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'login' => $user->login,
                        'branch_name' => $user->branch ? $user->branch->name : null,
                    ],
                ];
            }
        }

        // For non-admin employees (Cashier, Chef, Waiter, etc.): Issue token directly
        $user->tokens()->delete();
        $token = $user->createToken('vrestro-auth-token')->plainTextToken;
        $roles = $user->getRoleNames();

        return [
            'requires_otp' => false,
            'user' => $this->presentUser($user, $roles),
            'token' => $token,
        ];
    }

    /**
     * Authenticate user via PIN.
     *
     * @param string $pin
     * @return array
     * @throws ValidationException
     */
    public function verifyPin(string $pin): array
    {
        $user = User::where('pin', trim($pin))->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'pin' => ['PIN kod orqali xodim topilmadi yoki noto\'g\'ri.'],
            ]);
        }

        // Check if user is Admin and 2FA is enabled
        if ($user->hasRole('Manager')) {
            $settingRepo = app(\App\Repositories\Contracts\SettingRepositoryInterface::class);
            $twoFaEnabled = filter_var($settingRepo->getByKey('telegram_2fa_enabled'), FILTER_VALIDATE_BOOLEAN);

            if ($twoFaEnabled) {
                // Generate 8-digit OTP for Admin
                $otp = sprintf("%08d", mt_rand(10000000, 99999999));
                $user->telegram_otp = $otp;
                $user->telegram_otp_expires_at = now()->addMinutes(10);
                $user->save();

                // Send OTP to Telegram Channel using TelegramService
                try {
                    $telegramService = app(\App\Services\TelegramService::class);
                    $message = "🔐 <b>FoodFlow Admin Autentifikatsiya Kodi (PIN)</b>\n\n";
                    $message .= "Administrator: <b>{$user->name}</b> ({$user->login})\n";
                    $message .= "Kirish uchun 8 xonali tasdiqlash kodi: <code>{$otp}</code>\n\n";
                    $message .= "⚠️ Ushbu kod 10 daqiqa davomida faol bo'ladi.";
                    $telegramService->sendMessage($message);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Failed to send OTP to Telegram: " . $e->getMessage());
                }

                return [
                    'requires_otp' => true,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'login' => $user->login,
                        'branch_name' => $user->branch ? $user->branch->name : null,
                    ],
                ];
            }
        }

        // For non-admin employees (Cashier, Chef, Waiter, etc.): Issue token directly
        $user->tokens()->delete();
        $token = $user->createToken('vrestro-auth-token')->plainTextToken;
        $roles = $user->getRoleNames();

        return [
            'requires_otp' => false,
            'user' => $this->presentUser($user, $roles),
            'token' => $token,
        ];
    }

    /**
     * Shape the profile fields returned to the frontend after login, so a
     * staff member's own profile screen shows the same data the admin
     * entered when creating their account (name/login/roles alone left
     * phone, email, passport, birth date, address and avatar blank there).
     */
    protected function presentUser(User $user, $roles): array
    {
        return [
            'id'              => $user->id,
            'name'            => $user->name,
            'login'           => $user->login,
            'phone'           => $user->phone,
            'email'           => $user->email,
            'passport_number' => $user->passport_number,
            'birth_date'      => $user->birth_date,
            'address'         => $user->address,
            'avatar_url'      => $user->avatar_url,
            'shift_hours'     => $user->shift_hours,
            'is_superadmin'   => (bool) $user->is_superadmin,
            'roles'           => $roles,
            'branch_id'       => $user->branch_id,
            'branch_name'     => $user->branch ? $user->branch->name : null,
        ];
    }

    /**
     * Verify 8-digit Telegram OTP code and issue bearer token.
     */
    public function verifyOtpAndIssueToken(int $userId, string $otp): array
    {
        $user = $this->userRepository->findById($userId);

        if (!$user) {
            throw ValidationException::withMessages([
                'otp' => ['Foydalanuvchi topilmadi.'],
            ]);
        }

        if (!$user->telegram_otp || $user->telegram_otp !== $otp || now()->greaterThan($user->telegram_otp_expires_at)) {
            throw ValidationException::withMessages([
                'otp' => ['Tasdiqlash kodi noto\'g\'ri yoki eskirgan.'],
            ]);
        }

        // Clear OTP on success
        $user->telegram_otp = null;
        $user->telegram_otp_expires_at = null;
        $user->save();

        // Revoke existing tokens if any
        $user->tokens()->delete();

        // Create Sanctum Token
        $token = $user->createToken('vrestro-auth-token')->plainTextToken;

        // Get user roles
        $roles = $user->getRoleNames();

        return [
            'user' => $this->presentUser($user, $roles),
            'token' => $token,
        ];
    }
}
