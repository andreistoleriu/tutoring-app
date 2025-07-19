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
        'link_url',
        'type',
        'target_audience',
        'impressions',
        'clicks',
        'ctr',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'ctr' => 'decimal:2',
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

    public function scopeForAudience($query, $audience)
    {
        return $query->where(function ($q) use ($audience) {
            $q->where('target_audience', 'all')
              ->orWhere('target_audience', $audience);
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
            $this->update(['ctr' => ($this->clicks / $this->impressions) * 100]);
        }
    }
}
