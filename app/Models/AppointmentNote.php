<?php
// app/Models/AppointmentNote.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AppointmentNote extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'appointment_id',
        'coach_id',
        'title',
        'note_content',
        'note_type',
        'visibility',
        'is_pinned',
        'attachments',
        'metadata',
        'status',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'attachments' => 'array',
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

    public function scopePinned(Builder $query): Builder
    {
        return $query->where('is_pinned', true);
    }

    public function scopeByType(Builder $query, $type): Builder
    {
        return $query->where('note_type', $type);
    }

    public function scopeVisibleTo(Builder $query, $visibility): Builder
    {
        if ($visibility === 'admin') {
            return $query->whereIn('visibility', ['admin_coach', 'all']);
        } elseif ($visibility === 'coach') {
            return $query->whereIn('visibility', ['coach_only', 'admin_coach', 'all']);
        } elseif ($visibility === 'client') {
            return $query->where('visibility', 'all');
        }
        return $query;
    }

    // Relationships
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function coach()
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Accessors
    public function getTypeBadgeAttribute()
    {
        $badges = [
            'general' => '<span class="badge badge-secondary">General</span>',
            'progress' => '<span class="badge badge-info">Progress</span>',
            'goal' => '<span class="badge badge-primary">Goal</span>',
            'action_item' => '<span class="badge badge-warning">Action Item</span>',
            'follow_up' => '<span class="badge badge-success">Follow Up</span>',
            'private' => '<span class="badge badge-dark">Private</span>',
        ];

        return $badges[$this->note_type] ?? '<span class="badge badge-secondary">Unknown</span>';
    }

    public function getVisibilityBadgeAttribute()
    {
        $badges = [
            'coach_only' => '<span class="badge badge-dark"><i class="fas fa-lock"></i> Coach Only</span>',
            'admin_coach' => '<span class="badge badge-warning"><i class="fas fa-user-shield"></i> Admin & Coach</span>',
            'all' => '<span class="badge badge-success"><i class="fas fa-globe"></i> Visible to All</span>',
        ];

        return $badges[$this->visibility] ?? '<span class="badge badge-secondary">Unknown</span>';
    }

    public function getExcerptAttribute()
    {
        return \Str::limit(strip_tags($this->note_content), 100);
    }

    // Methods
    public function togglePin()
    {
        $this->is_pinned = !$this->is_pinned;
        $this->updated_by = Auth::id() ?? 0;
        return $this->save();
    }

    public function softDelete()
    {
        $this->status = 'delete';
        $this->updated_by = Auth::id() ?? 0;
        return $this->save();
    }
}