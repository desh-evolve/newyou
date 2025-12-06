<?php
// app/Services/AppointmentService.php

namespace App\Services;

use App\Models\Appointment;
use App\Models\TimeSlot;
use App\Models\Client;
use App\Models\AppointmentNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
            Log::info('Creating notifications for appointment: ' . $appointment->id);
            $this->createAppointmentNotifications($appointment, 'created');

            return $appointment;
        });
    }

    /**
     * Confirm an appointment
     */
    public function confirmAppointment(Appointment $appointment)
    {
        return DB::transaction(function () use ($appointment) {
            $appointment->confirm();
            
            // Book the time slot
            if ($appointment->timeSlot) {
                $appointment->timeSlot->book();
            }
            
            // Send notification
            Log::info('Creating confirm notification for appointment: ' . $appointment->id);
            $this->createAppointmentNotifications($appointment, 'confirmed');
            
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
            Log::info('Creating cancel notification for appointment: ' . $appointment->id);
            $this->createAppointmentNotifications($appointment, 'cancelled');
            
            // Process refund if paid
            if ($appointment->payment_status === 'paid' && $appointment->stripe_payment_intent_id) {
                try {
                    $this->stripeService->refundPayment($appointment);
                } catch (Exception $e) {
                    Log::error('Refund failed: ' . $e->getMessage());
                }
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
            Log::info('Creating complete notification for appointment: ' . $appointment->id);
            $this->createAppointmentNotifications($appointment, 'completed');
            
            return $appointment;
        });
    }

    /**
     * Create notifications for appointment events
     */
    protected function createAppointmentNotifications(Appointment $appointment, string $type)
    {
        try {
            // Reload appointment with relationships
            $appointment->load(['client.user', 'coach']);
            
            $clientUserId = $appointment->client->user_id ?? null;
            $coachId = $appointment->coach_id;
            
            Log::info("Creating notification - Type: {$type}, Client User ID: {$clientUserId}, Coach ID: {$coachId}");
            
            switch ($type) {
                case 'created':
                    // Notify client
                    if ($clientUserId) {
                        AppointmentNotification::create([
                            'user_id' => $clientUserId,
                            'appointment_id' => $appointment->id,
                            'title' => 'Appointment Booked',
                            'message' => "Your appointment has been booked for {$appointment->formatted_date} at {$appointment->formatted_time}.",
                            'type' => 'appointment_created',
                            'channel' => 'database',
                            'is_read' => false,
                        ]);
                        Log::info("Created notification for client: {$clientUserId}");
                    }
                    
                    // Notify coach
                    if ($coachId) {
                        AppointmentNotification::create([
                            'user_id' => $coachId,
                            'appointment_id' => $appointment->id,
                            'title' => 'New Appointment',
                            'message' => "New appointment with {$appointment->client->full_name} on {$appointment->formatted_date} at {$appointment->formatted_time}.",
                            'type' => 'appointment_created',
                            'channel' => 'database',
                            'is_read' => false,
                        ]);
                        Log::info("Created notification for coach: {$coachId}");
                    }
                    
                    // Notify admin (current user if different)
                    $adminId = Auth::id();
                    if ($adminId && $adminId != $clientUserId && $adminId != $coachId) {
                        AppointmentNotification::create([
                            'user_id' => $adminId,
                            'appointment_id' => $appointment->id,
                            'title' => 'Appointment Created',
                            'message' => "Appointment {$appointment->appointment_number} has been created.",
                            'type' => 'appointment_created',
                            'channel' => 'database',
                            'is_read' => false,
                        ]);
                        Log::info("Created notification for admin: {$adminId}");
                    }
                    break;
                    
                case 'confirmed':
                    if ($clientUserId) {
                        AppointmentNotification::create([
                            'user_id' => $clientUserId,
                            'appointment_id' => $appointment->id,
                            'title' => 'Appointment Confirmed',
                            'message' => "Your appointment on {$appointment->formatted_date} at {$appointment->formatted_time} has been confirmed.",
                            'type' => 'appointment_confirmed',
                            'channel' => 'database',
                            'is_read' => false,
                        ]);
                    }
                    
                    if ($coachId) {
                        AppointmentNotification::create([
                            'user_id' => $coachId,
                            'appointment_id' => $appointment->id,
                            'title' => 'Appointment Confirmed',
                            'message' => "Appointment with {$appointment->client->full_name} on {$appointment->formatted_date} has been confirmed.",
                            'type' => 'appointment_confirmed',
                            'channel' => 'database',
                            'is_read' => false,
                        ]);
                    }
                    break;
                    
                case 'cancelled':
                    if ($clientUserId) {
                        AppointmentNotification::create([
                            'user_id' => $clientUserId,
                            'appointment_id' => $appointment->id,
                            'title' => 'Appointment Cancelled',
                            'message' => "Your appointment on {$appointment->formatted_date} has been cancelled. Reason: {$appointment->cancellation_reason}",
                            'type' => 'appointment_cancelled',
                            'channel' => 'database',
                            'is_read' => false,
                        ]);
                    }
                    
                    if ($coachId) {
                        AppointmentNotification::create([
                            'user_id' => $coachId,
                            'appointment_id' => $appointment->id,
                            'title' => 'Appointment Cancelled',
                            'message' => "Appointment with {$appointment->client->full_name} on {$appointment->formatted_date} has been cancelled.",
                            'type' => 'appointment_cancelled',
                            'channel' => 'database',
                            'is_read' => false,
                        ]);
                    }
                    break;
                    
                case 'completed':
                    if ($clientUserId) {
                        AppointmentNotification::create([
                            'user_id' => $clientUserId,
                            'appointment_id' => $appointment->id,
                            'title' => 'Session Completed',
                            'message' => "Your session on {$appointment->formatted_date} has been completed. Thank you!",
                            'type' => 'appointment_completed',
                            'channel' => 'database',
                            'is_read' => false,
                        ]);
                    }
                    break;
            }
            
        } catch (Exception $e) {
            Log::error('Failed to create notification: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            // Don't throw - let the main operation continue
        }
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
                'status' => 'active',
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
                'title' => $appointment->client->full_name ?? 'Unknown',
                'start' => $appointment->appointment_date->format('Y-m-d') . 'T' . $appointment->start_time,
                'end' => $appointment->appointment_date->format('Y-m-d') . 'T' . $appointment->end_time,
                'color' => $this->getStatusColor($appointment->appointment_status),
                'extendedProps' => [
                    'appointment_number' => $appointment->appointment_number,
                    'status' => $appointment->appointment_status,
                    'payment_status' => $appointment->payment_status,
                    'type' => $appointment->appointment_type,
                    'coach_name' => $appointment->coach->name ?? 'Unknown',
                    'client_name' => $appointment->client->full_name ?? 'Unknown',
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
            'pending' => '#ffc107',
            'confirmed' => '#17a2b8',
            'in_progress' => '#007bff',
            'completed' => '#28a745',
            'cancelled' => '#dc3545',
            'no_show' => '#6c757d',
            'rescheduled' => '#343a40',
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
                
                // Create payment notification
                $this->createPaymentNotification($appointment, true);
            } else {
                $appointment->payment_status = 'failed';
                $appointment->save();
                
                $this->createPaymentNotification($appointment, false);
            }
            
            return $result;
        });
    }

    /**
     * Create payment notification
     */
    protected function createPaymentNotification(Appointment $appointment, bool $success)
    {
        try {
            $appointment->load(['client.user']);
            $clientUserId = $appointment->client->user_id ?? null;
            
            if ($clientUserId) {
                if ($success) {
                    AppointmentNotification::create([
                        'user_id' => $clientUserId,
                        'appointment_id' => $appointment->id,
                        'title' => 'Payment Received',
                        'message' => "Payment of \${$appointment->final_amount} received for your appointment on {$appointment->formatted_date}.",
                        'type' => 'payment_received',
                        'channel' => 'database',
                        'is_read' => false,
                    ]);
                } else {
                    AppointmentNotification::create([
                        'user_id' => $clientUserId,
                        'appointment_id' => $appointment->id,
                        'title' => 'Payment Failed',
                        'message' => "Payment for your appointment on {$appointment->formatted_date} failed. Please try again.",
                        'type' => 'payment_failed',
                        'channel' => 'database',
                        'is_read' => false,
                    ]);
                }
            }
        } catch (Exception $e) {
            Log::error('Failed to create payment notification: ' . $e->getMessage());
        }
    }
}