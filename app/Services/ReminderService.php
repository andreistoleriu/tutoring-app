<?php

namespace App\Services;

use App\Models\Reminder;
use App\Models\Booking;
use App\Models\User;
use App\Notifications\LessonReminderNotification;
use App\Notifications\ReviewReminderNotification;
use App\Notifications\PaymentReminderNotification;
use Carbon\Carbon;

class ReminderService
{
    public function createLessonReminders(Booking $booking): void
    {
        $student = $booking->student;
        $tutor = $booking->tutor;

        // Get user preferences
        $studentPrefs = $student->getNotificationPreferences();
        $tutorPrefs = $tutor->getNotificationPreferences();

        if ($studentPrefs->lesson_reminders) {
            $this->createStudentLessonReminder($booking, $studentPrefs);
        }

        if ($tutorPrefs->lesson_reminders) {
            $this->createTutorLessonReminder($booking, $tutorPrefs);
        }
    }

    private function createStudentLessonReminder(Booking $booking, $preferences): void
    {
        $reminderTime = $booking->scheduled_at->subHours($preferences->reminder_hours_before);

        if ($reminderTime > now()) {
            Reminder::create([
                'user_id' => $booking->student_id,
                'booking_id' => $booking->id,
                'type' => 'lesson_reminder_student',
                'title' => 'Lecție programată mâine',
                'message' => "Nu uita de lecția ta de {$booking->subject->name} cu {$booking->tutor->full_name} programată pentru {$booking->scheduled_at->format('d.m.Y la H:i')}.",
                'data' => [
                    'booking_id' => $booking->id,
                    'tutor_name' => $booking->tutor->full_name,
                    'subject' => $booking->subject->name,
                    'lesson_type' => $booking->lesson_type,
                ],
                'scheduled_at' => $reminderTime,
                'channel' => $preferences->email_notifications ? 'email' : 'in_app',
            ]);
        }
    }

    private function createTutorLessonReminder(Booking $booking, $preferences): void
    {
        $reminderTime = $booking->scheduled_at->subHours($preferences->reminder_hours_before);

        if ($reminderTime > now()) {
            Reminder::create([
                'user_id' => $booking->tutor_id,
                'booking_id' => $booking->id,
                'type' => 'lesson_reminder_tutor',
                'title' => 'Lecție programată mâine',
                'message' => "Ai o lecție de {$booking->subject->name} cu {$booking->student->full_name} programată pentru {$booking->scheduled_at->format('d.m.Y la H:i')}.",
                'data' => [
                    'booking_id' => $booking->id,
                    'student_name' => $booking->student->full_name,
                    'subject' => $booking->subject->name,
                    'lesson_type' => $booking->lesson_type,
                ],
                'scheduled_at' => $reminderTime,
                'channel' => $preferences->email_notifications ? 'email' : 'in_app',
            ]);
        }
    }

    public function createReviewReminder(Booking $booking): void
    {
        if ($booking->status !== 'completed') {
            return;
        }

        $studentPrefs = $booking->student->getNotificationPreferences();

        if (!$studentPrefs->review_reminders) {
            return;
        }

        // Create reminder 24 hours after lesson completion
        $reminderTime = $booking->completed_at->addHours(24);

        if ($reminderTime > now()) {
            Reminder::create([
                'user_id' => $booking->student_id,
                'booking_id' => $booking->id,
                'type' => 'review_reminder',
                'title' => 'Lasă un review pentru lecția ta',
                'message' => "Cum a fost lecția de {$booking->subject->name} cu {$booking->tutor->full_name}? Lasă un review pentru a ajuta alți studenți.",
                'data' => [
                    'booking_id' => $booking->id,
                    'tutor_name' => $booking->tutor->full_name,
                    'subject' => $booking->subject->name,
                ],
                'scheduled_at' => $reminderTime,
                'channel' => $studentPrefs->email_notifications ? 'email' : 'in_app',
            ]);
        }
    }

    public function sendPendingReminders(): int
    {
        $pendingReminders = Reminder::pending()->get();
        $sentCount = 0;

        foreach ($pendingReminders as $reminder) {
            if ($this->sendReminder($reminder)) {
                $sentCount++;
            }
        }

        return $sentCount;
    }

    private function sendReminder(Reminder $reminder): bool
    {
        try {
            $user = $reminder->user;

            switch ($reminder->type) {
                case 'lesson_reminder_student':
                case 'lesson_reminder_tutor':
                    $user->notify(new LessonReminderNotification($reminder));
                    break;
                case 'review_reminder':
                    $user->notify(new ReviewReminderNotification($reminder));
                    break;
                case 'payment_reminder':
                    $user->notify(new PaymentReminderNotification($reminder));
                    break;
            }

            $reminder->markAsSent();
            return true;

        } catch (\Exception $e) {
            \Log::error('Failed to send reminder: ' . $e->getMessage(), [
                'reminder_id' => $reminder->id,
                'user_id' => $reminder->user_id,
            ]);
            return false;
        }
    }

    public function getUserReminders(User $user, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return $user->reminders()
                   ->with('booking.subject', 'booking.tutor', 'booking.student')
                   ->where('scheduled_at', '>=', now()->subDays(7)) // Last 7 days
                   ->orderBy('scheduled_at', 'desc')
                   ->limit($limit)
                   ->get();
    }

    public function getUnreadCount(User $user): int
    {
        return $user->reminders()->unread()->count();
    }

    public function markAllAsRead(User $user): bool
    {
        return $user->reminders()->unread()->update(['is_read' => true]);
    }
}
