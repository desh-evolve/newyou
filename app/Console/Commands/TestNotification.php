<?php
// app/Console/Commands/TestNotification.php

namespace App\Console\Commands;

use App\Models\AppointmentNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class TestNotification extends Command
{
    protected $signature = 'test:notification {user_id?}';
    protected $description = 'Create a test notification';

    public function handle()
    {
        $userId = $this->argument('user_id') ?? 1;
        
        $this->info("Creating test notification for user ID: {$userId}");
        
        try {
            $notification = AppointmentNotification::create([
                'user_id' => $userId,
                'appointment_id' => null,
                'title' => 'Test Notification',
                'message' => 'This is a test notification created at ' . now()->format('Y-m-d H:i:s'),
                'type' => 'general',
                'channel' => 'database',
                'is_read' => false,
                'status' => 'active',
            ]);
            
            $this->info("Notification created with ID: {$notification->id}");
            
            // Verify it exists
            $count = AppointmentNotification::where('user_id', $userId)->count();
            $this->info("Total notifications for user {$userId}: {$count}");
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            $this->error($e->getTraceAsString());
        }
        
        return Command::SUCCESS;
    }
}