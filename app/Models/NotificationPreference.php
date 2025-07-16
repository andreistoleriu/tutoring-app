<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model // FIXED: Changed from NotificationPreferences to NotificationPreference
{
    use HasFactory;

    protected $table = 'notification_preferences'; // ADDED: Explicit table name

    protected $fillable = [
        'user_id',
        'lesson_reminders',
        'review_reminders',
        'payment_reminders',
        'booking_confirmations',
        'email_notifications',
        'sms_notifications',
        'push_notifications',
        'reminder_hours_before',
    ];

    protected $casts = [
        'lesson_reminders' => 'boolean',
        'review_reminders' => 'boolean',
        'payment_reminders' => 'boolean',
        'booking_confirmations' => 'boolean',
        'email_notifications' => 'boolean',
        'sms_notifications' => 'boolean',
        'push_notifications' => 'boolean',
        'reminder_hours_before' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
