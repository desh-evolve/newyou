<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'duration',
        'icon',
        'image',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration' => 'integer',
        'sort_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($service) {
            if (empty($service->slug)) {
                $service->slug = Str::slug($service->name);
            }
        });
        
        static::updating(function ($service) {
            if ($service->isDirty('name') && empty($service->slug)) {
                $service->slug = Str::slug($service->name);
            }
        });
    }

    // Relationships
    public function packages()
    {
        return $this->belongsToMany(Package::class, 'package_services')
                    ->withPivot(['quantity', 'custom_price', 'notes'])
                    ->withTimestamps();
    }

    public function packageServices()
    {
        return $this->hasMany(PackageService::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // Accessors
    public function getFormattedPriceAttribute()
    {
        return '$' . number_format($this->price, 2);
    }

    public function getFormattedDurationAttribute()
    {
        if (!$this->duration) return 'N/A';
        
        $hours = floor($this->duration / 60);
        $minutes = $this->duration % 60;
        
        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        }
        return "{$minutes}m";
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : asset('images/default-service.png');
    }
}