<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tutor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'location_id',
        'bio',
        'profile_image',
        'hourly_rate',
        'offers_online',
        'offers_in_person',
        'experience',
        'education',
        'rating',
        'total_reviews',
        'total_lessons',
        'total_earnings',
        'is_verified',
        'is_featured',
        'is_active',
        'last_active',
    ];

    protected $casts = [
        'hourly_rate' => 'decimal:2',
        'rating' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'offers_online' => 'boolean',
        'offers_in_person' => 'boolean',
        'is_verified' => 'boolean',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'last_active' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'tutor_subjects')
            ->withPivot('experience_description')
            ->withTimestamps();
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'tutor_id', 'user_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'tutor_id', 'user_id');
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(Availability::class, 'tutor_id', 'user_id');
    }

    public function subscription(): HasMany
    {
        return $this->hasMany(Subscription::class, 'tutor_id', 'user_id');
    }

    // Accessors
    public function getLessonTypesAttribute(): array
    {
        $types = [];

        if ($this->offers_online) {
            $types[] = 'online';
        }

        if ($this->offers_in_person) {
            $types[] = 'in_person';
        }

        return $types;
    }

    public function getProfileImageUrlAttribute(): ?string
    {
        if ($this->profile_image) {
            return asset('storage/tutors/' . $this->profile_image);
        }

        return null;
    }

    // Helper methods
    public function hasActiveSubscription(): bool
    {
        return $this->subscription()
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->exists();
    }

    public function updateRating(): void
    {
        $reviews = $this->reviews()->get();

        if ($reviews->count() > 0) {
            $this->update([
                'rating' => $reviews->avg('rating'),
                'total_reviews' => $reviews->count(),
            ]);
        }
    }
}
