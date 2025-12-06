<?php
// app/Models/Client.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Client extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'phone',
        'alternate_phone',
        'date_of_birth',
        'gender',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'timezone',
        'emergency_contact_name',
        'emergency_contact_phone',
        'health_notes',
        'goals',
        'preferred_communication',
        'profile_image',
        'metadata',
        'status',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
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

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return $this->user->name ?? '';
    }

    public function getEmailAttribute()
    {
        return $this->user->email ?? '';
    }

    public function getAgeAttribute()
    {
        return $this->date_of_birth ? $this->date_of_birth->age : null;
    }

    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country
        ]);
        return implode(', ', $parts);
    }

    // Methods
    public function softDelete()
    {
        $this->status = 'delete';
        $this->updated_by = Auth::id() ?? 0;
        return $this->save();
    }

    public function getTotalAppointments()
    {
        return $this->appointments()->notDeleted()->count();
    }

    public function getCompletedAppointments()
    {
        return $this->appointments()
            ->notDeleted()
            ->where('appointment_status', 'completed')
            ->count();
    }

    public function getUpcomingAppointments()
    {
        return $this->appointments()
            ->notDeleted()
            ->where('appointment_date', '>=', now()->toDateString())
            ->whereIn('appointment_status', ['pending', 'confirmed'])
            ->orderBy('appointment_date')
            ->orderBy('start_time')
            ->get();
    }
}