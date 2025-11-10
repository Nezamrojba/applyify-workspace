<?php

namespace App\Jobs;

use App\Models\Application;
use App\Notifications\LetterIssuedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifyLetterIssuedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $applicationId, public string $type) {}

    public function handle(): void
    {
        $app = Application::find($this->applicationId);
        if (!$app) return;
        Log::info('NotifyLetterIssued', ['application_id' => $app->id, 'type' => $this->type]);
        $student = $app->student;
        if ($student && $student->email) {
            $student->notify(new LetterIssuedNotification($app, $this->type));
        }
    }
}
