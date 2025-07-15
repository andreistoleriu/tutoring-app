<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReminderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReminderController extends Controller
{
    public function __construct(
        private ReminderService $reminderService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $reminders = $this->reminderService->getUserReminders($user, 20);

        return response()->json([
            'reminders' => $reminders->map(function ($reminder) {
                return [
                    'id' => $reminder->id,
                    'type' => $reminder->type,
                    'title' => $reminder->title,
                    'message' => $reminder->message,
                    'scheduled_at' => $reminder->scheduled_at->toISOString(),
                    'sent_at' => $reminder->sent_at?->toISOString(),
                    'is_sent' => $reminder->is_sent,
                    'is_read' => $reminder->is_read,
                    'booking' => $reminder->booking ? [
                        'id' => $reminder->booking->id,
                        'subject' => $reminder->booking->subject->name,
                        'scheduled_at' => $reminder->booking->scheduled_at->toISOString(),
                    ] : null,
                    'time_until' => $reminder->getTimeUntil(),
                ];
            }),
            'unread_count' => $this->reminderService->getUnreadCount($user),
        ]);
    }

    public function markAsRead(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $reminder = $user->reminders()->findOrFail($id);

        $reminder->markAsRead();

        return response()->json([
            'message' => 'Reminder marked as read',
            'unread_count' => $this->reminderService->getUnreadCount($user),
        ]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->reminderService->markAllAsRead($user);

        return response()->json([
            'message' => 'All reminders marked as read',
            'unread_count' => 0,
        ]);
    }

    public function getUnreadCount(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'unread_count' => $this->reminderService->getUnreadCount($user),
        ]);
    }
}
