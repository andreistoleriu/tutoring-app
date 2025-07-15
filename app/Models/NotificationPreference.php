<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    use HasFactory;

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
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getDefaultPreferences(): array
    {
        return [
            'lesson_reminders' => true,
            'review_reminders' => true,
            'payment_reminders' => true,
            'booking_confirmations' => true,
            'email_notifications' => true,
            'sms_notifications' => false,
            'push_notifications' => true,
            'reminder_hours_before' => 24,
        ];
    }
}
