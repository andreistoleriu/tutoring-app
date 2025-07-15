<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationPreferenceController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $preferences = $user->getNotificationPreferences();

        return response()->json(['preferences' => $preferences]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lesson_reminders' => 'boolean',
            'review_reminders' => 'boolean',
            'payment_reminders' => 'boolean',
            'booking_confirmations' => 'boolean',
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'reminder_hours_before' => 'integer|min:1|max:168', // Max 1 week
        ]);

        $user = $request->user();
        $preferences = $user->getNotificationPreferences();
        $preferences->update($validated);

        return response()->json([
            'message' => 'Notification preferences updated successfully',
            'preferences' => $preferences->fresh(),
        ]);
    }
}
