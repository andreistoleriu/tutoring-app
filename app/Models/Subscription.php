<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'tutor_id',
        'plan_type',
        'price',
        'status',
        'started_at',
        'expires_at',
        'trial_ends_at',
        'stripe_subscription_id',
        'cancellation_reason',
        'cancelled_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    const PLANS = [
        'free_trial' => [
            'name' => 'Free Trial',
            'price' => 0,
            'duration_days' => 14,
            'features' => [
                'Up to 5 bookings per month',
                'Basic profile',
                'Email support'
            ]
        ],
        'basic' => [
            'name' => 'Basic Plan',
            'price' => 29.99,
            'duration_days' => 30,
            'features' => [
                'Unlimited bookings',
                'Enhanced profile',
                'Priority support',
                'Basic analytics'
            ]
        ],
        'premium' => [
            'name' => 'Premium Plan',
            'price' => 49.99,
            'duration_days' => 30,
            'features' => [
                'Everything in Basic',
                'Featured profile',
                'Advanced analytics',
                'Custom branding',
                'Priority listing'
            ]
        ]
    ];

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }

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
               $this->expires_at && $this->expires_at->isFuture();
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
        return $this->trial_ends_at &&
               $this->trial_ends_at->isPast();
    }

    public function getDaysRemainingAttribute(): int
    {
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

    public function cancel(string $reason = null): bool
    {
        return $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);
    }

    public function renew(int $days = null): bool
    {
        $planConfig = $this->plan_config;
        $renewalDays = $days ?? $planConfig['duration_days'] ?? 30;

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
        return $query->where('status', 'active')
                    ->where('expires_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    public function scopeTrial($query)
    {
        return $query->where('plan_type', 'free_trial');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($subscription) {
            if ($subscription->plan_type === 'free_trial' && $subscription->started_at) {
                $subscription->trial_ends_at = $subscription->started_at->addDays(14);
            }
        });
    }
}
