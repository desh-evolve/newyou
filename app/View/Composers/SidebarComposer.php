<?php
// app/View/Composers/SidebarComposer.php

namespace App\View\Composers;

use App\Models\Appointment;
use App\Models\AppointmentNotification;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class SidebarComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        $userId = Auth::id();
        
        // Cache counts for 5 minutes to reduce database queries
        $sidebarData = Cache::remember("sidebar_counts_{$userId}", 300, function () use ($userId) {
            return [
                'pending_appointments' => Appointment::notDeleted()->pending()->count(),
                'today_appointments' => Appointment::notDeleted()->today()->count(),
                'unread_notifications' => AppointmentNotification::forUser($userId)
                    ->notDeleted()
                    ->unread()
                    ->count(),
            ];
        });

        $view->with('sidebarData', $sidebarData);
    }
}