<?php
// app/Http/Controllers/Admin/PaymentController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Services\AppointmentService;
use App\Services\StripePaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected $appointmentService;
    protected $stripeService;

    public function __construct(
        AppointmentService $appointmentService,
        StripePaymentService $stripeService
    ) {
        $this->appointmentService = $appointmentService;
        $this->stripeService = $stripeService;
    }

    /**
     * Process manual payment
     */
    public function processPayment(Request $request, Appointment $appointment)
    {
        $request->validate([
            'payment_method' => 'required|string',
        ]);

        try {
            $result = $this->appointmentService->processPayment(
                $appointment,
                $request->payment_method
            );

            if ($result['success']) {
                return back()->with('success', 'Payment processed successfully.');
            }

            if (isset($result['requires_action'])) {
                return response()->json([
                    'requires_action' => true,
                    'client_secret' => $result['client_secret'],
                ]);
            }

            return back()->with('error', $result['error'] ?? 'Payment failed.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Mark as paid manually
     */
    public function markAsPaid(Request $request, Appointment $appointment)
    {
        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $appointment->markPaid();
            
            if ($request->notes) {
                $appointment->admin_notes = ($appointment->admin_notes ? $appointment->admin_notes . "\n" : '') . 
                    "Manual payment marked: " . $request->notes;
                $appointment->save();
            }

            // Confirm appointment after payment
            $this->appointmentService->confirmAppointment($appointment);

            return back()->with('success', 'Appointment marked as paid.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Process refund
     */
    public function refund(Request $request, Appointment $appointment)
    {
        $request->validate([
            'amount' => 'nullable|numeric|min:0|max:' . $appointment->final_amount,
            'reason' => 'required|string|max:500',
        ]);

        try {
            $result = $this->stripeService->refundPayment($appointment, $request->amount);

            if ($result['success']) {
                $appointment->admin_notes = ($appointment->admin_notes ? $appointment->admin_notes . "\n" : '') . 
                    "Refund processed: " . $request->reason;
                $appointment->save();

                return back()->with('success', 'Refund processed successfully.');
            }

            return back()->with('error', $result['error'] ?? 'Refund failed.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}