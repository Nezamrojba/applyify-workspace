<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CleanupTempFilesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $disk = Storage::disk(config('filesystems.default'));
        $prefix = 'temp';
        $ttlMinutes = (int) (config('files.temp_ttl_minutes', 60 * 24));
        $now = now();
        foreach ($disk->allFiles($prefix) as $path) {
            $ts = $disk->lastModified($path);
            if ($now->diffInMinutes($ts ? now()->setTimestamp($ts) : now()) > $ttlMinutes) {
                $disk->delete($path);
                Log::info('Temp file deleted', ['path' => $path]);
            }
        }
    }
}

