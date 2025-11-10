<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LetterIssuedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Application $application, public string $type) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Letter Issued')
            ->greeting('Hello')
            ->line('Your new letter is ready: '.$this->type)
            ->line('You can download it from your application page.');
    }
}

