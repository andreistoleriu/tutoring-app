<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Reminder;

class PaymentReminderNotification extends Notification implements ShouldQueue
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
                    ->subject('Reminder plată lecție')
                    ->greeting('Bună ' . $notifiable->first_name . '!')
                    ->line($this->reminder->message)
                    ->line('**Detalii plată:**')
                    ->line('📚 Materia: ' . $booking->subject->name)
                    ->line('💰 Suma: ' . $booking->price . ' RON')
                    ->line('📅 Data lecției: ' . $booking->scheduled_at->format('d.m.Y la H:i'))
                    ->action('Vezi detalii plată', url("/student/bookings/{$booking->id}"))
                    ->line('Vă mulțumim!');
    }

    public function toArray($notifiable): arrays
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
