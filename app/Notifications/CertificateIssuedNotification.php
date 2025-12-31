<?php

namespace App\Notifications;

use App\Models\Certificate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CertificateIssuedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected Certificate $certificate) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Electronic Signature is ready!')
            ->greeting("Hi {$notifiable->name},")
            ->line('We are pleased to inform you that your electronic signature certificate has been successfully issued.')
            ->line('You can now download the file and start signing your documents.')
            ->action('Download Certificate', url('/dashboard/certificates'))
            ->line('Thank you for trusting our platform.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'certificate_id' => $this->certificate->id,
            'message' => 'Your certificate has been successfully issued.',
            'action_url' => '/dashboard/certificates'
        ];
    }
}
