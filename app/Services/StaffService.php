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

            // Prevent self-role modification if changing from Admin to something else
            if ($id === $currentUserId && $data['role'] !== 'Admin' && $user->hasRole('Admin')) {
                throw ValidationException::withMessages([
                    'role' => ['O\'zingizning administratorlik rolingizni o\'zgartira olmaysiz.'],
                ]);
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

    /**
     * Toggle staff active/inactive status.
     *
     * @param int $currentUserId
     * @param int $id
     * @return User
     * @throws ValidationException
     */
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

            $newStatus = $user->status === 'active' ? 'inactive' : 'active';
            $user->update(['status' => $newStatus]);

            // If deactivated, revoke all active Sanctum auth sessions instantly
            if ($newStatus === 'inactive') {
                $user->tokens()->delete();
            }

            return $user;
        });
    }

    /**
     * Delete staff member safely.
     *
     * @param int $currentUserId
     * @param int $id
     * @return bool
     * @throws ValidationException
     */
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

            // Invalidate logins
            $user->tokens()->delete();

            return $this->userRepository->delete($id);
        });
    }
}
