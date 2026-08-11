<?php

namespace App\Repositories;

use App\Models\Setting;
use App\Repositories\Contracts\SettingRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class SettingRepository implements SettingRepositoryInterface
{
    protected const CACHE_KEY = 'global_settings';
    protected const CACHE_TTL = 3600; // 1 hour

    /**
     * Get all settings as flat key-value mapped dictionary.
     */
    public function getAllKeyValue(): array
    {
        $branchId = request()->header('X-Branch-Id') ?? auth()->user()?->branch_id ?? 'global';
        $cacheKey = self::CACHE_KEY . '_' . $branchId;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            // Fetch global settings first (branch_id is null), then branch settings (overrides)
            // By ordering by branch_id NULLs first, branch_id overrides global values in the flat array
            $settings = Setting::orderByRaw('branch_id IS NOT NULL, branch_id')->get();
            $flat = [];
            foreach ($settings as $setting) {
                $flat[$setting->key] = $setting->cast_value;
            }
            return $flat;
        });
    }

    /**
     * Get value of a specific setting key.
     */
    public function getByKey(string $key)
    {
        $all = $this->getAllKeyValue();
        return $all[$key] ?? null;
    }

    /**
     * Set/update setting key value atomically.
     */
    public function setKeyValue(string $key, ?string $value, string $type): void
    {
        $branchId = request()->header('X-Branch-Id') ?? auth()->user()?->branch_id;
        
        Setting::updateOrCreate(
            ['key' => $key, 'branch_id' => $branchId],
            ['value' => $value, 'type' => $type]
        );
        
        $cacheKey = self::CACHE_KEY . '_' . ($branchId ?? 'global');
        Cache::forget($cacheKey);
    }
}
