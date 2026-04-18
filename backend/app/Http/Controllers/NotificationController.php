<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->limit(20)
            ->get()
            ->map(function ($notification): array {
                $data = $notification->data ?? [];

                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'readAt' => optional($notification->read_at)->toDateTimeString(),
                    'createdAt' => optional($notification->created_at)->toDateTimeString(),
                    'title' => $data['title'] ?? 'System notification',
                    'message' => $data['message'] ?? 'You have a new system notification.',
                    'game' => $data['game'] ?? null,
                    'store' => $data['store'] ?? null,
                    'price' => $data['price'] ?? null,
                    'previousPrice' => $data['previous_price'] ?? null,
                    'originalPrice' => $data['original_price'] ?? null,
                    'discountPercent' => $data['discount_percent'] ?? null,
                    'isOnSale' => $data['is_on_sale'] ?? null,
                    'changedAt' => $data['changed_at'] ?? null,
                ];
            })
            ->values();

        return response()->json([
            'notifications' => $notifications,
            'unreadCount' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read.',
        ]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'message' => 'All notifications marked as read.',
        ]);
    }
}
