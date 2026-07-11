<?php

namespace App\Repositories;

use App\Models\SystemNotification;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NotificationRepository implements NotificationRepositoryInterface
{
    /**
     * Get latest notifications.
     */
    public function getLatest(array $filters): Collection
    {
        $query = SystemNotification::query();

        if (isset($filters['is_read'])) {
            $query->where('is_read', $filters['is_read']);
        }

        if (isset($filters['type']) && !empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query->orderByDesc('created_at')->get();
    }

    /**
     * Mark a single notification as read inside a database transaction.
     */
    public function markAsRead(int $id): ?SystemNotification
    {
        return DB::transaction(function () use ($id) {
            $notification = SystemNotification::lockForUpdate()->find($id);
            if ($notification) {
                $notification->update(['is_read' => true]);
            }
            return $notification;
        });
    }

    /**
     * Mark all notifications as read inside a database transaction.
     */
    public function markAllAsRead(): void
    {
        DB::transaction(function () {
            SystemNotification::where('is_read', false)->update(['is_read' => true]);
        });
    }

    /**
     * Delete a single notification.
     */
    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $notification = SystemNotification::find($id);
            if ($notification) {
                return $notification->delete();
            }
            return false;
        });
    }
}
