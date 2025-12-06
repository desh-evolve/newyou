<?php
// app/Console/Commands/DailyAppointmentReport.php

namespace App\Console\Commands;

use App\Models\Appointment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class DailyAppointmentReport extends Command
{
    protected $signature = 'appointments:daily-report 
                            {--date= : Specific date for report (Y-m-d format)}';
    
    protected $description = 'Generate and send daily appointment report';

    public function handle(): int
    {
        $date = $this->option('date') 
            ? Carbon::parse($this->option('date')) 
            : Carbon::today();

        $this->info("Generating report for {$date->format('Y-m-d')}...");

        $appointments = Appointment::with(['client.user', 'coach'])
            ->whereDate('appointment_date', $date)
            ->notDeleted()
            ->get();

        $stats = [
            'date' => $date->format('F j, Y'),
            'total' => $appointments->count(),
            'completed' => $appointments->where('appointment_status', 'completed')->count(),
            'cancelled' => $appointments->where('appointment_status', 'cancelled')->count(),
            'no_show' => $appointments->where('appointment_status', 'no_show')->count(),
            'pending' => $appointments->where('appointment_status', 'pending')->count(),
            'revenue' => $appointments->where('payment_status', 'paid')->sum('final_amount'),
        ];

        // Output to console
        $this->newLine();
        $this->table(
            ['Metric', 'Value'],
            [
                ['Date', $stats['date']],
                ['Total Appointments', $stats['total']],
                ['Completed', $stats['completed']],
                ['Cancelled', $stats['cancelled']],
                ['No Shows', $stats['no_show']],
                ['Pending', $stats['pending']],
                ['Revenue', '$' . number_format($stats['revenue'], 2)],
            ]
        );

        // Send email report (optional)
        if (env('ADMIN_EMAIL')) {
            try {
                Mail::raw($this->generateReportText($stats), function ($message) use ($stats) {
                    $message->to(env('ADMIN_EMAIL'))
                        ->subject("Daily Appointment Report - {$stats['date']}");
                });
                $this->info('Report sent to ' . env('ADMIN_EMAIL'));
            } catch (\Exception $e) {
                $this->error('Failed to send email: ' . $e->getMessage());
            }
        }

        return Command::SUCCESS;
    }

    protected function generateReportText(array $stats): string
    {
        return "Daily Appointment Report - {$stats['date']}\n\n" .
            "Total Appointments: {$stats['total']}\n" .
            "Completed: {$stats['completed']}\n" .
            "Cancelled: {$stats['cancelled']}\n" .
            "No Shows: {$stats['no_show']}\n" .
            "Pending: {$stats['pending']}\n" .
            "Revenue: $" . number_format($stats['revenue'], 2) . "\n";
    }
}