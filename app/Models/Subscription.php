<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_type',
        'price',
        'currency',
        'status',
        'started_at',
        'expires_at',
        'trial_ends_at',
        'shows_ads',
        'netopia_subscription_id',
        'cancellation_reason',
        'cancelled_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'shows_ads' => 'boolean',
    ];

    const PLANS = [
        'free_trial' => [
            'name' => 'Free Trial with Ads',
            'price' => 0,
            'duration_days' => 14,
            'shows_ads' => true,
            'features' => [
                'Full access to platform',
                'Connect with tutors/students',
                'Basic messaging',
                'Ads displayed'
            ]
        ],
        'premium' => [
            'name' => 'Premium - Ad Free',
            'price' => 3.49,
            'duration_days' => 30,
            'shows_ads' => false,
            'features' => [
                'All Free Trial features',
                'No advertisements',
                'Priority support',
                'Advanced search filters',
                'Unlimited messaging'
            ]
        ]
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // Helper methods
    public function getPlanConfigAttribute(): array
    {
        return self::PLANS[$this->plan_type] ?? [];
    }

    public function getPlanNameAttribute(): string
    {
        return $this->plan_config['name'] ?? ucfirst($this->plan_type);
    }

    public function getPlanFeaturesAttribute(): array
    {
        return $this->plan_config['features'] ?? [];
    }

    public function isActive(): bool
    {
        return $this->status === 'active' &&
               (!$this->expires_at || $this->expires_at->isFuture());
    }

    public function isInTrial(): bool
    {
        return $this->plan_type === 'free_trial' &&
               $this->trial_ends_at &&
               $this->trial_ends_at->isFuture();
    }

    public function hasExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function trialHasExpired(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isPast();
    }

    public function shouldShowAds(): bool
    {
        return $this->shows_ads && $this->isActive();
    }

    public function getDaysRemainingAttribute(): int
    {
        if ($this->isInTrial()) {
            return $this->trial_days_remaining;
        }

        if (!$this->expires_at || $this->hasExpired()) {
            return 0;
        }

        return max(0, now()->diffInDays($this->expires_at, false));
    }

    public function getTrialDaysRemainingAttribute(): int
    {
        if (!$this->trial_ends_at || $this->trialHasExpired()) {
            return 0;
        }

        return max(0, now()->diffInDays($this->trial_ends_at, false));
    }

    public function upgradeToPremium(): self
    {
        $this->update([
            'plan_type' => 'premium',
            'price' => self::PLANS['premium']['price'],
            'shows_ads' => false,
            'expires_at' => now()->addDays(30),
        ]);

        return $this;
    }

    public function cancel(string $reason = null): bool
    {
        return $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);
    }

    public function renew(): bool
    {
        $planConfig = $this->plan_config;
        $renewalDays = $planConfig['duration_days'] ?? 30;

        return $this->update([
            'status' => 'active',
            'expires_at' => now()->addDays($renewalDays),
            'cancelled_at' => null,
            'cancellation_reason' => null,
        ]);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    public function scopeTrial($query)
    {
        return $query->where('plan_type', 'free_trial');
    }

    public function scopePremium($query)
    {
        return $query->where('plan_type', 'premium');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($subscription) {
            if ($subscription->plan_type === 'free_trial' && $subscription->started_at) {
                $subscription->trial_ends_at = $subscription->started_at->addDays(14);
                $subscription->expires_at = $subscription->started_at->addDays(14);
            }
        });
    }
}
