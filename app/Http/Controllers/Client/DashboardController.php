<?php
// app/Http/Controllers/Client/DashboardController.php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display client dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $client = Client::where('user_id', $user->id)->first();

        $data = [
            'user' => $user,
            'client' => $client,
            'upcomingAppointments' => collect(),
            'recentAppointments' => collect(),
            'stats' => [
                'total' => 0,
                'upcoming' => 0,
                'completed' => 0,
                'cancelled' => 0,
            ],
        ];

        if ($client) {
            $data['upcomingAppointments'] = Appointment::with(['coach', 'package', 'service'])
                ->forClient($client->id)
                ->notDeleted()
                ->upcoming()
                ->take(5)
                ->get();

            $data['recentAppointments'] = Appointment::with(['coach', 'package', 'service'])
                ->forClient($client->id)
                ->notDeleted()
                ->past()
                ->take(5)
                ->get();

            $data['stats'] = [
                'total' => Appointment::forClient($client->id)->notDeleted()->count(),
                'upcoming' => Appointment::forClient($client->id)->notDeleted()->upcoming()->count(),
                'completed' => Appointment::forClient($client->id)->notDeleted()->byStatus('completed')->count(),
                'cancelled' => Appointment::forClient($client->id)->notDeleted()->byStatus('cancelled')->count(),
            ];
        }

        return view('client.dashboard', $data);
    }
}