<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'email',
        'password',
        'first_name',
        'last_name',
        'phone',
        'user_type',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    // Accessors
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    // Helper methods
    public function isTutor(): bool
    {
        return $this->user_type === 'tutor';
    }

    public function isStudent(): bool
    {
        return $this->user_type === 'student';
    }

    // Relationships
    public function tutor(): HasOne
    {
        return $this->hasOne(Tutor::class);
    }

    public function studentBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'student_id');
    }

    public function tutorBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'tutor_id');
    }

    public function givenReviews(): HasMany
    {
        return $this->hasMany(Review::class, 'student_id');
    }

    public function receivedReviews(): HasMany
    {
        return $this->hasMany(Review::class, 'tutor_id');
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(Availability::class, 'tutor_id');
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class, 'tutor_id');
    }

    public function reminders(): HasMany
{
    return $this->hasMany(Reminder::class);
}

public function notificationPreferences(): HasOne
{
    return $this->hasOne(NotificationPreferences::class);
}

/**
 * Get or create notification preferences for the user
 */
public function getNotificationPreferences()
{
    if ($this->notificationPreferences) {
        return $this->notificationPreferences;
    }

    return $this->notificationPreferences()->create([
        'lesson_reminders' => true,
        'review_reminders' => true,
        'payment_reminders' => true,
        'booking_confirmations' => true,
        'email_notifications' => true,
        'sms_notifications' => false,
        'push_notifications' => true,
        'reminder_hours_before' => 24,
    ]);
}
}
