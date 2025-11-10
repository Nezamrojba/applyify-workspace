<?php

namespace App\Console;

use App\Jobs\CleanupTempFilesJob;
use App\Jobs\CollectMonitoringJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->job(new CleanupTempFilesJob())->daily();
        $schedule->job(new CollectMonitoringJob())->hourly();
    }
}

