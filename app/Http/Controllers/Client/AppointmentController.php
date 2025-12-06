<?php
// app/Http/Controllers/Client/AppointmentController.php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\TimeSlot;
use App\Models\Package;
use App\Models\Service;
use App\Models\Client;
use App\Models\User;
use App\Services\AppointmentService;
use App\Services\TimeSlotService;
use App\Services\StripePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    protected $appointmentService;
    protected $timeSlotService;
    protected $stripeService;

    public function __construct(
        AppointmentService $appointmentService,
        TimeSlotService $timeSlotService,
        StripePaymentService $stripeService
    ) {
        $this->appointmentService = $appointmentService;
        $this->timeSlotService = $timeSlotService;
        $this->stripeService = $stripeService;
    }

    /**
     * Display client's appointments
     */
    public function index(Request $request)
    {
        $client = $this->getClientProfile();

        if (!$client) {
            return redirect()->route('client.profile.create')
                ->with('info', 'Please complete your profile first.');
        }

        $query = Appointment::with(['coach', 'package', 'service'])
            ->forClient($client->id)
            ->notDeleted();

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->get('view') === 'upcoming') {
            $query->upcoming();
        } elseif ($request->get('view') === 'past') {
            $query->past();
        }

        $appointments = $query->orderByDesc('appointment_date')
            ->orderByDesc('start_time')
            ->paginate(10);

        $upcomingCount = Appointment::forClient($client->id)->notDeleted()->upcoming()->count();
        $completedCount = Appointment::forClient($client->id)->notDeleted()->byStatus('completed')->count();

        return view('client.appointments.index', compact('appointments', 'upcomingCount', 'completedCount'));
    }

    /**
     * Show calendar view
     */
    public function calendar()
    {
        $client = $this->getClientProfile();

        if (!$client) {
            return redirect()->route('client.profile.create');
        }

        return view('client.appointments.calendar', compact('client'));
    }

    /**
     * Get calendar events (AJAX)
     */
    public function calendarEvents(Request $request)
    {
        $client = $this->getClientProfile();

        if (!$client) {
            return response()->json([]);
        }

        $appointments = $this->appointmentService->getAppointmentsForCalendar(
            $request->start,
            $request->end,
            null,
            $client->id
        );

        return response()->json($appointments);
    }

    /**
     * Show booking form
     */
    public function create(Request $request)
    {
        $coaches = User::role('coach')->where('status', 'active')->get();
        $packages = Package::where('status', 'active')->orderBy('sort_order')->get();
        $services = Service::where('status', 'active')->orderBy('sort_order')->get();

        $selectedCoach = $request->coach_id;
        $selectedDate = $request->date;

        return view('client.appointments.create', compact(
            'coaches',
            'packages',
            'services',
            'selectedCoach',
            'selectedDate'
        ));
    }

    /**
     * Get available slots (AJAX)
     */
    public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'coach_id' => 'required|exists:users,id',
            'date' => 'required|date|after_or_equal:today',
        ]);

        $slots = $this->timeSlotService->getAvailableSlots($request->coach_id, $request->date);

        return response()->json([
            'slots' => $slots->map(function ($slot) {
                return [
                    'id' => $slot->id,
                    'time' => $slot->formatted_time,
                    'duration' => $slot->duration_minutes,
                ];
            }),
        ]);
    }

    /**
     * Store new appointment
     */
    public function store(Request $request)
    {
        $request->validate([
            'time_slot_id' => 'required|exists:time_slots,id',
            'package_id' => 'nullable|exists:packages,id',
            'service_id' => 'nullable|exists:services,id',
            'appointment_type' => 'required|in:in_person,video_call,phone_call',
            'client_notes' => 'nullable|string|max:500',
        ]);

        // Ensure client profile exists
        $client = $this->getOrCreateClientProfile();

        try {
            $appointment = $this->appointmentService->createAppointment([
                'time_slot_id' => $request->time_slot_id,
                'client_user_id' => Auth::id(),
                'package_id' => $request->package_id,
                'service_id' => $request->service_id,
                'appointment_type' => $request->appointment_type,
                'client_notes' => $request->client_notes,
            ]);

            // Redirect to payment
            return redirect()->route('client.appointments.payment', $appointment);
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show payment page
     */
    public function payment(Appointment $appointment)
    {
        // Verify ownership
        if ($appointment->client->user_id !== Auth::id()) {
            abort(403);
        }

        if ($appointment->payment_status === 'paid') {
            return redirect()->route('client.appointments.show', $appointment)
                ->with('info', 'This appointment has already been paid.');
        }

        // Create payment intent
        $paymentIntent = $this->stripeService->createPaymentIntent($appointment);

        return view('client.appointments.payment', compact('appointment', 'paymentIntent'));
    }

    /**
     * Process payment
     */
    public function processPayment(Request $request, Appointment $appointment)
    {
        // Verify ownership
        if ($appointment->client->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'payment_method' => 'required|string',
        ]);

        try {
            $result = $this->appointmentService->processPayment(
                $appointment,
                $request->payment_method
            );

            if ($result['success']) {
                return redirect()
                    ->route('client.appointments.show', $appointment)
                    ->with('success', 'Payment successful! Your appointment is confirmed.');
            }

            if (isset($result['requires_action'])) {
                return response()->json([
                    'requires_action' => true,
                    'client_secret' => $result['client_secret'],
                ]);
            }

            return back()->with('error', $result['error'] ?? 'Payment failed. Please try again.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Payment callback
     */
    public function paymentCallback(Request $request, Appointment $appointment)
    {
        if ($request->redirect_status === 'succeeded') {
            // Confirm the payment
            $result = $this->stripeService->confirmPayment($appointment->stripe_payment_intent_id);

            if ($result['success']) {
                $appointment->markPaid($appointment->stripe_payment_intent_id, $result['charge_id'] ?? null);
                $this->appointmentService->confirmAppointment($appointment);

                return redirect()
                    ->route('client.appointments.show', $appointment)
                    ->with('success', 'Payment successful! Your appointment is confirmed.');
            }
        }

        return redirect()
            ->route('client.appointments.payment', $appointment)
            ->with('error', 'Payment was not completed. Please try again.');
    }

    /**
     * Show appointment details
     */
    public function show(Appointment $appointment)
    {
        // Verify ownership
        if ($appointment->client->user_id !== Auth::id()) {
            abort(403);
        }

        $appointment->load(['coach', 'package', 'service', 'notes' => function ($query) {
            $query->where('visibility', 'all')->orderByDesc('created_at');
        }]);

        return view('client.appointments.show', compact('appointment'));
    }

    /**
     * Get client profile
     */
    protected function getClientProfile()
    {
        return Client::where('user_id', Auth::id())->first();
    }

    /**
     * Get or create client profile
     */
    protected function getOrCreateClientProfile()
    {
        $client = $this->getClientProfile();

        if (!$client) {
            $client = Client::create([
                'user_id' => Auth::id(),
                'status' => 'active',
            ]);
        }

        return $client;
    }
}