<?php

namespace App\Services;

use App\Repositories\Contracts\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class StaffService
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Create new staff member.
     *
     * @param int $currentUserId
     * @param array $data
     * @return User
     */
    public function createStaff(int $currentUserId, array $data): User
    {
        return DB::transaction(function () use ($data) {
            $data['password'] = Hash::make($data['password']);
            $data['face_registered'] = false; // Initial onboarding requires face register via auth controller later

            $user = $this->userRepository->create($data);
            
            // Assign Spatie permission role
            $user->assignRole($data['role']);

            return $user;
        });
    }

    /**
     * Update staff member.
     *
     * @param int $currentUserId
     * @param int $id
     * @param array $data
     * @return User
     * @throws ValidationException
     */
    public function updateStaff(int $currentUserId, int $id, array $data): User
    {
        return DB::transaction(function () use ($currentUserId, $id, $data) {
            $user = $this->userRepository->findById($id);

            if (!$user) {
                throw ValidationException::withMessages([
                    'user' => ['Xodim topilmadi.'],
                ]);
            }

            $currentUser = User::find($currentUserId);

            // Super-Admin protection: Only super-admin can edit super-admin
            if ($user->is_superadmin && (!$currentUser || !$currentUser->is_superadmin)) {
                throw ValidationException::withMessages([
                    'user' => ['Bosh administrator ma\'lumotlarini faqat Bosh administrator tahrirlashi mumkin!'],
                ]);
            }

            // Prevent self-role modification if changing from Admin to something else
            if ($id === $currentUserId && $data['role'] !== 'Admin' && $user->hasRole('Admin')) {
                throw ValidationException::withMessages([
                    'role' => ['O\'zingizning administratorlik rolingizni o\'zgartira olmaysiz.'],
                ]);
            }

            // Last active admin safeguard
            if ($user->hasRole('Admin') && ($data['role'] !== 'Admin' || $data['status'] === 'inactive')) {
                $activeAdminsCount = User::whereHas('roles', fn($q) => $q->where('name', 'Admin'))
                    ->where('status', 'active')
                    ->where('id', '!=', $id)
                    ->count();

                if ($activeAdminsCount < 1) {
                    throw ValidationException::withMessages([
                        'status' => ['Tizimda kamida bitta faol administrator qolishi shart! Ularning rolini yoki faolligini o\'zgartirib bo\'lmaydi.'],
                    ]);
                }
            }

            // Handle password updating
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $updatedUser = $this->userRepository->update($id, $data);

            // Sync Spatie role
            $updatedUser->syncRoles([$data['role']]);

            return $updatedUser;
        });
    }

    public function toggleStatus(int $currentUserId, int $id): User
    {
        return DB::transaction(function () use ($currentUserId, $id) {
            if ($id === $currentUserId) {
                throw ValidationException::withMessages([
                    'status' => ['O\'zingizning faollik holatingizni o\'zgartira olmaysiz.'],
                ]);
            }

            $user = $this->userRepository->findById($id);

            if (!$user) {
                throw ValidationException::withMessages([
                    'user' => ['Xodim topilmadi.'],
                ]);
            }

            $currentUser = User::find($currentUserId);

            // Super-Admin protection
            if ($user->is_superadmin && (!$currentUser || !$currentUser->is_superadmin)) {
                throw ValidationException::withMessages([
                    'user' => ['Bosh administrator faollik holatini faqat Bosh administrator o\'zgartirishi mumkin!'],
                ]);
            }

            // Last active admin safeguard
            if ($user->hasRole('Admin') && $user->status === 'active') {
                $activeAdminsCount = User::whereHas('roles', fn($q) => $q->where('name', 'Admin'))
                    ->where('status', 'active')
                    ->where('id', '!=', $id)
                    ->count();

                if ($activeAdminsCount < 1) {
                    throw ValidationException::withMessages([
                        'status' => ['Tizimda kamida bitta faol administrator qolishi shart! Ularni faolsizlantirib bo\'lmaydi.'],
                    ]);
                }
            }

            $newStatus = $user->status === 'active' ? 'inactive' : 'active';
            $user->update(['status' => $newStatus]);

            // If deactivated, revoke all active Sanctum auth sessions instantly
            if ($newStatus === 'inactive') {
                $user->tokens()->delete();
            }

            return $user;
        });
    }

    public function deleteStaff(int $currentUserId, int $id): bool
    {
        return DB::transaction(function () use ($currentUserId, $id) {
            if ($id === $currentUserId) {
                throw ValidationException::withMessages([
                    'user' => ['O\'zingizning tizimdagi profilingizni o\'chira olmaysiz.'],
                ]);
            }

            $user = $this->userRepository->findById($id);

            if (!$user) {
                return false;
            }

            $currentUser = User::find($currentUserId);

            // Super-Admin protection
            if ($user->is_superadmin && (!$currentUser || !$currentUser->is_superadmin)) {
                throw ValidationException::withMessages([
                    'user' => ['Bosh administratorni faqat Bosh administrator o\'chira oladi!'],
                ]);
            }

            // Last active admin safeguard
            if ($user->hasRole('Admin')) {
                $activeAdminsCount = User::whereHas('roles', fn($q) => $q->where('name', 'Admin'))
                    ->where('status', 'active')
                    ->where('id', '!=', $id)
                    ->count();

                if ($activeAdminsCount < 1) {
                    throw ValidationException::withMessages([
                        'user' => ['Tizimda kamida bitta faol administrator qolishi shart! Ularni o\'chirib bo\'lmaydi.'],
                    ]);
                }
            }

            // Invalidate logins
            $user->tokens()->delete();

            return $this->userRepository->delete($id);
        });
    }
}
