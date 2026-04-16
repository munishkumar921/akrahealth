<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class NotificationController extends Controller
{
    private function transformNotification($notification): array
    {
        $data = is_string($notification->data)
            ? json_decode($notification->data, true)
            : ($notification->data ?? []);

        if (! is_array($data)) {
            $data = [];
        }

        return [
            'id' => $notification->id,
            'type' => $notification->type,
            'data' => $data,
            'title' => $data['title'] ?? 'Notification',
            'message' => $data['message'] ?? null,
            'action_url' => $data['action_url'] ?? null,
            'appointment_id' => $data['appointment_id'] ?? null,
            'order_id' => $data['order_id'] ?? null,
            'invoice_id' => $data['invoice_id'] ?? null,
            'encounter_id' => $data['encounter_id'] ?? null,
            'status' => $data['status'] ?? null,
            'notification_type' => $data['type'] ?? null,
            'channel' => $data['channel'] ?? 'in_app',
            'is_read' => filled($notification->read_at),
            'read_at' => $notification->read_at?->toISOString(),
            'created_at' => $notification->created_at->toISOString(),
            'created_at_human' => $notification->created_at->diffForHumans(),
            'notifiable_type' => $notification->notifiable_type,
            'notifiable_id' => $notification->notifiable_id,
        ];
    }

    /**
     * Fetch the unread notifications for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $readStatus = $request->get('read_status', 'all');

        // Build query ONLY for this user's notifications
        $query = $user->notifications()->latest();

        // Filter by read status
        if ($readStatus === 'read') {
            $query->whereNotNull('read_at');
        } elseif ($readStatus === 'unread') {
            $query->whereNull('read_at');
        }

        // Fetch notifications
        $notifications = $query->paginate(paginateLimit(20))
            ->through(fn ($notification) => $this->transformNotification($notification));

        return Inertia::render('Common/Notifications', [
            'filters' => $request->only(['read_status']),
            'notifications' => $notifications,
            'unread_count' => $user->unreadNotifications()->count(),
            'total_count' => $user->notifications()->count(),
        ]);

    }

    public function getNotifications(Request $request)
    {
        $user = Auth::user();

        // Get query parameters
        $limit = (int) $request->get('limit', 5);
        $readStatus = $request->get('read_status'); // all / read / unread

        // Build query ONLY for this user's notifications
        $query = $user->notifications()->latest();

        // Filter by read status
        if ($readStatus === 'read') {
            $query->whereNotNull('read_at');
        } elseif ($readStatus === 'unread') {
            $query->whereNull('read_at');
        }

        // Fetch notifications
        $notifications = $query->take($limit)->get()
            ->map(fn ($notification) => $this->transformNotification($notification));

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $user->unreadNotifications()->count(),
            'total_count' => $user->notifications()->count(),
        ]);

    }

    /**
     * Mark a specific notification as read.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead(Request $request, $id = null)
    {
        $user = Auth::user();
        $notification = $user->notifications()->find($id);

        if ($notification) {
            $notification->markAsRead();

            return response()->json(['message' => 'Notification marked as read.']);
        }

        return response()->json(['error' => 'Notification not found.'], 404);
    }

    /**
     * Mark all unread notifications as read.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllAsRead(Request $request)
    {
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();

        return response()->json(['message' => 'All notifications marked as read.']);
    }

    public function destroy($id)
    {
        $user = Auth::user();

        $notification = $user->notifications()->where('id', $id)->first();

        if (! $notification) {
            return response()->json(['error' => 'Notification not found'], 404);
        }

        $notification->delete();

        return response()->json(['message' => 'Notification deleted successfully']);
    }
}
