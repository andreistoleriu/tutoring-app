<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Reminder;

class ReviewReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Reminder $reminder
    ) {}

    public function via($notifiable): array
    {
        $channels = ['database'];

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
                    ->subject('Lasă un review pentru lecția ta')
                    ->greeting('Bună ' . $notifiable->first_name . '!')
                    ->line($this->reminder->message)
                    ->line('Review-ul tău îi ajută pe alți studenți să aleagă tutorul potrivit.')
                    ->action('Scrie review', route('booking.review', $booking->id))
                    ->line('Îți mulțumim pentru timpul acordat!');
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
