<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StageOpenedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Application $application, public string $stageKey) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Stage Opened')
            ->greeting('Hello')
            ->line('A new stage is now open for your application.')
            ->line('Stage: '.$this->stageKey)
            ->line('University ID: '.$this->application->university_id)
            ->line('Course ID: '.$this->application->course_id);
    }
}

