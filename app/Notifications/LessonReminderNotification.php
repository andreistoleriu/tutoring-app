<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Reminder;

class LessonReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Reminder $reminder
    ) {}

    public function via($notifiable): array
    {
        $channels = ['database']; // Always store in database for in-app notifications

        $preferences = $notifiable->getNotificationPreferences();

        if ($preferences->email_notifications) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail($notifiable): MailMessage
    {
        $booking = $this->reminder->booking;

        return (new MailMessage)
                    ->subject($this->reminder->title)
                    ->greeting('Bună ' . $notifiable->first_name . '!')
                    ->line($this->reminder->message)
                    ->line('**Detalii lecție:**')
                    ->line('📚 Materia: ' . $booking->subject->name)
                    ->line('📅 Data: ' . $booking->scheduled_at->format('d.m.Y'))
                    ->line('🕒 Ora: ' . $booking->scheduled_at->format('H:i'))
                    ->line('⏰ Durata: ' . $booking->duration_minutes . ' minute')
                    ->line('💻 Tip: ' . ($booking->lesson_type === 'online' ? 'Online' : 'Față în față'))
                    ->action('Vezi detalii', route('booking.show', $booking->id))
                    ->line('Mult succes la lecție!');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => $this->reminder->type,
            'title' => $this->reminder->title,
            'message' => $this->reminder->message,
            'data' => $this->reminder->data,
            'booking_id' => $this->reminder->booking_id,
        ];
    }
}
