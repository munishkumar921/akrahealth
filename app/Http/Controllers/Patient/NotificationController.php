<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    /**
     * Get unread notifications for the authenticated user.
     */
    public function index()
    {
        // Returns the 'data' field from the notifications table automatically
        return response()->json(auth()->user()->unreadNotifications);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json(['message' => 'Notification marked as read']);
    }
}
