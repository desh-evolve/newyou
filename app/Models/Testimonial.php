<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Testimonial extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'designation',
        'company',
        'testimonial',
        'rating',
        'image',
        'approval_status',
        'show_on_website',
        'display_order',
        'approved_at',
        'approved_by',
        'admin_notes',
        'status',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'show_on_website' => 'boolean',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    public function scopeVisible($query)
    {
        return $query->where('status', 'active')
                     ->where('approval_status', 'approved')
                     ->where('show_on_website', true);
    }

    public function scopePending($query)
    {
        return $query->where('approval_status', 'pending');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'asc')
                     ->orderBy('created_at', 'desc');
    }

    // Accessors
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return Storage::url($this->image);
        }
        return asset('adminlte/dist/img/default-avatar.png');
    }

    public function getApprovalStatusBadgeAttribute()
    {
        return match($this->approval_status) {
            'pending' => '<span class="badge badge-warning">Pending</span>',
            'approved' => '<span class="badge badge-success">Approved</span>',
            'rejected' => '<span class="badge badge-danger">Rejected</span>',
            default => '<span class="badge badge-secondary">Unknown</span>',
        };
    }

    public function getShowBadgeAttribute()
    {
        return $this->show_on_website 
            ? '<span class="badge badge-success">Visible</span>' 
            : '<span class="badge badge-secondary">Hidden</span>';
    }

    // Methods
    public function approve($userId)
    {
        $this->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $userId,
            'updated_by' => $userId,
        ]);
    }

    public function reject($userId, $notes = null)
    {
        $this->update([
            'approval_status' => 'rejected',
            'admin_notes' => $notes,
            'show_on_website' => false,
            'updated_by' => $userId,
        ]);
    }

    public function toggleWebsiteVisibility($userId)
    {
        $this->update([
            'show_on_website' => !$this->show_on_website,
            'updated_by' => $userId,
        ]);
    }

    public function softDelete($userId)
    {
        $this->update([
            'status' => 'delete',
            'updated_by' => $userId,
        ]);
    }
}