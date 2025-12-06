<?php
// app/Models/TimeSlot.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TimeSlot extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'coach_id',
        'slot_date',
        'start_time',
        'end_time',
        'duration_minutes',
        'slot_status',
        'locked_by',
        'locked_at',
        'notes',
        'is_recurring_generated',
        'status',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'slot_date' => 'date',
        'locked_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'is_recurring_generated' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_by = Auth::id() ?? 0;
            $model->updated_by = Auth::id() ?? 0;
        });

        static::updating(function ($model) {
            $model->updated_by = Auth::id() ?? 0;
        });
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeNotDeleted(Builder $query): Builder
    {
        return $query->where('status', '!=', 'delete');
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('slot_status', 'available');
    }

    public function scopeForCoach(Builder $query, $coachId): Builder
    {
        return $query->where('coach_id', $coachId);
    }

    public function scopeForDate(Builder $query, $date): Builder
    {
        return $query->where('slot_date', $date);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('slot_date', '>=', now()->toDateString())
            ->orderBy('slot_date')
            ->orderBy('start_time');
    }

    public function scopeBetweenDates(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('slot_date', [$startDate, $endDate]);
    }

    // Relationships
    public function coach()
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    public function lockedByUser()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function appointment()
    {
        return $this->hasOne(Appointment::class);
    }

    // Accessors
    public function getFormattedTimeAttribute()
    {
        return Carbon::parse($this->start_time)->format('g:i A') . ' - ' . 
               Carbon::parse($this->end_time)->format('g:i A');
    }

    public function getFormattedDateAttribute()
    {
        return $this->slot_date->format('M d, Y');
    }

    public function getFullDateTimeAttribute()
    {
        return $this->slot_date->format('M d, Y') . ' ' . $this->formatted_time;
    }

    public function getIsAvailableAttribute()
    {
        return $this->slot_status === 'available' && 
               $this->status === 'active' &&
               $this->slot_date >= now()->toDateString();
    }

    public function getIsPastAttribute()
    {
        $slotDateTime = Carbon::parse($this->slot_date->format('Y-m-d') . ' ' . $this->start_time);
        return $slotDateTime->isPast();
    }

    // Methods
    public function lock($userId = null)
    {
        $this->slot_status = 'locked';
        $this->locked_by = $userId ?? Auth::id();
        $this->locked_at = now();
        $this->updated_by = Auth::id() ?? 0;
        return $this->save();
    }

    public function unlock()
    {
        $this->slot_status = 'available';
        $this->locked_by = null;
        $this->locked_at = null;
        $this->updated_by = Auth::id() ?? 0;
        return $this->save();
    }

    public function book()
    {
        $this->slot_status = 'booked';
        $this->updated_by = Auth::id() ?? 0;
        return $this->save();
    }

    public function block()
    {
        $this->slot_status = 'blocked';
        $this->updated_by = Auth::id() ?? 0;
        return $this->save();
    }

    public function softDelete()
    {
        $this->status = 'delete';
        $this->updated_by = Auth::id() ?? 0;
        return $this->save();
    }

    public static function generateSlots($coachId, $date, $startTime, $endTime, $duration = 60)
    {
        $slots = [];
        
        // Ensure duration is an integer
        $duration = (int) $duration;
        
        $current = Carbon::parse($date . ' ' . $startTime);
        $end = Carbon::parse($date . ' ' . $endTime);

        while ($current->copy()->addMinutes($duration)->lte($end)) {
            $slotEnd = $current->copy()->addMinutes($duration);
            
            // Check if slot already exists
            $exists = self::where('coach_id', $coachId)
                ->where('slot_date', $date)
                ->where('start_time', $current->format('H:i:s'))
                ->notDeleted()
                ->exists();

            if (!$exists) {
                $slots[] = self::create([
                    'coach_id' => $coachId,
                    'slot_date' => $date,
                    'start_time' => $current->format('H:i:s'),
                    'end_time' => $slotEnd->format('H:i:s'),
                    'duration_minutes' => $duration,  // This is now an integer
                    'slot_status' => 'available',
                ]);
            }

            $current = $slotEnd;
        }

        return $slots;
    }
}