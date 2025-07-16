<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reminder;
use App\Models\User;
use App\Models\Booking;
use Carbon\Carbon;

class ReminderSeeder extends Seeder
{
    public function run(): void
    {
        // Get some users and bookings
        $students = User::where('user_type', 'student')->limit(3)->get();
        $tutors = User::where('user_type', 'tutor')->limit(2)->get();
        $bookings = Booking::with(['student', 'tutor', 'subject'])->limit(5)->get();

        // If no bookings exist, create sample ones for testing
        if ($bookings->isEmpty()) {
            echo "No bookings found. Creating sample bookings first...\n";
            return;
        }

        foreach ($students as $student) {
            $booking = $bookings->random();

            // Create past reminder (already due)
            Reminder::create([
                'user_id' => $student->id,
                'booking_id' => $booking->id,
                'type' => 'lesson_reminder_student',
                'title' => 'Lecție programată azi',
                'message' => 'Nu uita de lecția ta de ' . ($booking->subject->name ?? 'Matematică') . ' cu ' . ($booking->tutor->full_name ?? 'Ana Popescu') . ' programată pentru astăzi la 14:00.',
                'data' => [
                    'booking_id' => $booking->id,
                    'tutor_name' => $booking->tutor->full_name ?? 'Ana Popescu',
                    'subject' => $booking->subject->name ?? 'Matematică',
                    'lesson_type' => $booking->lesson_type ?? 'online',
                ],
                'scheduled_at' => now()->subHours(2),
                'is_sent' => false,
                'is_read' => false,
                'channel' => 'in_app',
            ]);

            // Create upcoming reminder
            Reminder::create([
                'user_id' => $student->id,
                'booking_id' => $booking->id,
                'type' => 'review_reminder',
                'title' => 'Lasă un review pentru lecția ta',
                'message' => 'Cum a fost lecția de ' . ($booking->subject->name ?? 'Fizică') . ' cu ' . ($booking->tutor->full_name ?? 'Maria Ionescu') . '? Lasă un review pentru a ajuta alți studenți.',
                'data' => [
                    'booking_id' => $booking->id,
                    'tutor_name' => $booking->tutor->full_name ?? 'Maria Ionescu',
                    'subject' => $booking->subject->name ?? 'Fizică',
                ],
                'scheduled_at' => now()->addHours(1),
                'is_sent' => false,
                'is_read' => false,
                'channel' => 'in_app',
            ]);

            // Create another upcoming lesson reminder
            Reminder::create([
                'user_id' => $student->id,
                'booking_id' => $booking->id,
                'type' => 'lesson_reminder_student',
                'title' => 'Lecție programată mâine',
                'message' => 'Nu uita de lecția ta de ' . ($booking->subject->name ?? 'Engleză') . ' cu ' . ($booking->tutor->full_name ?? 'Elena Georgescu') . ' programată pentru mâine la 10:00.',
                'data' => [
                    'booking_id' => $booking->id,
                    'tutor_name' => $booking->tutor->full_name ?? 'Elena Georgescu',
                    'subject' => $booking->subject->name ?? 'Engleză',
                    'lesson_type' => $booking->lesson_type ?? 'online',
                ],
                'scheduled_at' => now()->addDay(),
                'is_sent' => false,
                'is_read' => false,
                'channel' => 'in_app',
            ]);
        }

        // Create some tutor reminders
        foreach ($tutors as $tutor) {
            $booking = $bookings->random();

            Reminder::create([
                'user_id' => $tutor->id,
                'booking_id' => $booking->id,
                'type' => 'lesson_reminder_tutor',
                'title' => 'Lecție programată astăzi',
                'message' => 'Ai o lecție de ' . ($booking->subject->name ?? 'Matematică') . ' cu ' . ($booking->student->full_name ?? 'Alex Popescu') . ' programată pentru astăzi la 16:00.',
                'data' => [
                    'booking_id' => $booking->id,
                    'student_name' => $booking->student->full_name ?? 'Alex Popescu',
                    'subject' => $booking->subject->name ?? 'Matematică',
                    'lesson_type' => $booking->lesson_type ?? 'face_to_face',
                ],
                'scheduled_at' => now()->addHours(3),
                'is_sent' => false,
                'is_read' => false,
                'channel' => 'in_app',
            ]);
        }

        echo "✅ Created reminder test data successfully!\n";
    }
}
