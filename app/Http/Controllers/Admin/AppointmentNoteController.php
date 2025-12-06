<?php
// app/Http/Controllers/Admin/AppointmentNoteController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentNoteController extends Controller
{
    /**
     * Display notes for an appointment
     */
    public function index(Appointment $appointment)
    {
        $notes = $appointment->notes()
            ->with('coach')
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.appointments.notes.index', compact('appointment', 'notes'));
    }

    /**
     * Store a new note
     */
    public function store(Request $request, Appointment $appointment)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'note_content' => 'required|string',
            'note_type' => 'required|in:general,progress,goal,action_item,follow_up,private',
            'visibility' => 'required|in:coach_only,admin_coach,all',
            'is_pinned' => 'boolean',
        ]);

        try {
            $note = AppointmentNote::create([
                'appointment_id' => $appointment->id,
                'coach_id' => Auth::id(),
                'title' => $request->title,
                'note_content' => $request->note_content,
                'note_type' => $request->note_type,
                'visibility' => $request->visibility,
                'is_pinned' => $request->is_pinned ?? false,
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Note added successfully.',
                    'note' => $note->load('coach'),
                ]);
            }

            return back()->with('success', 'Note added successfully.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show edit form
     */
    public function edit(Appointment $appointment, AppointmentNote $note)
    {
        return view('admin.appointments.notes.edit', compact('appointment', 'note'));
    }

    /**
     * Update a note
     */
    public function update(Request $request, Appointment $appointment, AppointmentNote $note)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'note_content' => 'required|string',
            'note_type' => 'required|in:general,progress,goal,action_item,follow_up,private',
            'visibility' => 'required|in:coach_only,admin_coach,all',
        ]);

        try {
            $note->update($request->only(['title', 'note_content', 'note_type', 'visibility']));

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Note updated successfully.',
                    'note' => $note->fresh()->load('coach'),
                ]);
            }

            return redirect()
                ->route('admin.appointments.notes.index', $appointment)
                ->with('success', 'Note updated successfully.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Toggle pin status
     */
    public function togglePin(Appointment $appointment, AppointmentNote $note)
    {
        try {
            $note->togglePin();

            return response()->json([
                'success' => true,
                'message' => $note->is_pinned ? 'Note pinned.' : 'Note unpinned.',
                'is_pinned' => $note->is_pinned,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a note
     */
    public function destroy(Request $request, Appointment $appointment, AppointmentNote $note)
    {
        try {
            $note->softDelete();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Note deleted successfully.',
                ]);
            }

            return back()->with('success', 'Note deleted successfully.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }

            return back()->with('error', $e->getMessage());
        }
    }
}