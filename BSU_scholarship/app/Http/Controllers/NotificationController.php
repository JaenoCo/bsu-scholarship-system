<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Mark a notification as read.
     */
    public function markAsRead($id)
    {
        if (!session()->has('user_id')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $notification = Notification::where('id', $id)
            ->where('user_id', session('user_id'))
            ->first();

        if (!$notification) {
            return response()->json(['error' => 'Notification not found'], 404);
        }

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Mark a notification as unread.
     */
    public function markAsUnread($id)
    {
        if (!session()->has('user_id')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $notification = Notification::where('id', $id)
            ->where('user_id', session('user_id'))
            ->first();

        if (!$notification) {
            return response()->json(['error' => 'Notification not found'], 404);
        }

        $notification->update([
            'is_read' => false,
            'read_at' => null
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        if (!session()->has('user_id')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        Notification::where('user_id', session('user_id'))
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return response()->json(['success' => true]);
    }

    /**
     * Delete a notification.
     */
    public function destroy($id)
    {
        if (!session()->has('user_id')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $notification = Notification::where('id', $id)
            ->where('user_id', session('user_id'))
            ->first();

        if (!$notification) {
            return response()->json(['error' => 'Notification not found'], 404);
        }

        $notification->delete();

        return response()->json(['success' => true]);
    }
}
