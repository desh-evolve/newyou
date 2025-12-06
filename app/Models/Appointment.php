<?php
// app/Models/Appointment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Appointment extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'appointment_number',
        'client_id',
        'coach_id',
        'time_slot_id',
        'package_id',
        'service_id',
        'appointment_date',
        'start_time',
        'end_time',
        'duration_minutes',
        'appointment_type',
        'meeting_link',
        'meeting_location',
        'appointment_status',
        'amount',
        'discount_amount',
        'final_amount',
        'payment_status',
        'stripe_payment_intent_id',
        'stripe_charge_id',
        'client_notes',
        'admin_notes',
        'cancellation_reason',
        'cancelled_by',
        'cancelled_at',
        'confirmed_at',
        'completed_at',
        'metadata',
        'status',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'cancelled_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'completed_at' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_by = Auth::id() ?? 0;
            $model->updated_by = Auth::id() ?? 0;
            
            if (empty($model->appointment_number)) {
                $model->appointment_number = self::generateAppointmentNumber();
            }
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

    public function scopeForClient(Builder $query, $clientId): Builder
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeForCoach(Builder $query, $coachId): Builder
    {
        return $query->where('coach_id', $coachId);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('appointment_date', '>=', now()->toDateString())
            ->whereIn('appointment_status', ['pending', 'confirmed'])
            ->orderBy('appointment_date')
            ->orderBy('start_time');
    }

    public function scopePast(Builder $query): Builder
    {
        return $query->where('appointment_date', '<', now()->toDateString())
            ->orWhere('appointment_status', 'completed')
            ->orderByDesc('appointment_date')
            ->orderByDesc('start_time');
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->where('appointment_date', now()->toDateString());
    }

    public function scopeThisWeek(Builder $query): Builder
    {
        return $query->whereBetween('appointment_date', [
            now()->startOfWeek()->toDateString(),
            now()->endOfWeek()->toDateString()
        ]);
    }

    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereMonth('appointment_date', now()->month)
            ->whereYear('appointment_date', now()->year);
    }

    public function scopeBetweenDates(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('appointment_date', [$startDate, $endDate]);
    }

    public function scopeByStatus(Builder $query, $status): Builder
    {
        return $query->where('appointment_status', $status);
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('appointment_status', 'pending');
    }

    // Relationships
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function coach()
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    public function timeSlot()
    {
        return $this->belongsTo(TimeSlot::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function notes()
    {
        return $this->hasMany(AppointmentNote::class)->where('status', '!=', 'delete');
    }

    public function notifications()
    {
        return $this->hasMany(AppointmentNotification::class);
    }

    public function cancelledByUser()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    // Accessors
    public function getFormattedDateAttribute()
    {
        return $this->appointment_date->format('M d, Y');
    }

    public function getFormattedTimeAttribute()
    {
        return Carbon::parse($this->start_time)->format('g:i A') . ' - ' . 
               Carbon::parse($this->end_time)->format('g:i A');
    }

    public function getFullDateTimeAttribute()
    {
        return $this->formatted_date . ' ' . $this->formatted_time;
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => '<span class="badge badge-warning">Pending</span>',
            'confirmed' => '<span class="badge badge-info">Confirmed</span>',
            'in_progress' => '<span class="badge badge-primary">In Progress</span>',
            'completed' => '<span class="badge badge-success">Completed</span>',
            'cancelled' => '<span class="badge badge-danger">Cancelled</span>',
            'no_show' => '<span class="badge badge-secondary">No Show</span>',
            'rescheduled' => '<span class="badge badge-dark">Rescheduled</span>',
        ];

        return $badges[$this->appointment_status] ?? '<span class="badge badge-secondary">Unknown</span>';
    }

    public function getPaymentBadgeAttribute()
    {
        $badges = [
            'pending' => '<span class="badge badge-warning">Payment Pending</span>',
            'paid' => '<span class="badge badge-success">Paid</span>',
            'failed' => '<span class="badge badge-danger">Payment Failed</span>',
            'refunded' => '<span class="badge badge-info">Refunded</span>',
            'partial' => '<span class="badge badge-secondary">Partial Payment</span>',
        ];

        return $badges[$this->payment_status] ?? '<span class="badge badge-secondary">Unknown</span>';
    }

    public function getTypeBadgeAttribute()
    {
        $badges = [
            'in_person' => '<span class="badge badge-primary"><i class="fas fa-user"></i> In Person</span>',
            'video_call' => '<span class="badge badge-info"><i class="fas fa-video"></i> Video Call</span>',
            'phone_call' => '<span class="badge badge-secondary"><i class="fas fa-phone"></i> Phone Call</span>',
        ];

        return $badges[$this->appointment_type] ?? '<span class="badge badge-secondary">Unknown</span>';
    }

    public function getIsPastAttribute()
    {
        $appointmentDateTime = Carbon::parse($this->appointment_date->format('Y-m-d') . ' ' . $this->end_time);
        return $appointmentDateTime->isPast();
    }

    public function getIsUpcomingAttribute()
    {
        $appointmentDateTime = Carbon::parse($this->appointment_date->format('Y-m-d') . ' ' . $this->start_time);
        return $appointmentDateTime->isFuture() && in_array($this->appointment_status, ['pending', 'confirmed']);
    }

    public function getIsTodayAttribute()
    {
        return $this->appointment_date->isToday();
    }

    public function getCanStartAttribute()
    {
        if ($this->appointment_status !== 'confirmed') {
            return false;
        }

        $startTime = Carbon::parse($this->appointment_date->format('Y-m-d') . ' ' . $this->start_time);
        $endTime = Carbon::parse($this->appointment_date->format('Y-m-d') . ' ' . $this->end_time);

        return now()->between($startTime->subMinutes(15), $endTime);
    }

    // Methods
    public static function generateAppointmentNumber()
    {
        $prefix = 'APT';
        $date = now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -4));
        
        return $prefix . $date . $random;
    }

    public function confirm()
    {
        $this->appointment_status = 'confirmed';
        $this->confirmed_at = now();
        $this->updated_by = Auth::id() ?? 0;
        return $this->save();
    }

    public function start()
    {
        $this->appointment_status = 'in_progress';
        $this->updated_by = Auth::id() ?? 0;
        return $this->save();
    }

    public function complete()
    {
        $this->appointment_status = 'completed';
        $this->completed_at = now();
        $this->updated_by = Auth::id() ?? 0;
        return $this->save();
    }

    public function cancel($reason = null, $userId = null)
    {
        $this->appointment_status = 'cancelled';
        $this->cancellation_reason = $reason;
        $this->cancelled_by = $userId ?? Auth::id();
        $this->cancelled_at = now();
        $this->updated_by = Auth::id() ?? 0;
        
        // Unlock the time slot
        if ($this->timeSlot) {
            $this->timeSlot->unlock();
        }

        return $this->save();
    }

    public function markNoShow()
    {
        $this->appointment_status = 'no_show';
        $this->updated_by = Auth::id() ?? 0;
        return $this->save();
    }

    public function markPaid($stripePaymentIntentId = null, $stripeChargeId = null)
    {
        $this->payment_status = 'paid';
        $this->stripe_payment_intent_id = $stripePaymentIntentId;
        $this->stripe_charge_id = $stripeChargeId;
        $this->updated_by = Auth::id() ?? 0;
        return $this->save();
    }

    public function markRefunded()
    {
        $this->payment_status = 'refunded';
        $this->updated_by = Auth::id() ?? 0;
        return $this->save();
    }

    public function softDelete()
    {
        $this->status = 'delete';
        $this->updated_by = Auth::id() ?? 0;
        
        // Unlock the time slot
        if ($this->timeSlot) {
            $this->timeSlot->unlock();
        }

        return $this->save();
    }

    public function getNotesCount()
    {
        return $this->notes()->count();
    }

    public function getPinnedNotes()
    {
        return $this->notes()->where('is_pinned', true)->get();
    }
}