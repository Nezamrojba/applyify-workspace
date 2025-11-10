<?php

namespace App\Jobs;

use App\Models\Application;
use App\Notifications\StageOpenedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifyStageOpenedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $applicationId, public string $stageKey) {}

    public function handle(): void
    {
        $app = Application::find($this->applicationId);
        if (!$app) return;
        Log::info('NotifyStageOpened', ['application_id' => $app->id, 'stage' => $this->stageKey]);
        $student = $app->student;
        if ($student && $student->email) {
            $student->notify(new StageOpenedNotification($app, $this->stageKey));
        }
    }
}
