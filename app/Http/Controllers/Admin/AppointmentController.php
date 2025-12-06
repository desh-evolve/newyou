<?php
// app/Http/Controllers/Admin/AppointmentController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\TimeSlot;
use App\Models\Client;
use App\Models\Package;
use App\Models\Service;
use App\Models\User;
use App\Services\AppointmentService;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    protected $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    /**
     * Display listing of appointments
     */
    public function index(Request $request)
    {
        $query = Appointment::with(['client.user', 'coach', 'package', 'service'])
            ->notDeleted()
            ->orderByDesc('appointment_date')
            ->orderByDesc('start_time');

        // Apply filters
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('coach_id')) {
            $query->forCoach($request->coach_id);
        }

        if ($request->filled('date_from')) {
            $query->where('appointment_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('appointment_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('appointment_number', 'like', "%{$search}%")
                    ->orWhereHas('client.user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $appointments = $query->paginate(15);

        // Get filter options
        // Correct query for your many-to-many relationship structure
        $coaches = User::whereHas('roles', function($query) {
                $query->where('name', 'coach');
            })
            ->where('status', 'active')
            ->get();
        $stats = $this->appointmentService->getDashboardStats();

        return view('admin.appointments.index', compact('appointments', 'coaches', 'stats'));
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
        
        return view('admin.appointments.calendar', compact('coaches'));
    }

    /**
     * Get appointments for calendar (AJAX)
     */
    public function calendarEvents(Request $request)
    {
        $startDate = $request->start;
        $endDate = $request->end;
        $coachId = $request->coach_id;

        $appointments = $this->appointmentService->getAppointmentsForCalendar(
            $startDate, 
            $endDate, 
            $coachId
        );

        return response()->json($appointments);
    }

    /**
     * Show create form
     */
    public function create(Request $request)
    {
        $coaches = User::whereHas('roles', function($query) {
                $query->where('name', 'coach');
            })
            ->where('status', 'active')
            ->get();
        $clients = Client::with('user')->active()->get();
        $packages = Package::where('status', 'active')->get();
        $services = Service::where('status', 'active')->get();

        $selectedSlotId = $request->slot_id;
        $selectedSlot = $selectedSlotId ? TimeSlot::find($selectedSlotId) : null;

        return view('admin.appointments.create', compact(
            'coaches', 'clients', 'packages', 'services', 'selectedSlot'
        ));
    }

    /**
     * Store new appointment
     */
    public function store(StoreAppointmentRequest $request)
    {
        try {
            $data = $request->validated();
            $data['client_user_id'] = $request->client_user_id;

            $appointment = $this->appointmentService->createAppointment($data);

            return redirect()
                ->route('admin.appointments.show', $appointment)
                ->with('success', 'Appointment created successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show appointment details
     */
    public function show(Appointment $appointment)
    {
        $appointment->load(['client.user', 'coach', 'package', 'service', 'timeSlot', 'notes.coach']);

        return view('admin.appointments.show', compact('appointment'));
    }

    /**
     * Show edit form
     */
    public function edit(Appointment $appointment)
    {
        $coaches = User::whereHas('roles', function($query) {
                $query->where('name', 'coach');
            })
            ->where('status', 'active')
            ->get();
        $packages = Package::where('status', 'active')->get();
        $services = Service::where('status', 'active')->get();

        return view('admin.appointments.edit', compact('appointment', 'coaches', 'packages', 'services'));
    }

    /**
     * Update appointment
     */
    public function update(UpdateAppointmentRequest $request, Appointment $appointment)
    {
        try {
            $appointment->update($request->validated());

            return redirect()
                ->route('admin.appointments.show', $appointment)
                ->with('success', 'Appointment updated successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Confirm appointment
     */
    public function confirm(Appointment $appointment)
    {
        try {
            $this->appointmentService->confirmAppointment($appointment);

            return back()->with('success', 'Appointment confirmed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel appointment
     */
    public function cancel(Request $request, Appointment $appointment)
    {
        $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        try {
            $this->appointmentService->cancelAppointment($appointment, $request->cancellation_reason);

            return back()->with('success', 'Appointment cancelled successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Complete appointment
     */
    public function complete(Appointment $appointment)
    {
        try {
            $this->appointmentService->completeAppointment($appointment);

            return back()->with('success', 'Appointment marked as completed.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Mark as no show
     */
    public function noShow(Appointment $appointment)
    {
        try {
            $appointment->markNoShow();

            return back()->with('success', 'Appointment marked as no-show.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Start appointment
     */
    public function start(Appointment $appointment)
    {
        try {
            $appointment->start();

            return back()->with('success', 'Appointment started.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete appointment (soft delete)
     */
    public function destroy(Appointment $appointment)
    {
        try {
            $appointment->softDelete();

            return redirect()
                ->route('admin.appointments.index')
                ->with('success', 'Appointment deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get available slots for a coach on a date (AJAX)
     */
    public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'coach_id' => 'required|exists:users,id',
            'date' => 'required|date',
        ]);

        $slots = TimeSlot::forCoach($request->coach_id)
            ->forDate($request->date)
            ->available()
            ->active()
            ->orderBy('start_time')
            ->get()
            ->map(function ($slot) {
                return [
                    'id' => $slot->id,
                    'time' => $slot->formatted_time,
                    'duration' => $slot->duration_minutes,
                ];
            });

        return response()->json($slots);
    }

    /**
     * Export appointments
     */
    public function export(Request $request)
    {
        $query = Appointment::with(['client.user', 'coach', 'package', 'service'])
            ->notDeleted();

        if ($request->filled('date_from')) {
            $query->where('appointment_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('appointment_date', '<=', $request->date_to);
        }

        $appointments = $query->get();

        $filename = 'appointments_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($appointments) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'Appointment #',
                'Date',
                'Time',
                'Client Name',
                'Client Email',
                'Coach',
                'Package/Service',
                'Type',
                'Status',
                'Payment Status',
                'Amount',
            ]);

            foreach ($appointments as $appointment) {
                fputcsv($file, [
                    $appointment->appointment_number,
                    $appointment->formatted_date,
                    $appointment->formatted_time,
                    $appointment->client->full_name,
                    $appointment->client->email,
                    $appointment->coach->name,
                    $appointment->package->name ?? $appointment->service->name ?? 'N/A',
                    ucfirst(str_replace('_', ' ', $appointment->appointment_type)),
                    ucfirst(str_replace('_', ' ', $appointment->appointment_status)),
                    ucfirst($appointment->payment_status),
                    '$' . number_format($appointment->final_amount, 2),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}