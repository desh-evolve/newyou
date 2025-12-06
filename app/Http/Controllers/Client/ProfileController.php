<?php
// app/Http/Controllers/Client/ProfileController.php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Show profile edit form
     */
    public function edit()
    {
        $user = Auth::user();
        $client = Client::where('user_id', $user->id)->first();

        // Create client profile if doesn't exist
        if (!$client) {
            $client = Client::create([
                'user_id' => $user->id,
                'status' => 'active',
            ]);
        }

        return view('client.profile.edit', compact('user', 'client'));
    }

    /**
     * Update profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $client = Client::where('user_id', $user->id)->first();

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other,prefer_not_to_say',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'timezone' => 'nullable|string|max:50',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            // Update user name
            $user->update([
                'name' => $request->name,
            ]);

            // Handle profile image upload
            $profileImagePath = $client->profile_image;
            if ($request->hasFile('profile_image')) {
                // Delete old image
                if ($profileImagePath && Storage::disk('public')->exists($profileImagePath)) {
                    Storage::disk('public')->delete($profileImagePath);
                }
                $profileImagePath = $request->file('profile_image')->store('clients/profile', 'public');
            }

            // Update or create client profile
            if ($client) {
                $client->update([
                    'phone' => $request->phone,
                    'date_of_birth' => $request->date_of_birth,
                    'gender' => $request->gender,
                    'address' => $request->address,
                    'city' => $request->city,
                    'state' => $request->state,
                    'country' => $request->country,
                    'postal_code' => $request->postal_code,
                    'timezone' => $request->timezone ?? 'UTC',
                    'profile_image' => $profileImagePath,
                ]);
            } else {
                Client::create([
                    'user_id' => $user->id,
                    'phone' => $request->phone,
                    'date_of_birth' => $request->date_of_birth,
                    'gender' => $request->gender,
                    'address' => $request->address,
                    'city' => $request->city,
                    'state' => $request->state,
                    'country' => $request->country,
                    'postal_code' => $request->postal_code,
                    'timezone' => $request->timezone ?? 'UTC',
                    'profile_image' => $profileImagePath,
                    'status' => 'active',
                ]);
            }

            return back()->with('success', 'Profile updated successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update profile: ' . $e->getMessage());
        }
    }

    /**
     * Show profile creation form (for new clients)
     */
    public function create()
    {
        $user = Auth::user();
        $client = Client::where('user_id', $user->id)->first();

        if ($client) {
            return redirect()->route('client.profile.edit');
        }

        return view('client.profile.create', compact('user'));
    }

    /**
     * Store new profile
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'phone' => 'required|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other,prefer_not_to_say',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'timezone' => 'nullable|string|max:50',
        ]);

        try {
            Client::create([
                'user_id' => $user->id,
                'phone' => $request->phone,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'country' => $request->country,
                'postal_code' => $request->postal_code,
                'timezone' => $request->timezone ?? 'UTC',
                'status' => 'active',
            ]);

            return redirect()
                ->route('client.dashboard')
                ->with('success', 'Profile created successfully. You can now book appointments.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to create profile: ' . $e->getMessage());
        }
    }
}