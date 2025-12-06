<?php
// app/Services/AppointmentService.php

namespace App\Services;

use App\Models\Appointment;
use App\Models\TimeSlot;
use App\Models\Client;
use App\Models\AppointmentNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Exception;

class AppointmentService
{
    protected $stripeService;
    protected $notificationService;

    public function __construct(StripePaymentService $stripeService, NotificationService $notificationService)
    {
        $this->stripeService = $stripeService;
        $this->notificationService = $notificationService;
    }

    /**
     * Create a new appointment
     */
    public function createAppointment(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Get the time slot
            $timeSlot = TimeSlot::findOrFail($data['time_slot_id']);

            // Check if slot is available
            if (!$timeSlot->is_available) {
                throw new Exception('This time slot is no longer available.');
            }

            // Get or create client record
            $client = $this->getOrCreateClient($data['client_user_id'], $data['client_data'] ?? []);

            // Calculate pricing
            $pricing = $this->calculatePricing($data['package_id'] ?? null, $data['service_id'] ?? null);

            // Create the appointment
            $appointment = Appointment::create([
                'client_id' => $client->id,
                'coach_id' => $timeSlot->coach_id,
                'time_slot_id' => $timeSlot->id,
                'package_id' => $data['package_id'] ?? null,
                'service_id' => $data['service_id'] ?? null,
                'appointment_date' => $timeSlot->slot_date,
                'start_time' => $timeSlot->start_time,
                'end_time' => $timeSlot->end_time,
                'duration_minutes' => $timeSlot->duration_minutes,
                'appointment_type' => $data['appointment_type'] ?? 'video_call',
                'meeting_link' => $data['meeting_link'] ?? null,
                'meeting_location' => $data['meeting_location'] ?? null,
                'amount' => $pricing['amount'],
                'discount_amount' => $pricing['discount'],
                'final_amount' => $pricing['final_amount'],
                'client_notes' => $data['client_notes'] ?? null,
                'appointment_status' => 'pending',
                'payment_status' => 'pending',
            ]);

            // Lock the time slot
            $timeSlot->lock($client->user_id);

            // Send notifications
            $this->notificationService->sendAppointmentCreatedNotifications($appointment);

            return $appointment;
        });
    }

    /**
     * Get or create a client record
     */
    protected function getOrCreateClient($userId, array $clientData = [])
    {
        $client = Client::where('user_id', $userId)->first();

        if (!$client) {
            $client = Client::create(array_merge([
                'user_id' => $userId,
            ], $clientData));
        } elseif (!empty($clientData)) {
            $client->update($clientData);
        }

        return $client;
    }

    /**
     * Calculate pricing based on package and service
     */
    protected function calculatePricing($packageId = null, $serviceId = null)
    {
        $amount = 0;
        $discount = 0;

        if ($packageId) {
            $package = \App\Models\Package::find($packageId);
            if ($package) {
                $amount = $package->price;
                $discount = $package->price - ($package->discount_price ?? $package->price);
            }
        } elseif ($serviceId) {
            $service = \App\Models\Service::find($serviceId);
            if ($service) {
                $amount = $service->price;
            }
        }

        return [
            'amount' => $amount,
            'discount' => $discount,
            'final_amount' => $amount - $discount,
        ];
    }

    /**
     * Confirm an appointment
     */
    public function confirmAppointment(Appointment $appointment)
    {
        return DB::transaction(function () use ($appointment) {
            $appointment->confirm();
            
            // Book the time slot
            $appointment->timeSlot->book();
            
            // Send notification
            $this->notificationService->sendAppointmentConfirmedNotification($appointment);
            
            return $appointment;
        });
    }

    /**
     * Cancel an appointment (admin only)
     */
    public function cancelAppointment(Appointment $appointment, $reason = null)
    {
        return DB::transaction(function () use ($appointment, $reason) {
            $appointment->cancel($reason);
            
            // Send notification
            $this->notificationService->sendAppointmentCancelledNotification($appointment);
            
            // Process refund if paid
            if ($appointment->payment_status === 'paid') {
                $this->stripeService->refundPayment($appointment);
            }
            
            return $appointment;
        });
    }

    /**
     * Complete an appointment
     */
    public function completeAppointment(Appointment $appointment)
    {
        return DB::transaction(function () use ($appointment) {
            $appointment->complete();
            
            // Send notification
            $this->notificationService->sendAppointmentCompletedNotification($appointment);
            
            return $appointment;
        });
    }

    /**
     * Process payment for appointment
     */
    public function processPayment(Appointment $appointment, $paymentMethodId)
    {
        return DB::transaction(function () use ($appointment, $paymentMethodId) {
            $result = $this->stripeService->processPayment($appointment, $paymentMethodId);
            
            if ($result['success']) {
                $appointment->markPaid($result['payment_intent_id'], $result['charge_id'] ?? null);
                
                // Auto-confirm after payment
                $this->confirmAppointment($appointment);
                
                // Send payment notification
                $this->notificationService->sendPaymentReceivedNotification($appointment);
            } else {
                $appointment->payment_status = 'failed';
                $appointment->save();
                
                $this->notificationService->sendPaymentFailedNotification($appointment);
            }
            
            return $result;
        });
    }

    /**
     * Get appointments for calendar view
     */
    public function getAppointmentsForCalendar($startDate, $endDate, $coachId = null, $clientId = null)
    {
        $query = Appointment::with(['client.user', 'coach', 'package', 'service'])
            ->notDeleted()
            ->betweenDates($startDate, $endDate);

        if ($coachId) {
            $query->forCoach($coachId);
        }

        if ($clientId) {
            $query->forClient($clientId);
        }

        return $query->get()->map(function ($appointment) {
            return [
                'id' => $appointment->id,
                'title' => $appointment->client->full_name,
                'start' => $appointment->appointment_date->format('Y-m-d') . 'T' . $appointment->start_time,
                'end' => $appointment->appointment_date->format('Y-m-d') . 'T' . $appointment->end_time,
                'color' => $this->getStatusColor($appointment->appointment_status),
                'extendedProps' => [
                    'appointment_number' => $appointment->appointment_number,
                    'status' => $appointment->appointment_status,
                    'payment_status' => $appointment->payment_status,
                    'type' => $appointment->appointment_type,
                    'coach_name' => $appointment->coach->name,
                    'client_name' => $appointment->client->full_name,
                    'package' => $appointment->package->name ?? null,
                    'service' => $appointment->service->name ?? null,
                ],
            ];
        });
    }

    /**
     * Get status color for calendar
     */
    protected function getStatusColor($status)
    {
        $colors = [
            'pending' => '#ffc107',      // Yellow
            'confirmed' => '#17a2b8',    // Cyan
            'in_progress' => '#007bff',  // Blue
            'completed' => '#28a745',    // Green
            'cancelled' => '#dc3545',    // Red
            'no_show' => '#6c757d',      // Gray
            'rescheduled' => '#343a40',  // Dark
        ];

        return $colors[$status] ?? '#6c757d';
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats($coachId = null)
    {
        $baseQuery = Appointment::notDeleted();
        
        if ($coachId) {
            $baseQuery->forCoach($coachId);
        }

        return [
            'today' => (clone $baseQuery)->today()->count(),
            'this_week' => (clone $baseQuery)->thisWeek()->count(),
            'this_month' => (clone $baseQuery)->thisMonth()->count(),
            'pending' => (clone $baseQuery)->pending()->count(),
            'completed' => (clone $baseQuery)->byStatus('completed')->count(),
            'cancelled' => (clone $baseQuery)->byStatus('cancelled')->count(),
            'total_revenue' => (clone $baseQuery)->paid()->sum('final_amount'),
            'upcoming' => (clone $baseQuery)->upcoming()->take(5)->get(),
        ];
    }

    /**
     * Clear sidebar cache for a user
     */
    function clearSidebarCache($userId = null): void
    {
        $userId = $userId ?? auth()->id();
        if ($userId) {
            Cache::forget("sidebar_counts_{$userId}");
        }
    }

    // Call this after creating/updating/deleting appointments
    // Example in AppointmentService after createAppointment():
    //clearSidebarCache();
}