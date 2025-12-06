<?php
// app/Services/TimeSlotService.php

namespace App\Services;

use App\Models\TimeSlot;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Exception;

class TimeSlotService
{
    /**
     * Generate time slots for a coach
     */
    public function generateSlots(array $data)
    {
        $slots = [];
        $coachId = $data['coach_id'];
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);
        $startTime = $data['start_time'];
        $endTime = $data['end_time'];
        
        // Ensure duration is an integer
        $duration = (int) ($data['duration'] ?? 60);
        
        $selectedDays = $data['days'] ?? [0, 1, 2, 3, 4, 5, 6];

        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            // Check if this day of week is selected
            if (!in_array($date->dayOfWeek, $selectedDays)) {
                continue;
            }

            $generatedSlots = TimeSlot::generateSlots(
                $coachId,
                $date->format('Y-m-d'),
                $startTime,
                $endTime,
                $duration  // Now an integer
            );

            $slots = array_merge($slots, $generatedSlots);
        }

        return $slots;
    }

    /**
     * Get available slots for a coach on a specific date
     */
    public function getAvailableSlots($coachId, $date)
    {
        return TimeSlot::forCoach($coachId)
            ->forDate($date)
            ->available()
            ->active()
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Get all slots for a coach within a date range
     */
    public function getSlotsForCalendar($coachId, $startDate, $endDate)
    {
        return TimeSlot::forCoach($coachId)
            ->betweenDates($startDate, $endDate)
            ->notDeleted()
            ->orderBy('slot_date')
            ->orderBy('start_time')
            ->get()
            ->map(function ($slot) {
                return [
                    'id' => $slot->id,
                    'title' => $this->getSlotTitle($slot),
                    'start' => $slot->slot_date->format('Y-m-d') . 'T' . $slot->start_time,
                    'end' => $slot->slot_date->format('Y-m-d') . 'T' . $slot->end_time,
                    'color' => $this->getSlotColor($slot->slot_status),
                    'extendedProps' => [
                        'status' => $slot->slot_status,
                        'duration' => $slot->duration_minutes,
                        'coach_id' => $slot->coach_id,
                    ],
                ];
            });
    }

    /**
     * Get slot title based on status
     */
    protected function getSlotTitle($slot)
    {
        $titles = [
            'available' => 'Available',
            'locked' => 'Locked',
            'booked' => 'Booked',
            'blocked' => 'Blocked',
        ];

        return $titles[$slot->slot_status] ?? 'Unknown';
    }

    /**
     * Get slot color based on status
     */
    protected function getSlotColor($status)
    {
        $colors = [
            'available' => '#28a745',  // Green
            'locked' => '#ffc107',     // Yellow
            'booked' => '#17a2b8',     // Cyan
            'blocked' => '#dc3545',    // Red
        ];

        return $colors[$status] ?? '#6c757d';
    }

    /**
     * Block a slot
     */
    public function blockSlot(TimeSlot $slot, $reason = null)
    {
        if ($slot->slot_status !== 'available') {
            throw new Exception('Only available slots can be blocked.');
        }

        $slot->block();
        
        if ($reason) {
            $slot->notes = $reason;
            $slot->save();
        }

        return $slot;
    }

    /**
     * Unblock a slot
     */
    public function unblockSlot(TimeSlot $slot)
    {
        if ($slot->slot_status !== 'blocked') {
            throw new Exception('Only blocked slots can be unblocked.');
        }

        $slot->unlock();
        return $slot;
    }

    /**
     * Bulk delete slots
     */
    public function bulkDeleteSlots(array $slotIds)
    {
        $deleted = 0;

        foreach ($slotIds as $id) {
            $slot = TimeSlot::find($id);
            
            if ($slot && $slot->slot_status === 'available') {
                $slot->softDelete();
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * Get upcoming available slots for booking widget
     */
    public function getUpcomingAvailableSlots($coachId = null, $limit = 10)
    {
        $query = TimeSlot::available()
            ->active()
            ->upcoming();

        if ($coachId) {
            $query->forCoach($coachId);
        }

        return $query->limit($limit)->get();
    }

    /**
     * Check if a slot can be booked
     */
    public function canBook(TimeSlot $slot)
    {
        return $slot->is_available && !$slot->is_past;
    }
}