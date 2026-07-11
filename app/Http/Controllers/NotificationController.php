<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\NotificationRepositoryInterface;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;
    protected NotificationRepositoryInterface $notificationRepository;

    public function __construct(
        NotificationService $notificationService,
        NotificationRepositoryInterface $notificationRepository
    ) {
        $this->notificationService = $notificationService;
        $this->notificationRepository = $notificationRepository;
    }

    /**
     * Get list of notifications.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['is_read', 'type']);
        
        // Handle boolean parsing for filters
        if (isset($filters['is_read'])) {
            $filters['is_read'] = filter_var($filters['is_read'], FILTER_VALIDATE_BOOLEAN);
        }

        $notifications = $this->notificationRepository->getLatest($filters);

        return response()->json($notifications);
    }

    /**
     * Mark notification as read.
     */
    public function markRead(int $id): JsonResponse
    {
        $notification = $this->notificationService->markAsRead($id);

        if (!$notification) {
            return response()->json(['message' => 'Bildirishnoma topilmadi.'], 404);
        }

        return response()->json([
            'message' => 'Bildirishnoma o\'qildi deb belgilandi.',
            'notification' => $notification
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(): JsonResponse
    {
        $this->notificationService->markAllAsRead();

        return response()->json([
            'message' => 'Barcha bildirishnomalar o\'qildi deb belgilandi.'
        ]);
    }

    /**
     * Delete notification.
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->notificationRepository->delete($id);

        if (!$deleted) {
            return response()->json(['message' => 'Bildirishnoma topilmadi.'], 404);
        }

        return response()->json([
            'message' => 'Bildirishnoma o\'chirildi.'
        ]);
    }
}
