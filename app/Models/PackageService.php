<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageService extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_id',
        'service_id',
        'quantity',
        'custom_price',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'custom_price' => 'decimal:2',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    // Relationships
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    //Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Accessors
    public function getEffectivePriceAttribute()
    {
        return $this->custom_price ?? $this->service->price;
    }

    public function getTotalPriceAttribute()
    {
        return $this->effective_price * $this->quantity;
    }

    public function getFormattedEffectivePriceAttribute()
    {
        return '$' . number_format($this->effective_price, 2);
    }

    public function getFormattedTotalPriceAttribute()
    {
        return '$' . number_format($this->total_price, 2);
    }
}