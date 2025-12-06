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

        if ($request->filled('type')) {
            $query->byType($request->type);
        }

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
        $notifications = AppointmentNotification::with(['appointment'])
            ->forUser(Auth::id())
            ->notDeleted()
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        $unreadCount = $this->notificationService->getUnreadCount(Auth::id());

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(AppointmentNotification $notification)
    {
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