<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'image_url',
        'click_url',      // Your actual field name
        'type',
        'placement',      // Your actual field name
        'targeting',      // Your actual field name (JSON)
        'priority',
        'impressions',
        'clicks',
        'budget',
        'cost_per_click',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'targeting' => 'array',  // Your field stores JSON
        'budget' => 'decimal:2',
        'cost_per_click' => 'decimal:2',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('starts_at')
                          ->orWhere('starts_at', '<=', now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('ends_at')
                          ->orWhere('ends_at', '>=', now());
                    });
    }

    public function scopeByPlacement($query, $placement)
    {
        return $query->where('placement', $placement);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopePriority($query)
    {
        return $query->orderBy('priority', 'desc');
    }

    // Target audience logic using the targeting JSON field
    public function scopeForAudience($query, $audience)
    {
        return $query->where(function ($q) use ($audience) {
            $q->where('targeting->audience', 'all')
              ->orWhere('targeting->audience', $audience)
              ->orWhereNull('targeting->audience'); // If no targeting specified, show to all
        });
    }

    // Methods
    public function recordImpression(): void
    {
        $this->increment('impressions');
        $this->updateCtr();
    }

    public function recordClick(): void
    {
        $this->increment('clicks');
        $this->updateCtr();
    }

    private function updateCtr(): void
    {
        if ($this->impressions > 0) {
            $ctr = ($this->clicks / $this->impressions) * 100;
            $this->update(['cost_per_click' => round($ctr, 2)]); // Using cost_per_click as CTR storage
        }
    }

    // Helper methods
    public function getCtrPercentageAttribute()
    {
        if ($this->impressions > 0) {
            return round(($this->clicks / $this->impressions) * 100, 2) . '%';
        }
        return '0%';
    }

    public function isExpired()
    {
        return $this->ends_at && $this->ends_at->isPast();
    }

    public function hasStarted()
    {
        return !$this->starts_at || $this->starts_at->isPast();
    }

    // Accessor for target audience from targeting JSON
    public function getTargetAudienceAttribute()
    {
        return $this->targeting['audience'] ?? 'all';
    }

    // Mutator to set target audience in targeting JSON
    public function setTargetAudienceAttribute($value)
    {
        $targeting = $this->targeting ?: [];
        $targeting['audience'] = $value;
        $this->targeting = $targeting;
    }
}
