<?php
// app/Http/Controllers/Admin/TimeSlotController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TimeSlot;
use App\Models\User;
use App\Services\TimeSlotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TimeSlotController extends Controller
{
    protected $timeSlotService;

    public function __construct(TimeSlotService $timeSlotService)
    {
        $this->timeSlotService = $timeSlotService;
    }

    /**
     * Display listing of time slots
     */
    public function index(Request $request)
    {
        $query = TimeSlot::with(['coach', 'appointment.client.user'])
            ->notDeleted()
            ->orderBy('slot_date')
            ->orderBy('start_time');

        // Apply filters
        if ($request->filled('coach_id')) {
            $query->forCoach($request->coach_id);
        }

        if ($request->filled('status')) {
            $query->where('slot_status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('slot_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('slot_date', '<=', $request->date_to);
        }

        // Default to upcoming slots
        if (!$request->filled('show_past')) {
            $query->where('slot_date', '>=', now()->toDateString());
        }

        $timeSlots = $query->paginate(20);
        $coaches = User::whereHas('roles', function($query) {
                $query->where('name', 'coach');
            })
            ->where('status', 'active')
            ->get();

        return view('admin.time-slots.index', compact('timeSlots', 'coaches'));
    }

    /**
     * Show calendar view
     */
    public function calendar(Request $request)
    {
        $coaches = User::whereHas('roles', function($query) {
                $query->where('name', 'coach');
            })
            ->where('status', 'active')
            ->get();
        $selectedCoach = $request->coach_id;

        return view('admin.time-slots.calendar', compact('coaches', 'selectedCoach'));
    }

    /**
     * Get slots for calendar (AJAX)
     */
    public function calendarEvents(Request $request)
    {
        // Debug: Log the request
        \Log::info('Calendar Events Request:', $request->all());
        
        $request->validate([
            'coach_id' => 'required|exists:users,id',
            'start' => 'required|date',
            'end' => 'required|date',
        ]);

        $coachId = $request->coach_id;
        $startDate = $request->start;
        $endDate = $request->end;

        // Get slots directly without service for debugging
        $slots = \App\Models\TimeSlot::where('coach_id', $coachId)
            ->whereBetween('slot_date', [$startDate, $endDate])
            ->where('status', '!=', 'delete')
            ->orderBy('slot_date')
            ->orderBy('start_time')
            ->get();

        // Debug: Log the slots count
        \Log::info('Slots found: ' . $slots->count());

        $events = $slots->map(function ($slot) {
            return [
                'id' => $slot->id,
                'title' => $this->getSlotTitle($slot->slot_status),
                'start' => $slot->slot_date->format('Y-m-d') . 'T' . $slot->start_time,
                'end' => $slot->slot_date->format('Y-m-d') . 'T' . $slot->end_time,
                'color' => $this->getSlotColor($slot->slot_status),
                'extendedProps' => [
                    'status' => $slot->slot_status,
                    'duration' => (int) $slot->duration_minutes,
                    'coach_id' => $slot->coach_id,
                ],
            ];
        });

        // Debug: Log the events
        \Log::info('Events:', $events->toArray());

        return response()->json($events);
    }

    /**
     * Get slot title based on status
     */
    private function getSlotTitle($status)
    {
        $titles = [
            'available' => 'Available',
            'locked' => 'Locked',
            'booked' => 'Booked',
            'blocked' => 'Blocked',
        ];

        return $titles[$status] ?? 'Unknown';
    }

    /**
     * Get slot color based on status
     */
    private function getSlotColor($status)
    {
        $colors = [
            'available' => '#28a745',
            'locked' => '#ffc107',
            'booked' => '#17a2b8',
            'blocked' => '#dc3545',
        ];

        return $colors[$status] ?? '#6c757d';
    }

    /**
     * Show create form
     */
    public function create()
    {
        $coaches = User::whereHas('roles', function($query) {
                $query->where('name', 'coach');
            })
            ->where('status', 'active')
            ->get();

        return view('admin.time-slots.create', compact('coaches'));
    }

    /**
     * Generate time slots
     */
    public function store(Request $request)
    {
        $request->validate([
            'coach_id' => 'required|exists:users,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'duration' => 'required|integer|min:15|max:480',  // Ensure integer validation
            'days' => 'required|array|min:1',
            'days.*' => 'integer|between:0,6',
        ]);

        try {
            // Convert duration to integer explicitly
            $data = $request->all();
            $data['duration'] = (int) $request->duration;
            
            $slots = $this->timeSlotService->generateSlots($data);

            return redirect()
                ->route('admin.time-slots.index')
                ->with('success', count($slots) . ' time slots generated successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show single time slot
     */
    public function show(TimeSlot $timeSlot)
    {
        $timeSlot->load(['coach', 'appointment.client.user']);

        return view('admin.time-slots.show', compact('timeSlot'));
    }

    /**
     * Edit time slot
     */
    public function edit(TimeSlot $timeSlot)
    {
        if ($timeSlot->slot_status === 'booked') {
            return back()->with('error', 'Cannot edit a booked time slot.');
        }

        return view('admin.time-slots.edit', compact('timeSlot'));
    }

    /**
     * Update time slot
     */
    public function update(Request $request, TimeSlot $timeSlot)
    {
        if ($timeSlot->slot_status === 'booked') {
            return back()->with('error', 'Cannot update a booked time slot.');
        }

        $request->validate([
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            // Calculate duration as integer
            $startTime = Carbon::parse($request->start_time);
            $endTime = Carbon::parse($request->end_time);
            $durationMinutes = (int) $startTime->diffInMinutes($endTime);

            $timeSlot->update([
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'duration_minutes' => $durationMinutes,  // Integer value
                'notes' => $request->notes,
            ]);

            return redirect()
                ->route('admin.time-slots.index')
                ->with('success', 'Time slot updated successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Block a time slot
     */
    public function block(Request $request, TimeSlot $timeSlot)
    {
        try {
            $this->timeSlotService->blockSlot($timeSlot, $request->reason);

            return back()->with('success', 'Time slot blocked successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Unblock a time slot
     */
    public function unblock(TimeSlot $timeSlot)
    {
        try {
            $this->timeSlotService->unblockSlot($timeSlot);

            return back()->with('success', 'Time slot unblocked successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Unlock a locked time slot
     */
    public function unlock(TimeSlot $timeSlot)
    {
        try {
            if ($timeSlot->slot_status !== 'locked') {
                throw new \Exception('Only locked slots can be unlocked.');
            }

            $timeSlot->unlock();

            return back()->with('success', 'Time slot unlocked successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete time slot
     */
    public function destroy(TimeSlot $timeSlot)
    {
        if ($timeSlot->slot_status === 'booked') {
            return back()->with('error', 'Cannot delete a booked time slot.');
        }

        try {
            $timeSlot->softDelete();

            return back()->with('success', 'Time slot deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Bulk delete time slots
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'slot_ids' => 'required|array|min:1',
            'slot_ids.*' => 'exists:time_slots,id',
        ]);

        try {
            $deleted = $this->timeSlotService->bulkDeleteSlots($request->slot_ids);

            return back()->with('success', "$deleted time slots deleted successfully.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Quick create single slot (AJAX)
     */
    public function quickCreate(Request $request)
    {
        $request->validate([
            'coach_id' => 'required|exists:users,id',
            'slot_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        try {
            // Check if slot already exists
            $exists = TimeSlot::where('coach_id', $request->coach_id)
                ->where('slot_date', $request->slot_date)
                ->where('start_time', $request->start_time)
                ->notDeleted()
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'This time slot already exists.',
                ], 422);
            }

            // Calculate duration as integer
            $startTime = Carbon::parse($request->start_time);
            $endTime = Carbon::parse($request->end_time);
            $durationMinutes = (int) $startTime->diffInMinutes($endTime);

            $slot = TimeSlot::create([
                'coach_id' => $request->coach_id,
                'slot_date' => $request->slot_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'duration_minutes' => $durationMinutes,  // Integer value
                'slot_status' => 'available',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Time slot created successfully.',
                'slot' => $slot,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}