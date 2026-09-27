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
     * Open a notification's intended destination and mark it read in the same
     * request. The notification is always scoped to the signed-in user.
     */
    public function open($id)
    {
        if (! session()->has('user_id')) {
            return redirect()->route('login');
        }

        $notification = Notification::where('id', $id)
            ->where('user_id', session('user_id'))
            ->firstOrFail();

        if (! $notification->is_read) {
            $notification->markAsRead();
        }

        return redirect()->to($this->destinationFor($notification));
    }

    protected function destinationFor(Notification $notification): string
    {
        $data = $notification->data ?: [];
        $requestedUrl = $data['redirect_url'] ?? $data['url'] ?? null;

        // Notifications can point to a specific in-app path, but never to an
        // external site or protocol-relative URL.
        if (is_string($requestedUrl) && str_starts_with($requestedUrl, '/') && ! str_starts_with($requestedUrl, '//')) {
            return $requestedUrl;
        }

        return match (session('role')) {
            'student' => match ($notification->type) {
                'scholarship_created' => route('student.dashboard', ['tab' => 'all_scholarships']),
                'application_status', 'sfao_comment' => route('student.dashboard', ['tab' => 'application_tracking']),
                default => route('student.dashboard', ['tab' => 'all_notifications']),
            },
            'sfao' => route('sfao.dashboard', ['tabs' => 'applicants']),
            'central' => route('central.dashboard', ['tabs' => 'dashboard']),
            default => route('login'),
        };
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
