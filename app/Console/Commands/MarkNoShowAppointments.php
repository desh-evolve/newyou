<?php
// app/Console/Commands/MarkNoShowAppointments.php

namespace App\Console\Commands;

use App\Models\Appointment;
use Illuminate\Console\Command;
use Carbon\Carbon;

class MarkNoShowAppointments extends Command
{
    protected $signature = 'appointments:mark-no-shows 
                            {--hours=2 : Hours after end time to mark as no-show}';
    
    protected $description = 'Mark confirmed appointments that were not started as no-shows';

    public function handle(): int
    {
        $hours = $this->option('hours');
        
        $this->info("Marking no-shows for appointments ended more than {$hours} hours ago...");

        $cutoffTime = Carbon::now()->subHours($hours);

        $appointments = Appointment::where('appointment_status', 'confirmed')
            ->where('appointment_date', '<=', Carbon::now()->toDateString())
            ->notDeleted()
            ->get()
            ->filter(function ($appointment) use ($cutoffTime) {
                $endDateTime = Carbon::parse(
                    $appointment->appointment_date->format('Y-m-d') . ' ' . $appointment->end_time
                );
                return $endDateTime->lt($cutoffTime);
            });

        $count = 0;

        foreach ($appointments as $appointment) {
            $appointment->markNoShow();
            $count++;
            $this->line("  ✓ Marked #{$appointment->appointment_number} as no-show");
        }

        $this->newLine();
        $this->info("Marked {$count} appointments as no-shows.");

        return Command::SUCCESS;
    }
}