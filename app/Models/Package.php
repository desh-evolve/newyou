<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Package extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'discount_price',
        'validity_days',
        'image',
        'status',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'validity_days' => 'integer',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($package) {
            if (empty($package->slug)) {
                $package->slug = Str::slug($package->name);
            }
        });
    }

    // Relationships
    public function services()
    {
        return $this->belongsToMany(Service::class, 'package_services')
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

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
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

    public function getFormattedDiscountPriceAttribute()
    {
        return $this->discount_price ? '$' . number_format($this->discount_price, 2) : null;
    }

    public function getEffectivePriceAttribute()
    {
        return $this->discount_price ?? $this->price;
    }

    public function getDiscountPercentageAttribute()
    {
        if (!$this->discount_price || $this->price <= 0) return 0;
        return round((($this->price - $this->discount_price) / $this->price) * 100);
    }

    public function getServicesValueAttribute()
    {
        return $this->packageServices->sum(function ($ps) {
            $price = $ps->custom_price ?? $ps->service->price;
            return $price * $ps->quantity;
        });
    }

    public function getSavingsAttribute()
    {
        return max(0, $this->services_value - $this->effective_price);
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : asset('images/default-package.png');
    }
}