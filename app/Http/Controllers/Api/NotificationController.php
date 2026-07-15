<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Current user ki notifications (latest 20)
     */
    public function index(Request $request)
    {
        $notifications = Notification::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $notifications,
        ]);
    }

    /**
     * Unread count (bell badge ke liye)
     */
    public function unreadCount(Request $request)
    {
        $count = Notification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'success' => true,
            'data'    => ['count' => $count],
        ]);
    }

    /**
     * Ek notification read mark karein
     */
    public function markRead(Request $request, Notification $notification)
    {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Not authorized.',
            ], 403);
        }

        $notification->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Marked as read',
        ]);
    }

    /**
     * Saari notifications read mark karein
     */
    public function markAllRead(Request $request)
    {
        Notification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }
}