<?php
// app/Http/Controllers/Admin/DashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\TimeSlot;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display admin dashboard
     */
    public function index()
    {
        // Appointment Stats
        $appointmentStats = [
            'today' => Appointment::notDeleted()->today()->count(),
            'this_week' => Appointment::notDeleted()->thisWeek()->count(),
            'this_month' => Appointment::notDeleted()->thisMonth()->count(),
            'pending' => Appointment::notDeleted()->pending()->count(),
            'confirmed' => Appointment::notDeleted()->byStatus('confirmed')->count(),
            'completed' => Appointment::notDeleted()->byStatus('completed')->count(),
            'cancelled' => Appointment::notDeleted()->byStatus('cancelled')->count(),
            'total_revenue' => Appointment::notDeleted()->paid()->sum('final_amount'),
        ];

        // Client Stats
        $clientStats = [
            'total' => Client::notDeleted()->count(),
            'active' => Client::active()->count(),
            'new_this_month' => Client::notDeleted()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        // Today's Appointments
        $todaysAppointments = Appointment::with(['client.user', 'coach'])
            ->notDeleted()
            ->today()
            ->orderBy('start_time')
            ->take(10)
            ->get();

        // Upcoming Appointments
        $upcomingAppointments = Appointment::with(['client.user', 'coach'])
            ->notDeleted()
            ->upcoming()
            ->take(5)
            ->get();

        // Available Slots Today
        $availableSlotsToday = TimeSlot::where('slot_date', Carbon::today())
            ->available()
            ->active()
            ->count();

        // Recent Clients
        $recentClients = Client::with('user')
            ->notDeleted()
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'appointmentStats',
            'clientStats',
            'todaysAppointments',
            'upcomingAppointments',
            'availableSlotsToday',
            'recentClients'
        ));
    }
}