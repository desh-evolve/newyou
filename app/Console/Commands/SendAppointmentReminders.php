<?php
// app/Console/Commands/SendAppointmentReminders.php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SendAppointmentReminders extends Command
{
    protected $signature = 'appointments:send-reminders 
                            {--days=1 : Days before appointment to send reminder}';
    
    protected $description = 'Send appointment reminders to clients';

    public function handle(NotificationService $notificationService): int
    {
        $days = $this->option('days');
        $targetDate = Carbon::now()->addDays($days)->toDateString();

        $this->info("Sending reminders for appointments on {$targetDate}...");

        $appointments = Appointment::with(['client.user', 'coach'])
            ->where('appointment_date', $targetDate)
            ->whereIn('appointment_status', ['confirmed'])
            ->where('payment_status', 'paid')
            ->notDeleted()
            ->get();

        $count = 0;
        $errors = 0;

        foreach ($appointments as $appointment) {
            try {
                $notificationService->sendAppointmentReminder($appointment);
                $count++;
                $this->line("  ✓ Sent reminder for appointment #{$appointment->appointment_number}");
            } catch (\Exception $e) {
                $errors++;
                $this->error("  ✗ Failed for #{$appointment->appointment_number}: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info("Completed: {$count} reminders sent, {$errors} errors.");

        return $errors > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}