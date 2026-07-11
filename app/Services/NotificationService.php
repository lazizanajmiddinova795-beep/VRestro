<?php

namespace App\Services;

use App\Repositories\Contracts\NotificationRepositoryInterface;
use App\Jobs\ProcessNotificationJob;
use App\Models\SystemNotification;

class NotificationService
{
    protected NotificationRepositoryInterface $notificationRepository;

    public function __construct(NotificationRepositoryInterface $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    /**
     * Send system notification (dispatches async job to Redis Queue).
     *
     * @param string $type
     * @param string $title
     * @param string $message
     * @param array $meta
     * @return void
     */
    public function sendNotification(string $type, string $title, string $message, array $meta = []): void
    {
        ProcessNotificationJob::dispatch([
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'meta_data' => $meta
        ]);
    }

    /**
     * Mark single notification as read.
     *
     * @param int $id
     * @return SystemNotification|null
     */
    public function markAsRead(int $id): ?SystemNotification
    {
        return $this->notificationRepository->markAsRead($id);
    }

    /**
     * Mark all notifications as read.
     *
     * @return void
     */
    public function markAllAsRead(): void
    {
        $this->notificationRepository->markAllAsRead();
    }
}
