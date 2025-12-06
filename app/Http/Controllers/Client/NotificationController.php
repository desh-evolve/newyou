<?php
// app/Http/Controllers/Client/NotificationController.php

namespace App\Http\Controllers\Client;

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
    public function index()
    {
        $notifications = AppointmentNotification::with(['appointment'])
            ->forUser(Auth::id())
            ->notDeleted()
            ->orderByDesc('created_at')
            ->paginate(15);

        $unreadCount = $this->notificationService->getUnreadCount(Auth::id());

        return view('client.notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Get notifications dropdown (AJAX)
     */
    public function dropdown()
    {
        $notifications = AppointmentNotification::with(['appointment'])
            ->forUser(Auth::id())
            ->notDeleted()
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        $unreadCount = $this->notificationService->getUnreadCount(Auth::id());

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark as read
     */
    public function markAsRead(AppointmentNotification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Mark all as read
     */
    public function markAllAsRead()
    {
        $this->notificationService->markAllAsRead(Auth::id());

        return response()->json(['success' => true]);
    }
}