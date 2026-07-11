<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface SettingRepositoryInterface
{
    /**
     * Get all settings as a flat associative key-value array.
     *
     * @return array
     */
    public function getAllKeyValue(): array;

    /**
     * Get value of a specific setting key.
     *
     * @param string $key
     * @return mixed
     */
    public function getByKey(string $key);

    /**
     * Set/update setting key value atomically.
     *
     * @param string $key
     * @param string|null $value
     * @param string $type
     * @return void
     */
    public function setKeyValue(string $key, ?string $value, string $type): void;
}
