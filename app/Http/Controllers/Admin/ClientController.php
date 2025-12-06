<?php
// app/Http/Controllers/Admin/ClientController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    /**
     * Display listing of clients
     */
    public function index(Request $request)
    {
        $query = Client::with(['user', 'appointments'])
            ->notDeleted();

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('phone', 'like', "%{$search}%");
        }

        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        $clients = $query->orderByDesc('created_at')->paginate(15);

        // Get stats
        $totalClients = Client::notDeleted()->count();
        $activeClients = Client::active()->count();
        $newThisMonth = Client::notDeleted()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return view('admin.clients.index', compact('clients', 'totalClients', 'activeClients', 'newThisMonth'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('admin.clients.create');
    }

    /**
     * Store new client
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'alternate_phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other,prefer_not_to_say',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'timezone' => 'nullable|string|max:50',
            'preferred_communication' => 'nullable|in:email,phone,sms',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'goals' => 'nullable|string',
            'health_notes' => 'nullable|string',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'nullable|in:active,inactive',
        ]);

        try {
            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'status' => 'active',
            ]);

            // Assign client role if using roles
            // $user->assignRole('client');

            // Handle profile image upload
            $profileImagePath = null;
            if ($request->hasFile('profile_image')) {
                $profileImagePath = $request->file('profile_image')->store('clients/profile', 'public');
            }

            // Create client profile
            $client = Client::create([
                'user_id' => $user->id,
                'phone' => $request->phone,
                'alternate_phone' => $request->alternate_phone,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'country' => $request->country,
                'postal_code' => $request->postal_code,
                'timezone' => $request->timezone ?? 'UTC',
                'preferred_communication' => $request->preferred_communication ?? 'email',
                'emergency_contact_name' => $request->emergency_contact_name,
                'emergency_contact_phone' => $request->emergency_contact_phone,
                'goals' => $request->goals,
                'health_notes' => $request->health_notes,
                'profile_image' => $profileImagePath,
                'status' => $request->has('status') ? 'active' : 'inactive',
            ]);

            return redirect()
                ->route('admin.clients.show', $client)
                ->with('success', 'Client created successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to create client: ' . $e->getMessage());
        }
    }

    /**
     * Show client details
     */
    public function show(Client $client)
    {
        $client->load(['user', 'appointments.coach', 'appointments.package', 'appointments.service']);

        $upcomingAppointments = $client->getUpcomingAppointments();
        $totalAppointments = $client->getTotalAppointments();
        $completedAppointments = $client->getCompletedAppointments();

        return view('admin.clients.show', compact(
            'client',
            'upcomingAppointments',
            'totalAppointments',
            'completedAppointments'
        ));
    }

    /**
     * Show edit form
     */
    public function edit(Client $client)
    {
        return view('admin.clients.edit', compact('client'));
    }

    /**
     * Update client
     */
    public function update(Request $request, Client $client)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $client->user_id,
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other,prefer_not_to_say',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'timezone' => 'nullable|string|max:50',
            'health_notes' => 'nullable|string',
            'goals' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        try {
            // Update user
            $client->user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            // Update client profile
            $client->update($request->only([
                'phone',
                'date_of_birth',
                'gender',
                'address',
                'city',
                'state',
                'country',
                'postal_code',
                'timezone',
                'health_notes',
                'goals',
                'status',
            ]));

            return redirect()
                ->route('admin.clients.show', $client)
                ->with('success', 'Client updated successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Delete client (soft delete)
     */
    public function destroy(Client $client)
    {
        try {
            $client->softDelete();

            return redirect()
                ->route('admin.clients.index')
                ->with('success', 'Client deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get client appointments (AJAX)
     */
    public function appointments(Client $client)
    {
        $appointments = $client->appointments()
            ->with(['coach', 'package', 'service'])
            ->notDeleted()
            ->orderByDesc('appointment_date')
            ->get();

        return response()->json($appointments);
    }
}