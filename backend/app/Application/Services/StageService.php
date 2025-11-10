<?php

namespace App\Application\Services;

use App\Jobs\NotifyStageOpenedJob;
use App\Models\Application;
use App\Models\ApplicationStage;

class StageService
{
    public const STAGES = ['stage1','stage2','stage3','stage4'];

    public function ensureInitialStages(Application $app): void
    {
        foreach (self::STAGES as $i => $key) {
            ApplicationStage::firstOrCreate(['application_id' => $app->id, 'stage_key' => $key], ['status' => $i === 0 ? 'draft' : 'locked']);
        }
    }

    public function submit(Application $app, string $stageKey): void
    {
        $stage = ApplicationStage::where('application_id', $app->id)->where('stage_key', $stageKey)->firstOrFail();
        $stage->update(['status' => 'submitted']);
    }

    public function approveStage(Application $app, string $stageKey): void
    {
        $stage = ApplicationStage::where('application_id', $app->id)->where('stage_key', $stageKey)->firstOrFail();
        if ($stage->status === 'draft' || $stage->status === 'submitted') {
            $stage->update(['status' => 'approved']);
        }
    }

    public function issueLetterAndAdvance(Application $app, string $letterType): void
    {
        $index = $app->stage_index;
        $currentKey = self::STAGES[$index - 1] ?? null;
        if ($currentKey) {
            ApplicationStage::where('application_id', $app->id)->where('stage_key', $currentKey)->update(['status' => 'letter_issued']);
        }
        $nextIndex = $index + 1;
        $app->update(['stage_index' => $nextIndex]);
        $nextKey = self::STAGES[$nextIndex - 1] ?? null;
        if ($nextKey) {
            ApplicationStage::where('application_id', $app->id)->where('stage_key', $nextKey)->update(['status' => 'draft']);
            NotifyStageOpenedJob::dispatch($app->id, $nextKey);
        } else {
            $app->update(['status' => 'completed']);
        }
    }
}
