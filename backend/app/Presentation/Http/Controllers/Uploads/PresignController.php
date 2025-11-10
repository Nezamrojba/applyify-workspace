<?php

namespace App\Presentation\Http\Controllers\Uploads;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PresignController
{
    public function presign(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'filename' => ['required','string'],
            'content_type' => ['required','in:application/pdf,image/png,image/jpeg'],
            'size_bytes' => ['required','integer','min:1','max:10485760'],
            'path' => ['sometimes','string']
        ]);
        $diskName = config('filesystems.cloud');
        $driver = $diskName ? config("filesystems.disks.$diskName.driver") : null;
        if ($driver !== 's3' || !class_exists('League\\Flysystem\\AwsS3V3\\PortableVisibilityConverter')) {
            return response()->json([
                'message' => 'S3 adapter not available. Install league/flysystem-aws-s3-v3 and aws/aws-sdk-php, and configure filesystems.cloud=s3.',
            ], 501);
        }
        $disk = Storage::disk($diskName);
        $path = trim($data['path'] ?? ('uploads/'.$user->id), '/');
        $key = $path.'/'.uniqid().'-'.$data['filename'];
        $expires = now()->addMinutes(10);
        try {
            $url = $disk->temporaryUploadUrl($key, $expires, ['ContentType' => $data['content_type']]);
            return response()->json(['url' => $url, 'method' => 'PUT', 'key' => $key, 'headers' => ['Content-Type' => $data['content_type']]]);
        } catch (\Throwable $e) {
            $fallback = $disk->temporaryUrl($key, $expires);
            return response()->json(['url' => $fallback, 'method' => 'PUT', 'key' => $key, 'headers' => ['Content-Type' => $data['content_type']]]);
        }
    }
}
