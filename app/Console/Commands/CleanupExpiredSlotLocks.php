<?php
// app/Console/Commands/CleanupExpiredSlotLocks.php

namespace App\Console\Commands;

use App\Models\TimeSlot;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CleanupExpiredSlotLocks extends Command
{
    protected $signature = 'appointments:cleanup-locks 
                            {--minutes=30 : Minutes after which to release locks}';
    
    protected $description = 'Release locked time slots that have expired';

    public function handle(): int
    {
        $minutes = $this->option('minutes');
        $expiredTime = Carbon::now()->subMinutes($minutes);

        $this->info("Releasing locks older than {$minutes} minutes...");

        $expiredLocks = TimeSlot::where('slot_status', 'locked')
            ->where('locked_at', '<', $expiredTime)
            ->notDeleted()
            ->get();

        $count = 0;

        foreach ($expiredLocks as $slot) {
            $slot->unlock();
            $count++;
            $this->line("  ✓ Released lock on slot #{$slot->id} ({$slot->slot_date} {$slot->formatted_time})");
        }

        $this->newLine();
        $this->info("Released {$count} expired locks.");

        return Command::SUCCESS;
    }
}