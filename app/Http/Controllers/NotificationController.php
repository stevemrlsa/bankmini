<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Mengambil daftar notifikasi terbaru via AJAX
     */
    public function fetchNotifications(): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'unread_count'  => 0,
                'notifications' => [],
            ]);
        }

        $data = $this->notificationService->getNotificationsForUser($user);

        return response()->json([
            'unread_count'  => $data['unread_count'],
            'notifications' => $data['notifications'],
        ]);
    }

    /**
     * Menandai semua notifikasi sebagai telah dibaca
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = Auth::user();
        if ($user) {
            $this->notificationService->markAllAsRead($user);
        }

        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi telah ditandai sebagai dibaca.',
        ]);
    }
}

