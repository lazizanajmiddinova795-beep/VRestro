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
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            $settings = Setting::all();
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
        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type]
        );
        Cache::forget(self::CACHE_KEY);
    }
}
