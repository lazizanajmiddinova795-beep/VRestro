<?php

namespace App\Services;

use App\Repositories\Contracts\SettingRepositoryInterface;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\UploadedFile;

class SettingService
{
    protected SettingRepositoryInterface $settingRepository;

    public function __construct(SettingRepositoryInterface $settingRepository)
    {
        $this->settingRepository = $settingRepository;
    }

    /**
     * Update settings keys inside a database transaction.
     */
    public function updateSettings(array $settingsData, array $files = []): void
    {
        DB::transaction(function () use ($settingsData, $files) {
            
            // 1. Process regular string/number/boolean fields
            foreach ($settingsData as $key => $value) {
                // Determine setting type
                $type = 'string';
                if (is_numeric($value)) {
                    $type = 'number';
                } elseif (is_bool($value) || $value === 'true' || $value === 'false') {
                    $type = 'boolean';
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
                }

                $this->settingRepository->setKeyValue($key, (string) $value, $type);
            }

            // 2. Process file uploads (e.g. restaurant logo)
            foreach ($files as $key => $file) {
                if ($file instanceof UploadedFile) {
                    
                    // Delete old logo file if exists
                    $oldLogoPath = Setting::where('key', $key)->first()?->value;
                    if ($oldLogoPath && Storage::disk('public')->exists($oldLogoPath)) {
                        Storage::disk('public')->delete($oldLogoPath);
                    }

                    $path = $file->store('branding', 'public');
                    $this->settingRepository->setKeyValue($key, $path, 'file');
                }
            }
        });
    }

    /**
     * Handles administrative security credentials rotation.
     */
    public function changePassword(int $userId, string $oldPassword, string $newPassword): void
    {
        $user = User::findOrFail($userId);

        if (!Hash::check($oldPassword, $user->password)) {
            throw ValidationException::withMessages([
                'old_password' => ['Joriy parol xato kiritildi.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($newPassword),
        ]);
    }
}
