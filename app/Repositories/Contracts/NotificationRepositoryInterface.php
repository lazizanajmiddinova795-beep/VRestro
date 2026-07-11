<?php

namespace App\Repositories\Contracts;

use App\Models\SystemNotification;
use Illuminate\Support\Collection;

interface NotificationRepositoryInterface
{
    /**
     * Get latest notifications.
     *
     * @param array $filters
     * @return Collection
     */
    public function getLatest(array $filters): Collection;

    /**
     * Mark a single notification as read.
     *
     * @param int $id
     * @return SystemNotification|null
     */
    public function markAsRead(int $id): ?SystemNotification;

    /**
     * Mark all notifications as read.
     *
     * @return void
     */
    public function markAllAsRead(): void;

    /**
     * Delete a single notification.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
}
