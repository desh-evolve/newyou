<?php
// app/Services/NotificationService.php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AppointmentNotification;
use App\Mail\AppointmentCreated;
use App\Mail\AppointmentConfirmed;
use App\Mail\AppointmentCancelled;
use App\Mail\AppointmentReminder;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Send appointment created notifications
     */
    public function sendAppointmentCreatedNotifications(Appointment $appointment)
    {
        // Notify client
        AppointmentNotification::createNotification(
            $appointment->client->user_id,
            'appointment_created',
            'Appointment Booked',
            "Your appointment has been booked for {$appointment->formatted_date} at {$appointment->formatted_time}.",
            $appointment->id
        );

        // Notify coach
        AppointmentNotification::createNotification(
            $appointment->coach_id,
            'appointment_created',
            'New Appointment',
            "New appointment with {$appointment->client->full_name} on {$appointment->formatted_date} at {$appointment->formatted_time}.",
            $appointment->id
        );

        // Send email
        try {
            Mail::to($appointment->client->email)->queue(new AppointmentCreated($appointment));
        } catch (\Exception $e) {
            \Log::error('Failed to send appointment created email: ' . $e->getMessage());
        }
    }

    /**
     * Send appointment confirmed notification
     */
    public function sendAppointmentConfirmedNotification(Appointment $appointment)
    {
        AppointmentNotification::createNotification(
            $appointment->client->user_id,
            'appointment_confirmed',
            'Appointment Confirmed',
            "Your appointment on {$appointment->formatted_date} at {$appointment->formatted_time} has been confirmed.",
            $appointment->id
        );

        try {
            Mail::to($appointment->client->email)->queue(new AppointmentConfirmed($appointment));
        } catch (\Exception $e) {
            \Log::error('Failed to send appointment confirmed email: ' . $e->getMessage());
        }
    }

    /**
     * Send appointment cancelled notification
     */
    public function sendAppointmentCancelledNotification(Appointment $appointment)
    {
        AppointmentNotification::createNotification(
            $appointment->client->user_id,
            'appointment_cancelled',
            'Appointment Cancelled',
            "Your appointment on {$appointment->formatted_date} has been cancelled. Reason: {$appointment->cancellation_reason}",
            $appointment->id
        );

        // Notify coach
        AppointmentNotification::createNotification(
            $appointment->coach_id,
            'appointment_cancelled',
            'Appointment Cancelled',
            "Appointment with {$appointment->client->full_name} on {$appointment->formatted_date} has been cancelled.",
            $appointment->id
        );

        try {
            Mail::to($appointment->client->email)->queue(new AppointmentCancelled($appointment));
        } catch (\Exception $e) {
            \Log::error('Failed to send appointment cancelled email: ' . $e->getMessage());
        }
    }

    /**
     * Send appointment completed notification
     */
    public function sendAppointmentCompletedNotification(Appointment $appointment)
    {
        AppointmentNotification::createNotification(
            $appointment->client->user_id,
            'appointment_completed',
            'Appointment Completed',
            "Your appointment on {$appointment->formatted_date} has been completed. Thank you!",
            $appointment->id
        );
    }

    /**
     * Send payment received notification
     */
    public function sendPaymentReceivedNotification(Appointment $appointment)
    {
        AppointmentNotification::createNotification(
            $appointment->client->user_id,
            'payment_received',
            'Payment Received',
            "Payment of \${$appointment->final_amount} received for your appointment on {$appointment->formatted_date}.",
            $appointment->id
        );

        // Notify admin/coach
        AppointmentNotification::createNotification(
            $appointment->coach_id,
            'payment_received',
            'Payment Received',
            "Payment of \${$appointment->final_amount} received from {$appointment->client->full_name}.",
            $appointment->id
        );
    }

    /**
     * Send payment failed notification
     */
    public function sendPaymentFailedNotification(Appointment $appointment)
    {
        AppointmentNotification::createNotification(
            $appointment->client->user_id,
            'payment_failed',
            'Payment Failed',
            "Payment for your appointment on {$appointment->formatted_date} failed. Please try again.",
            $appointment->id
        );
    }

    /**
     * Send appointment reminder
     */
    public function sendAppointmentReminder(Appointment $appointment)
    {
        AppointmentNotification::createNotification(
            $appointment->client->user_id,
            'appointment_reminder',
            'Appointment Reminder',
            "Reminder: Your appointment is scheduled for {$appointment->formatted_date} at {$appointment->formatted_time}.",
            $appointment->id
        );

        try {
            Mail::to($appointment->client->email)->queue(new AppointmentReminder($appointment));
        } catch (\Exception $e) {
            \Log::error('Failed to send appointment reminder email: ' . $e->getMessage());
        }
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead($userId)
    {
        AppointmentNotification::forUser($userId)
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Get unread count for user
     */
    public function getUnreadCount($userId)
    {
        return AppointmentNotification::getUnreadCount($userId);
    }
}