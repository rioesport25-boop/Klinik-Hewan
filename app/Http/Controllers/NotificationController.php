<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get user's notifications
     */
    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->take(20)
            ->get()
            ->map(fn($notification) => [
                'id' => $notification->id,
                'type' => $notification->type,
                'title' => $notification->title,
                'message' => $notification->message,
                'data' => $notification->data,
                'is_read' => $notification->is_read,
                'created_at' => $notification->created_at->diffForHumans(),
            ]);

        return response()->json([
            'notifications' => $notifications,
        ]);
    }

    /**
     * Get unread notifications
     */
    public function getUnread()
    {
        $notifications = auth()->user()
            ->notifications()
            ->unread()
            ->latest()
            ->get();

        return response()->json($notifications);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Notification $notification)
    {
        // Check ownership
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->markAsRead();

        return response()->json([
            'message' => 'Notifikasi ditandai sebagai dibaca',
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        auth()->user()
            ->unreadNotifications()
            ->update(['is_read' => true]);

        return response()->json([
            'message' => 'Semua notifikasi ditandai sebagai dibaca',
        ]);
    }

    /**
     * Delete notification
     */
    public function destroy(Notification $notification)
    {
        // Check ownership
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->delete();

        return response()->json([
            'message' => 'Notifikasi berhasil dihapus',
        ]);
    }
}
