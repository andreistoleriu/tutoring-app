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

    public function reminders(): HasMany
    {
        return $this->hasMany(Reminder::class);
    }

    // FIXED: Changed from NotificationPreferences to NotificationPreference (removed 's')
    public function notificationPreferences(): HasOne
    {
        return $this->hasOne(NotificationPreference::class);
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
    // Add these relationships to the User model
        public function subscription(): HasOne
        {
            return $this->hasOne(Subscription::class)->latest();
        }

        public function activeSubscription(): HasOne
        {
            return $this->hasOne(Subscription::class)->where('status', 'active');
        }

        public function payments(): HasMany
        {
            return $this->hasMany(Payment::class);
        }

        // Add helper methods
        public function hasActiveSubscription(): bool
        {
            return $this->activeSubscription()->exists();
        }

        public function isInTrial(): bool
        {
            $subscription = $this->activeSubscription;
            return $subscription && $subscription->isInTrial();
        }

        public function shouldShowAds(): bool
        {
            $subscription = $this->activeSubscription;
            return !$subscription || $subscription->shouldShowAds();
        }

        public function canAccessPremiumFeatures(): bool
        {
            $subscription = $this->activeSubscription;
            return $subscription && $subscription->plan_type === 'premium' && $subscription->isActive();
        }

        public function studentConversations(): HasMany
        {
            return $this->hasMany(Conversation::class, 'student_id');
        }

        public function sentMessages(): HasMany
        {
            return $this->hasMany(Message::class, 'sender_id');
        }

        // Add to the helper methods section
        public function conversations(): HasMany
        {
            if ($this->isTutor()) {
                return $this->tutorConversations();
            }

            return $this->studentConversations();
        }

        public function getConversationWith(User $user): ?Conversation
        {
            if ($this->isTutor() && $user->isStudent()) {
                return Conversation::where('tutor_id', $this->id)
                    ->where('student_id', $user->id)
                    ->first();
            }

            if ($this->isStudent() && $user->isTutor()) {
                return Conversation::where('tutor_id', $user->id)
                    ->where('student_id', $this->id)
                    ->first();
            }

            return null;
        }

        public function getTotalUnreadMessagesCount(): int
        {
            return $this->conversations()
                ->with('messages')
                ->get()
                ->sum(function ($conversation) {
                    return $conversation->getUnreadCountFor($this);
                });
        }

        // Add these relationship methods to your User model:
public function tutorConversations(): HasMany
{
    return $this->hasMany(Conversation::class, 'tutor_id');
}

}
