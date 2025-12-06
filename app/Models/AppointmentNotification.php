<?php
// app/Models/AppointmentNotification.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AppointmentNotification extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'appointment_id',
        'title',
        'message',
        'type',
        'channel',
        'is_read',
        'read_at',
        'sent_at',
        'metadata',
        'status',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'sent_at' => 'datetime',
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

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    public function scopeRead(Builder $query): Builder
    {
        return $query->where('is_read', true);
    }

    public function scopeForUser(Builder $query, $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByType(Builder $query, $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeRecent(Builder $query, $days = 30): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    // Accessors
    public function getTypeIconAttribute()
    {
        $icons = [
            'appointment_created' => 'fas fa-calendar-plus text-success',
            'appointment_confirmed' => 'fas fa-calendar-check text-info',
            'appointment_cancelled' => 'fas fa-calendar-times text-danger',
            'appointment_reminder' => 'fas fa-bell text-warning',
            'appointment_completed' => 'fas fa-check-circle text-success',
            'payment_received' => 'fas fa-dollar-sign text-success',
            'payment_failed' => 'fas fa-exclamation-circle text-danger',
            'slot_available' => 'fas fa-clock text-info',
            'general' => 'fas fa-info-circle text-secondary',
        ];

        return $icons[$this->type] ?? 'fas fa-bell text-secondary';
    }

    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    // Methods
    public function markAsRead()
    {
        $this->is_read = true;
        $this->read_at = now();
        $this->updated_by = Auth::id() ?? 0;
        return $this->save();
    }

    public function markAsUnread()
    {
        $this->is_read = false;
        $this->read_at = null;
        $this->updated_by = Auth::id() ?? 0;
        return $this->save();
    }

    public function softDelete()
    {
        $this->status = 'delete';
        $this->updated_by = Auth::id() ?? 0;
        return $this->save();
    }

    // Static Methods
    public static function createNotification($userId, $type, $title, $message, $appointmentId = null, $metadata = [])
    {
        return self::create([
            'user_id' => $userId,
            'appointment_id' => $appointmentId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'channel' => 'database',
            'metadata' => $metadata,
        ]);
    }

    public static function getUnreadCount($userId)
    {
        return self::forUser($userId)->notDeleted()->unread()->count();
    }
}