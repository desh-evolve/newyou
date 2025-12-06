<?php
// app/Http/Controllers/Admin/NotificationController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppointmentNotification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display all notifications
     */
    public function index(Request $request)
    {
        $query = AppointmentNotification::with(['appointment.client.user'])
            ->forUser(Auth::id())
            ->notDeleted()
            ->orderByDesc('created_at');

        // Filter by type
        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        // Filter by read status
        if ($request->filled('read')) {
            if ($request->read === 'unread') {
                $query->unread();
            } else {
                $query->read();
            }
        }

        $notifications = $query->paginate(20);
        $unreadCount = $this->notificationService->getUnreadCount(Auth::id());

        return view('admin.notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Get notifications for dropdown (AJAX)
     */
    public function dropdown()
    {
        $userId = Auth::id();
        
        \Log::info("Loading notifications for user: {$userId}");
        
        $notifications = AppointmentNotification::with(['appointment'])
            ->where('user_id', $userId)
            ->where('status', '!=', 'delete')
            ->orderByDesc('created_at')
            ->take(10)
            ->get();
        
        \Log::info("Found notifications: " . $notifications->count());
        
        $unreadCount = AppointmentNotification::where('user_id', $userId)
            ->where('status', '!=', 'delete')
            ->where('is_read', false)
            ->count();

        return response()->json([
            'notifications' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => \Str::limit($notification->message, 50),
                    'type' => $notification->type,
                    'type_icon' => $notification->type_icon,
                    'is_read' => $notification->is_read,
                    'time_ago' => $notification->time_ago,
                    'appointment_id' => $notification->appointment_id,
                ];
            }),
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(AppointmentNotification $notification)
    {
        // Check ownership
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.',
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $this->notificationService->markAllAsRead(Auth::id());

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read.',
        ]);
    }

    /**
     * Delete notification
     */
    public function destroy(AppointmentNotification $notification)
    {
        // Check ownership
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $notification->softDelete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted.',
        ]);
    }

    /**
     * Get unread count (AJAX)
     */
    public function unreadCount()
    {
        $count = $this->notificationService->getUnreadCount(Auth::id());

        return response()->json(['count' => $count]);
    }
}